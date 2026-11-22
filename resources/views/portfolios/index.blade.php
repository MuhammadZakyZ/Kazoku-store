@extends('layouts.app')

@section('title', 'Portofolio — Kazoku Store')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">Dibuat dengan cerita</span>
            <h1>Portofolio</h1>
            <p>Lihat berbagai ide yang sudah diwujudkan menjadi karya custom.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert err">
            <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" action="{{ route('portfolios.index') }}" class="filters">
        <div class="f">
            <label for="q">Kata kunci</label>
            <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100">
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
        <div class="actions">
            <button class="btn" type="submit">Terapkan</button>
            <a class="btn ghost" href="{{ route('portfolios.index') }}">Reset</a>
        </div>
    </form>

    @if ($portfolios->isEmpty())
        <div class="empty-state">
            <span class="empty-icon" aria-hidden="true">✳</span>
            <h2>Karya belum ditemukan</h2>
            <p>Coba kata kunci lain atau lihat semua karya yang tersedia.</p>
            <a class="btn ghost" href="{{ route('portfolios.index') }}">Lihat semua karya</a>
        </div>
    @else
        <div class="cards portfolio-grid">
            @foreach ($portfolios as $portfolio)
                @include('partials.portfolio-card', ['portfolio' => $portfolio])
            @endforeach
        </div>

        {{ $portfolios->links('pagination.kazoku') }}
    @endif
@endsection
