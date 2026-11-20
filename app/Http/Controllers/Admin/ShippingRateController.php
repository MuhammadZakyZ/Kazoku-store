<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\ShippingRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingRateController extends Controller
{
    public function store(Request $request, Courier $courier): RedirectResponse
    {
        $courier->shippingRates()->create($this->validated($request, $courier));

        return back()->with('status', 'Tarif ditambahkan.');
    }

    public function update(Request $request, ShippingRate $rate): RedirectResponse
    {
        $rate->update($this->validated($request, $rate->courier, $rate->id));

        return back()->with('status', 'Tarif diperbarui.');
    }

    public function destroy(ShippingRate $rate): RedirectResponse
    {
        $rate->delete();

        return back()->with('status', 'Tarif dihapus.');
    }

    private function validated(Request $request, Courier $courier, ?int $ignoreId = null): array
    {
        return $request->validate([
            'destination' => [
                'required', 'string', 'max:150',
                Rule::unique('shipping_rates', 'destination')->where('courier_id', $courier->id)->ignore($ignoreId),
            ],
            'rate' => ['required', 'integer', 'min:0', 'max:100000000'],
        ]);
    }
}
