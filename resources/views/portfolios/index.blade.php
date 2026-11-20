@extends('layouts.app')

@section('title', 'Portofolio — Kazoku Store')

@section('content')
    <h1>Portofolio</h1>

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
        <p class="empty">Belum ada portofolio yang cocok.</p>
    @else
        <div class="cards">
            @foreach ($portfolios as $portfolio)
                @include('partials.portfolio-card', ['portfolio' => $portfolio])
            @endforeach
        </div>

        {{ $portfolios->links('pagination.kazoku') }}
    @endif
@endsection
