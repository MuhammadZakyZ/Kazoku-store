<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090909">
    <title>@yield('title', 'Kazoku Store')</title>
    <link rel="stylesheet" href="{{ asset('css/kazoku.css') }}">
</head>

<body
    class="{{ request()->routeIs('admin.*') ? 'admin-layout' : (request()->routeIs('login') ? 'login-layout' : '') }}">
    <header class="nav">
        <div class="nav-in">
            <a class="brand" href="{{ route('home') }}" aria-label="Kazoku Store — Beranda">
                <img src="{{ asset('images/kazoku-logo.png') }}" alt="Kazoku Digital Printing Garment" width="113"
                    height="54">
            </a>

            <nav class="menu" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'on' : '' }}"
                    aria-current="{{ request()->routeIs('home') ? 'page' : 'false' }}">Beranda</a>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'on' : '' }}"
                    aria-current="{{ request()->routeIs('products.*') ? 'page' : 'false' }}">Produk</a>
                <a href="{{ route('portfolios.index') }}" class="{{ request()->routeIs('portfolios.*') ? 'on' : '' }}"
                    aria-current="{{ request()->routeIs('portfolios.*') ? 'page' : 'false' }}">Portofolio</a>
                <a href="{{ route('testimonials.index') }}"
                    class="{{ request()->routeIs('testimonials.*') ? 'on' : '' }}"
                    aria-current="{{ request()->routeIs('testimonials.*') ? 'page' : 'false' }}">Testimoni</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'on' : '' }}"
                    aria-current="{{ request()->routeIs('contact') ? 'page' : 'false' }}">Profil &amp; Kontak</a>
            </nav>

            <div class="acct" aria-label="Menu akun">
                @auth
                @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.orders.index') }}" class="btn ghost">Panel Admin</a>
                @endif
                <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'on' : '' }}"
                    aria-current="{{ request()->routeIs('orders.*') ? 'page' : 'false' }}">Pesanan Saya</a>
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn ghost">Keluar</button>
                </form>
                @else
                <a class="btn ghost account-cta" href="{{ route('login') }}">Masuk / Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    @auth
    @unless (auth()->user()->hasVerifiedEmail())
    <div class="banner">
        Email Anda belum terverifikasi. Anda belum dapat mengirim pesanan.
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn link">Kirim ulang tautan verifikasi</button>
        </form>
    </div>
    @endunless
    @endauth

    <main class="wrap">
        @if (session('status') && !(request()->routeIs('orders.show') && str_starts_with(session('status'), 'Pesanan
        berhasil dibuat.')))
        <div class="alert ok">{{ session('status') }}</div>
        @endif

        @if (session('error'))
        <div class="alert err">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    @unless (request()->routeIs('admin.*') || request()->routeIs('login'))
    @include('partials.footer')
    @endunless
</body>

</html>