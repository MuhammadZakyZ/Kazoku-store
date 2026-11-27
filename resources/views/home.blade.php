@extends('layouts.app')

@section('title', 'Kazoku Store — Jasa Custom Printing')

@section('content')
<div class="hm-page">

    {{-- ============ Hero ============ --}}
    <section class="hm-hero">
        <div class="hm-container hm-hero-in">
            <div class="hm-hero-copy">
                <span class="hm-kicker">Digital printing garment</span>
                <h1>Dari kain, jadi baju dengan desain</h1>
                <p>Pilih produk, unggah desainmu, atur posisi sablon, lalu pantau pesanan dalam satu tempat.</p>

                <div class="hm-actions">
                    <a class="btn" href="{{ route('products.index') }}">Mulai pesan</a>
                    <a class="btn ghost" href="{{ route('portfolios.index') }}">Lihat portofolio</a>
                </div>

                <ul class="hm-points">
                    <li><svg viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M3.5 8.5 6.5 11.5 12.5 4.5" />
                        </svg>Preview posisi sablon</li>
                    <li><svg viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M3.5 8.5 6.5 11.5 12.5 4.5" />
                        </svg>Bayar DP atau lunas</li>
                    <li><svg viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M3.5 8.5 6.5 11.5 12.5 4.5" />
                        </svg>Pantau status online</li>
                </ul>
            </div>

            <div class="hm-hero-photo" aria-hidden="true">
                @if ($heroImage)
                <img src="{{ $heroImage }}" alt="">
                @else
                <img class="hm-hero-logo" src="{{ asset('images/kazoku-logo.png') }}" alt="" width="430" height="206">
                @endif
            </div>
        </div>
    </section>

    {{-- ============ Keunggulan singkat ============ --}}
    <section class="hm-container hm-features" aria-label="Keunggulan">
        <div class="hm-feature">
            <span class="hm-icon"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M8 4 3.5 6.5 5 10l2-1v10h10V9l2 1 1.5-3.5L16 4a4 4 0 0 1-8 0z" />
                </svg></span>
            <div><strong>Pilih produk</strong>
                <p>Kaos, jersey, kemeja, hingga hoodie dengan banyak pilihan warna.</p>
            </div>
        </div>
        <div class="hm-feature">
            <span class="hm-icon"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 16V5m0 0-4 4m4-4 4 4M5 19h14" />
                </svg></span>
            <div><strong>Unggah desain</strong>
                <p>Kirim desainmu dan atur posisinya di atas mockup produk.</p>
            </div>
        </div>
        <div class="hm-feature">
            <span class="hm-icon"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z" />
                    <circle cx="12" cy="12" r="2.6" />
                </svg></span>
            <div><strong>Preview langsung</strong>
                <p>Lihat tampilan desain pada produk sebelum pesanan dikirim.</p>
            </div>
        </div>
        <div class="hm-feature">
            <span class="hm-icon"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="8.5" />
                    <path d="M12 7.5V12l3 2" />
                </svg></span>
            <div><strong>Pantau status</strong>
                <p>Ikuti perkembangan pesanan dari verifikasi hingga selesai.</p>
            </div>
        </div>
    </section>

    {{-- ============ Produk ============ --}}
    <section class="hm-section">
        <div class="hm-container">
            <div class="hm-head">
                <div>
                    <span class="hm-eyebrow">Pilih favoritmu</span>
                    <h2>Produk yang bisa dicetak</h2>
                </div>
                <a href="{{ route('products.index') }}">Lihat semua <span aria-hidden="true">›</span></a>
            </div>

            @if ($products->isEmpty())
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true">K</span>
                <h2>Produk segera hadir</h2>
                <p>Kami sedang menyiapkan pilihan produk custom terbaik untukmu.</p>
            </div>
            @else
            <div class="hm-products">
                @foreach ($products as $product)
                @include('partials.catalog-card', ['product' => $product])
                @endforeach
            </div>
            <p class="hm-note">Harga dikelola admin dan dapat berubah.</p>
            @endif
        </div>
    </section>

    {{-- ============ Alur ============ --}}
    <section class="hm-section hm-band">
        <div class="hm-container">
            <div class="hm-head">
                <div>
                    <span class="hm-eyebrow">Alur singkat</span>
                    <h2>Empat langkah, tanpa ribet</h2>
                </div>
            </div>
            <div class="hm-steps">
                <div class="hm-step"><b>01</b><strong>Pilih produk</strong>
                    <p>Tentukan produk, warna, ukuran, dan jumlah yang kamu butuhkan.</p>
                </div>
                <div class="hm-step"><b>02</b><strong>Unggah desain</strong>
                    <p>Pilih area sablon, unggah desain, lalu cek preview posisinya.</p>
                </div>
                <div class="hm-step"><b>03</b><strong>Bayar dan kirim bukti</strong>
                    <p>Bayar DP atau lunas sesuai pilihanmu, lalu unggah bukti transfer.</p>
                </div>
                <div class="hm-step"><b>04</b><strong>Pantau status</strong>
                    <p>Lihat progres pesanan sampai produk siap diambil atau dikirim.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Inspirasi (portofolio) ============ --}}
    @if ($portfolios->isNotEmpty())
    @php $featured = $portfolios->first(); @endphp
    <section class="hm-section">
        <div class="hm-container">
            <div class="hm-head">
                <div>
                    <span class="hm-eyebrow">Karya terbaru</span>
                    <h2>Inspirasi sablon dari pelanggan</h2>
                </div>
                <a href="{{ route('portfolios.index') }}">Lihat semua <span aria-hidden="true">›</span></a>
            </div>
            <a class="hm-showcase" href="{{ route('portfolios.index') }}">
                <img src="{{ asset('storage/'.$featured->image) }}" alt="{{ $featured->title }}" loading="lazy">
                <span class="hm-showcase-cap">
                    <strong>{{ $featured->title }}</strong>
                    <small>{{ $featured->category->name }}</small>
                </span>
            </a>
        </div>
    </section>
    @endif

    {{-- ============ Testimoni ============ --}}
    @if ($testimonials->isNotEmpty())
    <section class="hm-section hm-band">
        <div class="hm-container">
            <div class="hm-head">
                <div>
                    <span class="hm-eyebrow">Ulasan</span>
                    <h2>Kata mereka yang sudah memesan</h2>
                </div>
                <a href="{{ route('testimonials.index') }}">Lihat semua <span aria-hidden="true">›</span></a>
            </div>
            <div class="hm-quotes">
                @foreach ($testimonials as $testimonial)
                <blockquote class="hm-quote">
                    <p>“{{ $testimonial->message }}”</p>
                    <footer>
                        <span class="hm-avatar"
                            aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($testimonial->customer_name, 0, 1)) }}</span>
                        <span>
                            <strong>{{ $testimonial->customer_name }}</strong>
                            <small
                                aria-label="Rating {{ $testimonial->rating }} dari 5">{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</small>
                        </span>
                    </footer>
                </blockquote>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ============ Ajakan ============ --}}
    <section class="hm-section hm-cta-wrap">
        <div class="hm-container">
            <div class="hm-cta">
                <div>
                    <h2>Siap mencetak desainmu?</h2>
                    <p>Mulai pesan sekarang, atau tanya dulu kebutuhanmu ke admin.</p>
                </div>
                <div class="hm-actions">
                    <a class="btn" href="{{ route('products.index') }}">Mulai pesan</a>
                    <a class="btn ghost hm-cta-ghost" href="{{ $whatsapp ?? route('contact') }}" @if ($whatsapp)
                        target="_blank" rel="noopener" @endif>Hubungi kami</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection