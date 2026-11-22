@extends('layouts.app')

@section('content')
<div class="admin" id="admin-shell">
    <aside class="side" id="admin-side">
        <a class="admin-brand" href="{{ route('admin.orders.index') }}">
            <span class="admin-brand-mark" aria-hidden="true">K</span>
            <span>KAZOKU <b class="brand-gold">STORE</b></span>
        </a>
        <span class="admin-pill">Panel Admin</span>
        <a href="{{ route('admin.orders.index') }}"
            class="{{ request()->routeIs('admin.dashboard', 'admin.orders.*') ? 'active' : '' }}">Pesanan Baru</a>
        <a href="{{ route('admin.products.index') }}"
            class="{{ request()->routeIs('admin.products.*', 'admin.options.*', 'admin.colors.*', 'admin.areas.*') ? 'active' : '' }}">Kelola
            Produk</a>
        <a href="{{ route('admin.portfolios.index') }}"
            class="{{ request()->routeIs('admin.portfolios.*') ? 'active' : '' }}">Kelola Portofolio</a>
        <a href="{{ route('admin.testimonials.index') }}"
            class="{{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">Kelola Testimoni</a>
        <a href="{{ route('admin.categories.index') }}"
            class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Kelola Kategori</a>
        <a href="{{ route('admin.couriers.index') }}"
            class="{{ request()->routeIs('admin.couriers.*', 'admin.rates.*') ? 'active' : '' }}">Ekspedisi &amp;
            Tarif</a>
        <a href="{{ route('admin.settings.edit') }}"
            class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Pengaturan</a>
        <form class="admin-logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </aside>

    <section class="panel">
        <button type="button" class="side-toggle" id="side-toggle" aria-controls="admin-side" aria-expanded="true"
            aria-label="Tampilkan atau sembunyikan menu samping">
            <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
                <path d="M2 4.5h14M2 9h14M2 13.5h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    fill="none" />
            </svg>
        </button>

        @if ($errors->any())
        <div class="alert err">
            <strong>Periksa kembali isian Anda:</strong>
            <ul>
                @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('admin')
    </section>
</div>

<script>
(function() {
    var shell = document.getElementById('admin-shell');
    var btn = document.getElementById('side-toggle');
    if (!shell || !btn) return;
    btn.addEventListener('click', function() {
        var hidden = shell.classList.toggle('side-hidden');
        btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
    });
})();
</script>
@endsection