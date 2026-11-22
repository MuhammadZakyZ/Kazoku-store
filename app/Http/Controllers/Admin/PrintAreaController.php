<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AreaSide;
use App\Http\Controllers\Controller;
use App\Models\PrintArea;
use App\Models\Product;
use App\Support\AreaRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PrintAreaController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $conflicts = $this->conflictIds($request, $product);
        $artworkPath = $request->file('artwork_file')?->store('print-areas', 'local');
        unset($data['artwork_file'], $data['remove_artwork_file']);

        try {
            DB::transaction(function () use ($product, $data, $conflicts, $artworkPath): void {
                $area = $product->printAreas()->create([
                    ...$data,
                    'artwork_file' => $artworkPath,
                ]);
                AreaRules::setConflicts($area, $conflicts);
            });
        } catch (Throwable $exception) {
            if ($artworkPath !== null) {
                Storage::disk('local')->delete($artworkPath);
            }

            throw $exception;
        }

        return back()->with('status', 'Area sablon ditambahkan.');
    }

    public function update(Request $request, PrintArea $area): RedirectResponse
    {
        $data = $this->validated($request, $area->product, $area->id);
        $conflicts = $this->conflictIds($request, $area->product, $area->id);
        $oldArtworkPath = $area->artwork_file;
        $newArtworkPath = $request->file('artwork_file')?->store('print-areas', 'local');

        unset($data['artwork_file'], $data['remove_artwork_file']);
        if ($newArtworkPath !== null) {
            $data['artwork_file'] = $newArtworkPath;
        } elseif ($request->boolean('remove_artwork_file')) {
            $data['artwork_file'] = null;
        }

        try {
            DB::transaction(function () use ($area, $data, $conflicts): void {
                $area->update($data);
                AreaRules::setConflicts($area, $conflicts);
            });
        } catch (Throwable $exception) {
            if ($newArtworkPath !== null) {
                Storage::disk('local')->delete($newArtworkPath);
            }

            throw $exception;
        }

        if ($oldArtworkPath !== null && $oldArtworkPath !== $area->artwork_file) {
            Storage::disk('local')->delete($oldArtworkPath);
        }

        return back()->with('status', 'Area sablon diperbarui.');
    }

    public function destroy(PrintArea $area): RedirectResponse
    {
        // designs.print_area_id menjadi null; pesanan lama tetap punya snapshot area.
        $artworkPath = $area->artwork_file;
        $area->delete();

        if ($artworkPath !== null) {
            Storage::disk('local')->delete($artworkPath);
        }

        return back()->with('status', 'Area sablon dihapus.');
    }

    /** Id area lain (pada produk yang sama) yang tidak boleh dipilih bersama area ini. */
    private function conflictIds(Request $request, Product $product, ?int $selfId = null): array
    {
        $request->validate([
            'conflicts' => ['nullable', 'array'],
            'conflicts.*' => [
                'integer',
                Rule::exists('print_areas', 'id')->where('product_id', $product->id),
            ],
        ], [], ['conflicts' => 'larangan kombinasi', 'conflicts.*' => 'larangan kombinasi']);

        return array_values(array_filter(
            array_map('intval', (array) $request->input('conflicts', [])),
            fn ($id) => $id !== $selfId
        ));
    }

    private function validated(Request $request, Product $product, ?int $ignoreId = null): array
    {
        $coord = ['nullable', 'numeric'];

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('print_areas', 'name')->where('product_id', $product->id)->ignore($ignoreId),
            ],
            'side' => ['required', Rule::in(array_column(AreaSide::cases(), 'value'))],
            'artwork_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
            'remove_artwork_file' => ['nullable', 'boolean'],
            'width_cm' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'height_cm' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'price' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            // Koordinat preview (persen pada mockup): semuanya diisi atau semuanya kosong.
            'preview_x' => [...$coord, 'required_with:preview_y,preview_w,preview_h', 'between:0,100'],
            'preview_y' => [...$coord, 'required_with:preview_x,preview_w,preview_h', 'between:0,100'],
            'preview_w' => [...$coord, 'required_with:preview_x,preview_y,preview_h', 'gt:0', 'max:100'],
            'preview_h' => [...$coord, 'required_with:preview_x,preview_y,preview_w', 'gt:0', 'max:100'],
        ]);

        $data['price'] ??= 0;

        if (($data['preview_x'] ?? null) !== null) {
            if ($data['preview_x'] + $data['preview_w'] > 100) {
                throw ValidationException::withMessages(['preview_w' => 'Posisi X + lebar preview tidak boleh lebih dari 100%.']);
            }
            if ($data['preview_y'] + $data['preview_h'] > 100) {
                throw ValidationException::withMessages(['preview_h' => 'Posisi Y + tinggi preview tidak boleh lebih dari 100%.']);
            }
        }

        return $data;
    }
}
