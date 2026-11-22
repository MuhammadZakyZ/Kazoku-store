@extends('layouts.app')

@section('content')
<div class="admin">
    <aside class="side">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            <span class="admin-brand-mark" aria-hidden="true">K</span>
            <span>Kazoku Store</span>
        </a>
        <a href="{{ route('admin.dashboard') }}"
            class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Ringkasan</a>
        <a href="{{ route('admin.orders.index') }}"
            class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Pesanan</a>
        <a href="{{ route('admin.products.index') }}"
            class="{{ request()->routeIs('admin.products.*', 'admin.options.*', 'admin.colors.*', 'admin.areas.*') ? 'active' : '' }}">Produk</a>
        <a href="{{ route('admin.categories.index') }}"
            class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Kategori</a>
        <a href="{{ route('admin.portfolios.index') }}"
            class="{{ request()->routeIs('admin.portfolios.*') ? 'active' : '' }}">Portofolio</a>
        <a href="{{ route('admin.testimonials.index') }}"
            class="{{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">Testimoni</a>
        <a href="{{ route('admin.couriers.index') }}"
            class="{{ request()->routeIs('admin.couriers.*', 'admin.rates.*') ? 'active' : '' }}">Ekspedisi &amp;
            tarif</a>
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