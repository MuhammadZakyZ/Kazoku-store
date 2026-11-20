@extends('layouts.app')

@section('title', 'Kazoku Store — Jasa Custom Printing')

@section('content')
    <section class="hero">
        <h1>Kazoku Store</h1>
        <p>Jasa custom printing. Pilih produk, unggah desain Anda, lalu pantau pesanan dari satu tempat.</p>
        <div class="actions">
            <a class="btn" href="{{ route('products.index') }}">Lihat produk</a>
            @if ($whatsapp)
                <a class="btn ghost" href="{{ $whatsapp }}" target="_blank" rel="noopener">Tanya lewat WhatsApp</a>
            @endif
        </div>
    </section>

    <section class="block">
        <div class="bhead"><h2>Produk</h2><a href="{{ route('products.index') }}">Semua produk →</a></div>
        @if ($products->isEmpty())
            <p class="empty">Produk akan segera tersedia.</p>
        @else
            <div class="cards">
                @foreach ($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </section>

    @if ($portfolios->isNotEmpty())
        <section class="block">
            <div class="bhead"><h2>Portofolio</h2><a href="{{ route('portfolios.index') }}">Semua portofolio →</a></div>
            <div class="cards">
                @foreach ($portfolios as $portfolio)
                    @include('partials.portfolio-card', ['portfolio' => $portfolio])
                @endforeach
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="block">
            <div class="bhead"><h2>Kata pelanggan</h2><a href="{{ route('testimonials.index') }}">Semua testimoni →</a></div>
            <div class="quotes">
                @foreach ($testimonials as $testimonial)
                    @include('partials.testimonial-card', ['testimonial' => $testimonial])
                @endforeach
            </div>
        </section>
    @endif
@endsection
