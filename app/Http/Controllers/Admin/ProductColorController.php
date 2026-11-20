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
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate($this->rules($product));

        $color = new ProductColor(['name' => $data['name']]);
        $color->product_id = $product->id;

        if ($request->hasFile('mockup_image')) {
            $this->assertSameSize($product, $request->file('mockup_image'));
            $color->mockup_image = $request->file('mockup_image')->store('mockups', 'local');
        }

        $color->save();

        return back()->with('status', 'Warna ditambahkan.');
    }

    public function update(Request $request, ProductColor $color): RedirectResponse
    {
        $product = $color->product;
        $request->validate($this->rules($product, $color->id));

        $color->name = $request->input('name');

        if ($request->boolean('remove_mockup') && $color->mockup_image) {
            Storage::disk('local')->delete($color->mockup_image);
            $color->mockup_image = null;
        }

        if ($request->hasFile('mockup_image')) {
            $this->assertSameSize($product, $request->file('mockup_image'), $color->id);

            if ($color->mockup_image) {
                Storage::disk('local')->delete($color->mockup_image);
            }
            $color->mockup_image = $request->file('mockup_image')->store('mockups', 'local');
        }

        $color->save();

        return back()->with('status', 'Warna diperbarui.');
    }

    public function destroy(ProductColor $color): RedirectResponse
    {
        if ($color->mockup_image) {
            Storage::disk('local')->delete($color->mockup_image);
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
        ];
    }

    /** Semua mockup satu produk harus berukuran piksel sama (handoff bagian 13, butir 5). */
    private function assertSameSize(Product $product, UploadedFile $file, ?int $ignoreId = null): void
    {
        $existing = $product->colors()
            ->whereNotNull('mockup_image')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->first();

        if (! $existing) {
            return;
        }

        $existingPath = Storage::disk('local')->path($existing->mockup_image);
        if (! is_file($existingPath)) {
            return;
        }

        $a = @getimagesize($existingPath);
        $b = @getimagesize($file->getRealPath());

        if ($a && $b && ($a[0] !== $b[0] || $a[1] !== $b[1])) {
            throw ValidationException::withMessages([
                'mockup_image' => "Ukuran file harus sama dengan mockup warna lain pada produk ini ({$a[0]}×{$a[1]} piksel); file ini {$b[0]}×{$b[1]}.",
            ]);
        }
    }
}
