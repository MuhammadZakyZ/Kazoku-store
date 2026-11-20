@extends('layouts.admin')

@section('title', 'Kelola Ekspedisi — Panel Admin')

@section('admin')
    <div class="head">
        <h1>{{ $courier->name }}</h1>
        <a class="btn ghost" href="{{ route('admin.couriers.index') }}">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.couriers.update', $courier) }}" class="row-form">
        @csrf @method('PUT')
        <div class="f">
            <label for="name">Nama ekspedisi</label>
            <input id="name" type="text" name="name" value="{{ old('name', $courier->name) }}" required maxlength="100">
        </div>
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $courier->is_active))> Aktif (bisa dipilih pelanggan)</label>
        <button class="btn" type="submit">Simpan</button>
    </form>

    <h2 class="section">Tarif per tujuan</h2>
    <p class="hint">Tarif berupa ongkir tetap per tujuan (rupiah bulat).</p>

    @foreach ($courier->shippingRates as $rate)
        <form method="POST" action="{{ route('admin.rates.update', $rate) }}" class="row-form line">
            @csrf @method('PUT')
            <div class="f"><label>Tujuan</label><input type="text" name="destination" value="{{ $rate->destination }}" required maxlength="150"></div>
            <div class="f"><label>Tarif (Rp)</label><input type="number" name="rate" min="0" step="1" value="{{ $rate->rate }}" required></div>
            <button class="btn ghost sm" type="submit">Simpan</button>
            <button class="btn danger sm" type="submit" form="del-rate-{{ $rate->id }}">Hapus</button>
        </form>
        <form id="del-rate-{{ $rate->id }}" method="POST" action="{{ route('admin.rates.destroy', $rate) }}" onsubmit="return confirm('Hapus tarif ini?')">
            @csrf @method('DELETE')
        </form>
    @endforeach

    <form method="POST" action="{{ route('admin.couriers.rates.store', $courier) }}" class="row-form line add">
        @csrf
        <div class="f"><label>Tujuan baru</label><input type="text" name="destination" required maxlength="150" placeholder="mis. Malang"></div>
        <div class="f"><label>Tarif (Rp)</label><input type="number" name="rate" min="0" step="1" required></div>
        <button class="btn sm" type="submit">Tambah tarif</button>
    </form>
@endsection
