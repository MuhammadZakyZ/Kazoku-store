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
                'harga' => ['nullable', 'in:0-100000,100000-150000,150000-250000,250000-'],
            ],
            ['harga_max.gte' => 'Harga maksimum tidak boleh lebih kecil dari harga minimum.'],
            ['q' => 'kata kunci', 'kategori' => 'kategori', 'harga_min' => 'harga minimum', 'harga_max' => 'harga maksimum'],
        );

        // Pilihan "Rentang harga" (dropdown) diterjemahkan ke batas minimum/maksimum,
        // kecuali bila batas manual (harga_min/harga_max) dikirim langsung.
        $minPrice = $input['harga_min'] ?? null;
        $maxPrice = $input['harga_max'] ?? null;
        if (filled($input['harga'] ?? null) && $minPrice === null && $maxPrice === null) {
            [$low, $high] = explode('-', $input['harga']);
            $minPrice = $low !== '' ? (int) $low : null;
            $maxPrice = $high !== '' ? (int) $high : null;
        }

        $products = Product::active()
            ->with(['category', 'options:id,product_id,name', 'colors:id,product_id'])
            ->filter([
                'keyword' => $input['q'] ?? null,
                'category_id' => $input['kategori'] ?? null,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $input,
            'whatsapp' => Whatsapp::link(Setting::get('whatsapp_number'), 'Halo Kazoku Store, saya ingin bertanya tentang produk.'),
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