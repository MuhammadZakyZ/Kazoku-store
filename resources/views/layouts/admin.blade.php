@extends('layouts.app')

@section('content')
<div class="admin" id="admin-shell">
    <aside class="side" id="admin-side">
        <a class="admin-brand" href="{{ route('admin.orders.index') }}" aria-label="Kazoku Store — Pesanan">
            <img src="{{ asset('images/kazoku-logo.png') }}" alt="Kazoku Digital Printing Garment" width="100"
                height="48">
        </a>
        <span class="admin-pill">Panel Admin</span>
        <a href="{{ route('admin.orders.index') }}"
            class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Pesanan Baru</a>
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
@endsection