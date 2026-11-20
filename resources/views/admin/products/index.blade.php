@extends('layouts.admin')

@section('title', 'Produk — Panel Admin')

@section('admin')
    <div class="head">
        <h1>Produk</h1>
        <a class="btn" href="{{ route('admin.products.create') }}">Tambah produk</a>
    </div>

    <table>
        <thead>
        <tr><th>Produk</th><th>Kategori</th><th>Harga dasar</th><th>Ukuran</th><th>Warna</th><th>Area</th><th>Preview</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name }}</td>
                <td>Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                <td>{{ $product->options_count }}</td>
                <td>{{ $product->colors_count }}</td>
                <td>{{ $product->print_areas_count }}</td>
                <td>{!! $product->hasPreview() ? '<span class="badge ok">siap</span>' : '<span class="badge">belum</span>' !!}</td>
                <td>{!! $product->is_active ? '<span class="badge ok">aktif</span>' : '<span class="badge">nonaktif</span>' !!}</td>
                <td class="actions">
                    <a class="btn ghost sm" href="{{ route('admin.products.edit', $product) }}">Kelola</a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini beserta ukuran, warna, dan areanya?')">
                        @csrf @method('DELETE')
                        <button class="btn danger sm" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">Belum ada produk. Tambahkan kategori dulu bila belum ada.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
