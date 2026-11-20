<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintArea;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PrintAreaController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $product->printAreas()->create($this->validated($request, $product));

        return back()->with('status', 'Area sablon ditambahkan.');
    }

    public function update(Request $request, PrintArea $area): RedirectResponse
    {
        $area->update($this->validated($request, $area->product, $area->id));

        return back()->with('status', 'Area sablon diperbarui.');
    }

    public function destroy(PrintArea $area): RedirectResponse
    {
        // designs.print_area_id menjadi null; pesanan lama tetap punya snapshot area.
        $area->delete();

        return back()->with('status', 'Area sablon dihapus.');
    }

    private function validated(Request $request, Product $product, ?int $ignoreId = null): array
    {
        $coord = ['nullable', 'numeric'];

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('print_areas', 'name')->where('product_id', $product->id)->ignore($ignoreId),
            ],
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
