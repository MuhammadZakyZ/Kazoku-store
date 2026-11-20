<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kazoku Store')</title>
    <link rel="stylesheet" href="{{ asset('css/kazoku.css') }}">
</head>

<body>
    <header class="nav">
        <div class="nav-in">
            <a class="brand" href="{{ route('home') }}">Kazoku Store</a>

            <nav class="menu">
                <a href="{{ route('products.index') }}"
                    class="{{ request()->routeIs('products.*') ? 'on' : '' }}">Produk</a>
                <a href="{{ route('portfolios.index') }}"
                    class="{{ request()->routeIs('portfolios.*') ? 'on' : '' }}">Portofolio</a>
                <a href="{{ route('testimonials.index') }}"
                    class="{{ request()->routeIs('testimonials.*') ? 'on' : '' }}">Testimoni</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'on' : '' }}">Kontak</a>
            </nav>

            <div class="acct">
                @auth
                @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}">Panel Admin</a>
                @endif
                <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'on' : '' }}">Pesanan
                    Saya</a>
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn ghost">Keluar</button>
                </form>
                @else
                <a href="{{ route('login') }}">Masuk</a>
                <a class="btn" href="{{ route('register') }}">Daftar</a>
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
        @if (session('status'))
        <div class="alert ok">{{ session('status') }}</div>
        @endif

        @if (session('error'))
        <div class="alert err">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="foot">
        <div class="foot-in">
            <span>© {{ now()->year }} Kazoku Store</span>
            <a href="{{ route('contact') }}">Kontak &amp; alamat</a>
        </div>
    </footer>
</body>

</html>