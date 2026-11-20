<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourierController extends Controller
{
    public function index(): View
    {
        return view('admin.couriers.index', [
            'couriers' => Courier::withCount('shippingRates')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:couriers,name'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $courier = Courier::create($data);

        return redirect()->route('admin.couriers.edit', $courier)
            ->with('status', 'Ekspedisi ditambahkan. Isi tarif per tujuan di bawah.');
    }

    public function edit(Courier $courier): View
    {
        $courier->load(['shippingRates' => fn ($q) => $q->orderBy('destination')]);

        return view('admin.couriers.edit', compact('courier'));
    }

    public function update(Request $request, Courier $courier): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('couriers', 'name')->ignore($courier->id)],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $courier->update($data);

        return back()->with('status', 'Ekspedisi diperbarui.');
    }

    public function destroy(Courier $courier): RedirectResponse
    {
        // Pesanan lama menyimpan snapshot tujuan dan ongkir; relasinya menjadi null.
        $courier->delete();

        return redirect()->route('admin.couriers.index')->with('status', 'Ekspedisi dihapus.');
    }
}
