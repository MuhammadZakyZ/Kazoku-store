@extends('layouts.app')

@section('title', 'Produk — Kazoku Store')

@section('content')
    <h1>Produk</h1>

    @if ($errors->any())
        <div class="alert err">
            <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" action="{{ route('products.index') }}" class="filters">
        <div class="f">
            <label for="q">Kata kunci</label>
            <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="mis. kaos">
        </div>
        <div class="f">
            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori">
                <option value="">Semua</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['kategori'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="f s">
            <label for="harga_min">Harga min (Rp)</label>
            <input id="harga_min" type="number" name="harga_min" min="0" step="1" value="{{ $filters['harga_min'] ?? '' }}">
        </div>
        <div class="f s">
            <label for="harga_max">Harga maks (Rp)</label>
            <input id="harga_max" type="number" name="harga_max" min="0" step="1" value="{{ $filters['harga_max'] ?? '' }}">
        </div>
        <div class="actions">
            <button class="btn" type="submit">Terapkan</button>
            <a class="btn ghost" href="{{ route('products.index') }}">Reset</a>
        </div>
    </form>

    @if ($products->isEmpty())
        <p class="empty">Tidak ada produk yang cocok dengan filter Anda.</p>
    @else
        <div class="cards">
            @foreach ($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>

        {{ $products->links('pagination.kazoku') }}
    @endif
@endsection
