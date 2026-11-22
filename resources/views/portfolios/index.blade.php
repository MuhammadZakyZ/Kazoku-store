@extends('layouts.app')

@section('title', 'Portofolio — Kazoku Store')

@section('content')
<div class="catalog-page portfolio-page">
    <div class="page-heading">
        <div>
            <span class="eyebrow">Portofolio</span>
            <h1>Karya yang sudah kami cetak</h1>
            <p>Telusuri hasil sablon dari berbagai produk dan jumlah area.</p>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert err">
        <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="catalog-panel">
        <form method="GET" action="{{ route('portfolios.index') }}" class="catalog-form" role="search">
            <input type="hidden" name="kategori" value="{{ $filters['kategori'] ?? '' }}">

            <div class="search-field">
                <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                    <circle cx="7" cy="7" r="4.6" fill="none" stroke="currentColor" stroke-width="1.6" />
                    <path d="M10.5 10.5 14 14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <label class="sr-only" for="q">Kata kunci</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100"
                    placeholder="Cari karya, mis. jersey atau kemeja">
            </div>

            <button class="sr-only" type="submit">Cari</button>
        </form>

        <nav class="category-pills" aria-label="Filter kategori karya">
            <a class="{{ empty($filters['kategori']) ? 'active' : '' }}"
                href="{{ route('portfolios.index', request()->except('kategori', 'page')) }}">Semua</a>
            @foreach ($categories as $category)
            <a class="{{ ($filters['kategori'] ?? '') == $category->id ? 'active' : '' }}"
                href="{{ route('portfolios.index', array_merge(request()->except('page'), ['kategori' => $category->id])) }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    </div>

    @if ($portfolios->isEmpty())
    <div class="empty-state">
        <span class="empty-icon" aria-hidden="true">✳</span>
        <h2>Karya belum ditemukan</h2>
        <p>Coba kata kunci lain atau lihat semua karya yang tersedia.</p>
        <a class="btn ghost" href="{{ route('portfolios.index') }}">Lihat semua karya</a>
    </div>
    @else
    <p class="catalog-count">Menampilkan {{ $portfolios->firstItem() }}–{{ $portfolios->lastItem() }} dari
        {{ $portfolios->total() }} karya</p>

    <div class="cards portfolio-grid">
        @foreach ($portfolios as $portfolio)
        @include('partials.portfolio-tile', ['portfolio' => $portfolio])
        @endforeach
    </div>

    {{ $portfolios->links('pagination.kazoku') }}
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