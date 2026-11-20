<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with('category')
                ->withCount(['options', 'colors', 'printAreas'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['is_active' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.edit', $product)
            ->with('status', 'Produk dibuat. Lengkapi ukuran, warna + mockup, dan area sablon di bawah.');
    }

    public function edit(Product $product): View
    {
        $product->load(['options', 'colors', 'printAreas']);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return back()->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Produk sudah dipakai pesanan, jadi tidak dapat dihapus. Nonaktifkan saja.');
        }

        foreach ($product->colors as $color) {
            if ($color->mockup_image) {
                Storage::disk('local')->delete($color->mockup_image);
            }
        }
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete(); // opsi, warna, dan area ikut terhapus (cascade)

        return redirect()->route('admin.products.index')->with('status', 'Produk dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
