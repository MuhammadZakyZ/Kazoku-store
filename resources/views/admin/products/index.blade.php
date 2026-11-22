@extends('layouts.admin')

@section('title', 'Produk — Panel Admin')

@section('admin')
    <div class="head product-head">
        <div>
            <h1>Produk</h1>
            <p class="hint">{{ $products->count() }} produk terdaftar. Klik Kelola untuk mengatur ukuran, warna, dan area sablon.</p>
        </div>
        <a class="btn" href="{{ route('admin.products.create') }}">Tambah produk</a>
    </div>

    <div class="table-wrap">
        <table class="product-table">
            <thead>
            <tr>
                <th>Produk</th>
                <th>Kategori</th>
                <th class="r">Harga dasar</th>
                <th class="c">Ukuran</th>
                <th class="c">Warna</th>
                <th class="c">Area</th>
                <th>Preview</th>
                <th>Status</th>
                <th><span class="sr-only">Aksi</span></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>
                        <div class="product-cell">
                            @if ($product->image)
                                <img class="product-thumb" src="{{ asset('storage/'.$product->image) }}" alt="">
                            @else
                                <span class="product-thumb product-thumb-ph" aria-hidden="true">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                            @endif
                            <strong>{{ $product->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $product->category->name }}</td>
                    <td class="r nowrap">Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                    <td class="c">{{ $product->options_count }}</td>
                    <td class="c">{{ $product->colors_count }}</td>
                    <td class="c">{{ $product->print_areas_count }}</td>
                    <td>{!! $product->hasPreview() ? '<span class="badge ok">Siap</span>' : '<span class="badge warn">Belum</span>' !!}</td>
                    <td>{!! $product->is_active ? '<span class="badge ok">Aktif</span>' : '<span class="badge">Nonaktif</span>' !!}</td>
                    <td>
                        <div class="actions nowrap">
                            <a class="btn ghost sm" href="{{ route('admin.products.edit', $product) }}">Kelola</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini beserta ukuran, warna, dan areanya?')">
                                @csrf @method('DELETE')
                                <button class="btn danger sm" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty">Belum ada produk. Tambahkan kategori dulu bila belum ada.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
