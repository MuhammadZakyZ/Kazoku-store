@extends('layouts.app')

@section('title', 'Produk — Kazoku Store')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">Katalog</span>
            <h1>Semua produk</h1>
            <p>Pilih produk favorit, lalu buat jadi milikmu dengan desain yang paling kamu suka.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert err">
            <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="catalog-filters">
        <form method="GET" action="{{ route('products.index') }}" class="filters catalog-search">
            <input type="hidden" name="kategori" value="{{ $filters['kategori'] ?? '' }}">
            <div class="f">
                <label class="sr-only" for="q">Kata kunci</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Cari produk, misalnya kaos">
            </div>
            <button class="btn" type="submit">Cari</button>
            <details class="price-filter">
                <summary>Filter harga</summary>
                <div class="price-fields">
                    <div class="f">
                        <label for="harga_min">Harga minimum (Rp)</label>
                        <input id="harga_min" type="number" name="harga_min" min="0" step="1" value="{{ $filters['harga_min'] ?? '' }}">
                    </div>
                    <div class="f">
                        <label for="harga_max">Harga maksimum (Rp)</label>
                        <input id="harga_max" type="number" name="harga_max" min="0" step="1" value="{{ $filters['harga_max'] ?? '' }}">
                    </div>
                    <button class="btn ghost sm" type="submit">Terapkan</button>
                </div>
            </details>
        </form>

        <nav class="category-pills" aria-label="Filter kategori produk">
            <a class="{{ empty($filters['kategori']) ? 'active' : '' }}"
                href="{{ route('products.index', request()->except('kategori', 'page')) }}">Semua</a>
            @foreach ($categories as $category)
                <a class="{{ ($filters['kategori'] ?? '') == $category->id ? 'active' : '' }}"
                    href="{{ route('products.index', array_merge(request()->except('page'), ['kategori' => $category->id])) }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    </div>

    @if ($products->isEmpty())
        <div class="empty-state">
            <span class="empty-icon" aria-hidden="true">⌕</span>
            <h2>Produk tidak ditemukan</h2>
            <p>Coba ubah kata kunci atau filter pencarian untuk menemukan produk yang sesuai.</p>
            <a class="btn ghost" href="{{ route('products.index') }}">Reset filter</a>
        </div>
    @else
        <div class="cards product-grid">
            @foreach ($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>

        {{ $products->links('pagination.kazoku') }}
    @endif
@endsection
