@extends('layouts.app')

@section('title', 'Produk — Kazoku Store')

@section('content')
<div class="catalog-page">
    <div class="page-heading">
        <div>
            <span class="eyebrow">Katalog</span>
            <h1>Semua produk</h1>
            <p>Cari, saring berdasarkan kategori dan rentang harga, lalu pesan produk yang kamu butuhkan.</p>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert err">
        <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="catalog-panel">
        <form method="GET" action="{{ route('products.index') }}" class="catalog-form" role="search">
            <input type="hidden" name="kategori" value="{{ $filters['kategori'] ?? '' }}">

            <div class="search-field">
                <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                    <circle cx="7" cy="7" r="4.6" fill="none" stroke="currentColor" stroke-width="1.6" />
                    <path d="M10.5 10.5 14 14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <label class="sr-only" for="q">Kata kunci</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100"
                    placeholder="Cari produk, mis. kaos atau hoodie">
            </div>

            <div>
                <label class="sr-only" for="harga">Rentang harga</label>
                <select id="harga" name="harga" onchange="this.form.requestSubmit()">
                    <option value="">Rentang harga</option>
                    <option value="0-100000" @selected(($filters['harga'] ?? '' )==='0-100000' )>Di bawah Rp 100.000
                    </option>
                    <option value="100000-150000" @selected(($filters['harga'] ?? '' )==='100000-150000' )>Rp 100.000 –
                        150.000</option>
                    <option value="150000-250000" @selected(($filters['harga'] ?? '' )==='150000-250000' )>Rp 150.000 –
                        250.000</option>
                    <option value="250000-" @selected(($filters['harga'] ?? '' )==='250000-' )>Di atas Rp 250.000
                    </option>
                </select>
            </div>

            <button class="sr-only" type="submit">Cari</button>
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
    <p class="catalog-count">Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari
        {{ $products->total() }} produk</p>

    <div class="cards product-grid">
        @foreach ($products as $product)
        @include('partials.catalog-card', ['product' => $product])
        @endforeach
    </div>

    {{ $products->links('pagination.kazoku') }}

    <p class="catalog-note">Harga dikelola admin dan dapat berubah.</p>
    @endif
</div>

@if ($whatsapp)
<a class="wa-float" href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="Tanya lewat WhatsApp">
    <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
        <path
            d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H11l-4.2 3.6c-.5.4-1.3.1-1.3-.6V16h-.0A2.5 2.5 0 0 1 4 13.5z"
            fill="currentColor" />
    </svg>
</a>
@endif
@endsection