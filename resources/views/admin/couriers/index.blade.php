@extends('layouts.admin')

@section('title', 'Ekspedisi — Panel Admin')

@section('admin')
    <h1>Ekspedisi dan tarif</h1>

    <form method="POST" action="{{ route('admin.couriers.store') }}" class="row-form">
        @csrf
        <div class="f">
            <label for="name">Ekspedisi baru</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="mis. JNE">
        </div>
        <button class="btn" type="submit">Tambah</button>
    </form>

    <table class="section">
        <thead><tr><th>Ekspedisi</th><th>Tujuan bertarif</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($couriers as $courier)
            <tr>
                <td>{{ $courier->name }}</td>
                <td>{{ $courier->shipping_rates_count }}</td>
                <td>{!! $courier->is_active ? '<span class="badge ok">aktif</span>' : '<span class="badge">nonaktif</span>' !!}</td>
                <td class="actions">
                    <a class="btn ghost sm" href="{{ route('admin.couriers.edit', $courier) }}">Kelola tarif</a>
                    <form method="POST" action="{{ route('admin.couriers.destroy', $courier) }}" onsubmit="return confirm('Hapus ekspedisi ini beserta semua tarifnya?')">
                        @csrf @method('DELETE')
                        <button class="btn danger sm" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada ekspedisi.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
