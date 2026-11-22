<footer class="foot">
    <div class="foot-in">
        <div class="foot-main">
            <section class="foot-about" aria-label="Tentang Kazoku Store">
                <a class="foot-brand" href="{{ route('home') }}" aria-label="Kazoku Store — Beranda">
                    <img src="{{ asset('images/kazoku-logo.png') }}" alt="Kazoku Digital Printing Garment" width="92" height="44" loading="lazy">
                </a>
                <p>Jasa custom printing untuk kaos dan produk sablon. Pilih produk, unggah desain, dan pantau pesananmu dalam satu tempat.</p>
            </section>

            <nav class="foot-column" aria-label="Jelajahi">
                <h2>Jelajahi</h2>
                <a href="{{ route('products.index') }}">Produk</a>
                <a href="{{ route('portfolios.index') }}">Portofolio</a>
                <a href="{{ route('testimonials.index') }}">Testimoni</a>
                <a href="{{ route('contact') }}">Profil &amp; Kontak</a>
            </nav>

            <nav class="foot-column" aria-label="Hubungi kami">
                <h2>Hubungi kami</h2>
                <a href="{{ $footerWhatsapp ?? route('contact') }}" @if ($footerWhatsapp) target="_blank" rel="noopener" @endif>WhatsApp usaha</a>
                @if ($footerEmail)
                    <a href="mailto:{{ $footerEmail }}">Email usaha</a>
                @endif
                <a href="{{ $footerMap ?? route('contact') }}" @if ($footerMap) target="_blank" rel="noopener" @endif>Alamat &amp; peta</a>
            </nav>
        </div>

        <div class="foot-bottom">
            <span>© {{ now()->year }} CV Kazoku Advertising · Kazoku Store</span>
        </div>
    </div>
</footer>
