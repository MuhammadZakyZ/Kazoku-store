<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Whatsapp;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $input = $request->validate(
            [
                'q' => ['nullable', 'string', 'max:100'],
                'kategori' => ['nullable', 'exists:categories,id'],
                'harga_min' => ['nullable', 'integer', 'min:0'],
                'harga_max' => ['nullable', 'integer', 'min:0', 'gte:harga_min'],
            ],
            ['harga_max.gte' => 'Harga maksimum tidak boleh lebih kecil dari harga minimum.'],
            ['q' => 'kata kunci', 'kategori' => 'kategori', 'harga_min' => 'harga minimum', 'harga_max' => 'harga maksimum'],
        );

        $products = Product::active()
            ->with('category')
            ->filter([
                'keyword' => $input['q'] ?? null,
                'category_id' => $input['kategori'] ?? null,
                'min_price' => $input['harga_min'] ?? null,
                'max_price' => $input['harga_max'] ?? null,
            ])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $input,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'options', 'colors', 'printAreas']);

        return view('products.show', [
            'product' => $product,
            'hasPreview' => $product->hasPreview(),
            'whatsapp' => Whatsapp::link(
                Setting::get('whatsapp_number'),
                "Halo Kazoku Store, saya ingin konsultasi tentang {$product->name}."
            ),
        ]);
    }
}