<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductOptionController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);

        $product->options()->create($data);

        return back()->with('status', 'Ukuran ditambahkan.');
    }

    public function update(Request $request, ProductOption $option): RedirectResponse
    {
        $option->update($this->validated($request, $option->product, $option->id));

        return back()->with('status', 'Ukuran diperbarui.');
    }

    public function destroy(ProductOption $option): RedirectResponse
    {
        // Pesanan lama menyimpan snapshot nama ukuran, jadi aman dihapus.
        $option->delete();

        return back()->with('status', 'Ukuran dihapus.');
    }

    private function validated(Request $request, Product $product, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('product_options', 'name')->where('product_id', $product->id)->ignore($ignoreId),
            ],
            'extra_price' => ['nullable', 'integer', 'min:0', 'max:100000000'],
        ]);

        $data['extra_price'] ??= 0;

        return $data;
    }
}
