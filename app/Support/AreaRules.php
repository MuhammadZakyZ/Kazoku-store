<?php

namespace App\Support;

use App\Models\PrintArea;
use Illuminate\Support\Facades\DB;

/**
 * Aturan kombinasi area sablon: pasangan area yang tumpang tindih tidak boleh
 * dipilih bersama dalam satu pesanan. Aturannya data (tabel print_area_conflicts),
 * dikelola admin per area, bukan kode.
 */
class AreaRules
{
    /** @return array<int, array{0: int, 1: int}> pasangan terlarang pada satu produk */
    public static function pairs(int $productId): array
    {
        return DB::table('print_area_conflicts as c')
            ->join('print_areas as a', 'a.id', '=', 'c.print_area_id')
            ->where('a.product_id', $productId)
            ->get(['c.print_area_id', 'c.conflicts_with_id'])
            ->map(fn ($r) => [(int) $r->print_area_id, (int) $r->conflicts_with_id])
            ->all();
    }

    /** @return array<int, array<int, int>> peta simetris: id area => id area yang bentrok */
    public static function map(int $productId): array
    {
        $map = [];

        foreach (self::pairs($productId) as [$a, $b]) {
            $map[$a][] = $b;
            $map[$b][] = $a;
        }

        return $map;
    }

    /**
     * Pasangan yang dilanggar oleh pilihan tertentu.
     *
     * @param  array<int, int>  $selectedIds
     * @return array<int, array{0: int, 1: int}>
     */
    public static function violations(array $selectedIds, int $productId): array
    {
        $selected = array_flip(array_map('intval', $selectedIds));

        return array_values(array_filter(
            self::pairs($productId),
            fn ($pair) => isset($selected[$pair[0]], $selected[$pair[1]])
        ));
    }

    /** Mengganti seluruh larangan kombinasi untuk satu area. */
    public static function setConflicts(PrintArea $area, array $otherIds): void
    {
        $otherIds = array_values(array_unique(array_filter(
            array_map('intval', $otherIds),
            fn ($id) => $id > 0 && $id !== $area->id
        )));

        DB::transaction(function () use ($area, $otherIds) {
            DB::table('print_area_conflicts')
                ->where('print_area_id', $area->id)
                ->orWhere('conflicts_with_id', $area->id)
                ->delete();

            $rows = [];
            foreach ($otherIds as $other) {
                $rows[] = [
                    'print_area_id' => min($area->id, $other),
                    'conflicts_with_id' => max($area->id, $other),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($rows !== []) {
                DB::table('print_area_conflicts')->insert($rows);
            }
        });
    }
}
