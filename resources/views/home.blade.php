@extends('layouts.app')

@section('title', 'Kazoku Store — Jasa Custom Printing')

@section('content')
    <section class="hero">
        <div class="hero-copy">
            <span class="hero-kicker">Cetak custom, ceritamu</span>
            <h1>Ide kamu,<br>jadi nyata.</h1>
            <p>Buat produk custom yang terasa personal. Pilih produk, kirim desain, dan pantau pesanan dalam satu tempat.</p>
            <div class="actions">
                <a class="btn" href="{{ route('products.index') }}">Jelajahi produk <span aria-hidden="true">→</span></a>
                @if ($whatsapp)
                    <a class="btn ghost" href="{{ $whatsapp }}" target="_blank" rel="noopener">Tanya lewat WhatsApp</a>
                @endif
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            @if ($products->isNotEmpty() && $products->first()->image)
                <img class="hero-image" src="{{ asset('storage/'.$products->first()->image) }}" alt="">
            @else
                <div class="hero-orbit"></div>
                <span class="hero-dot"></span>
                <div class="hero-sample"><strong>KZK</strong><span>custom studio</span></div>
                <div class="hero-note"><b>100%</b> sesuai gayamu</div>
            @endif
        </div>
    </section>

    <section class="block">
        <div class="bhead"><div><span class="eyebrow">Pilih favoritmu</span><h2>Produk pilihan</h2></div><a href="{{ route('products.index') }}">Lihat semua produk →</a></div>
        @if ($products->isEmpty())
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true">K</span>
                <h2>Produk segera hadir</h2>
                <p>Kami sedang menyiapkan pilihan produk custom terbaik untukmu.</p>
            </div>
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
            <div class="bhead"><div><span class="eyebrow">Karya terbaru</span><h2>Inspirasi dari pelanggan</h2></div><a href="{{ route('portfolios.index') }}">Jelajahi portofolio →</a></div>
            <div class="cards">
                @foreach ($portfolios as $portfolio)
                    @include('partials.portfolio-card', ['portfolio' => $portfolio])
                @endforeach
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="block">
            <div class="bhead"><div><span class="eyebrow">Cerita mereka</span><h2>Kata pelanggan</h2></div><a href="{{ route('testimonials.index') }}">Baca cerita lainnya →</a></div>
            <div class="quotes">
                @foreach ($testimonials as $testimonial)
                    @include('partials.testimonial-card', ['testimonial' => $testimonial])
                @endforeach
            </div>
        </section>
    @endif
@endsection
