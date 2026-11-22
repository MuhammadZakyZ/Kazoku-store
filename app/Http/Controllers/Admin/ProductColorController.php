<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductColorController extends Controller
{
    /** field form => sisi */
    private const SIDES = ['mockup_image' => 'depan', 'mockup_back' => 'belakang'];

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate($this->rules($product));

        $color = new ProductColor(['name' => $data['name']]);
        $color->product_id = $product->id;

        foreach (self::SIDES as $field => $side) {
            if ($request->hasFile($field)) {
                $this->assertSameSize($product, $request->file($field), $field);
                $color->{$field} = $request->file($field)->store('mockups', 'local');
            }
        }

        $color->save();

        return back()->with('status', 'Warna ditambahkan.');
    }

    public function update(Request $request, ProductColor $color): RedirectResponse
    {
        $product = $color->product;
        $request->validate($this->rules($product, $color->id));

        $color->name = $request->input('name');

        foreach (self::SIDES as $field => $side) {
            $removeField = $field === 'mockup_back' ? 'remove_mockup_back' : 'remove_mockup';

            if ($request->boolean($removeField) && $color->{$field}) {
                Storage::disk('local')->delete($color->{$field});
                $color->{$field} = null;
            }

            if ($request->hasFile($field)) {
                $this->assertSameSize($product, $request->file($field), $field, $color->id);

                if ($color->{$field}) {
                    Storage::disk('local')->delete($color->{$field});
                }
                $color->{$field} = $request->file($field)->store('mockups', 'local');
            }
        }

        $color->save();

        return back()->with('status', 'Warna diperbarui.');
    }

    public function destroy(ProductColor $color): RedirectResponse
    {
        foreach (array_keys(self::SIDES) as $field) {
            if ($color->{$field}) {
                Storage::disk('local')->delete($color->{$field});
            }
        }

        $color->delete();

        return back()->with('status', 'Warna dihapus.');
    }

    private function rules(Product $product, ?int $ignoreId = null): array
    {
        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('product_colors', 'name')->where('product_id', $product->id)->ignore($ignoreId),
            ],
            'mockup_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mockup_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /** Semua mockup pada SISI yang sama dalam satu produk harus berukuran piksel sama. */
    private function assertSameSize(Product $product, UploadedFile $file, string $field, ?int $ignoreId = null): void
    {
        $existing = $product->colors()
            ->whereNotNull($field)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->first();

        if (! $existing) {
            return;
        }

        $existingPath = Storage::disk('local')->path($existing->{$field});
        if (! is_file($existingPath)) {
            return;
        }

        $a = @getimagesize($existingPath);
        $b = @getimagesize($file->getRealPath());

        if ($a && $b && ($a[0] !== $b[0] || $a[1] !== $b[1])) {
            $sideLabel = self::SIDES[$field];

            throw ValidationException::withMessages([
                $field => "Ukuran file mockup {$sideLabel} harus sama dengan mockup {$sideLabel} warna lain pada produk ini ({$a[0]}×{$a[1]} piksel); file ini {$b[0]}×{$b[1]}.",
            ]);
        }
    }
}
