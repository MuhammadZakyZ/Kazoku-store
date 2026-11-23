@extends('layouts.app')

@section('title', 'Pesanan Saya — Kazoku Store')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Semua dalam satu tempat</span>
        <h1>Pesanan saya</h1>
        <p>Pantau status pesanan dan pembayaranmu di sini.</p>
    </div>
    <a class="btn ghost" href="{{ route('products.index') }}">Jelajahi produk</a>
</div>

@if ($orders->isEmpty())
<div class="empty-state">
    <span class="empty-icon" aria-hidden="true">♡</span>
    <h2>Belum ada pesanan</h2>
    <p>Pesanan custom pertamamu dimulai dari sini. Temukan produk dan buat sesuatu yang spesial.</p>
    <a class="btn" href="{{ route('products.index') }}">Lihat produk</a>
</div>
@else
<div class="order-list">
    @foreach ($orders as $order)
    @php
    $statusValue = $order->status->value;
    $paymentValue = $order->payment_status->value;

    // Warna badge status pesanan
    $statusClass = match (true) {
    $statusValue === 'selesai' => 'ok',
    $statusValue === 'dibatalkan' => 'bad',
    str_contains($statusValue, 'menunggu'), str_contains($statusValue, 'konsultasi') => 'warn',
    default => '',
    };
    $statusIcon = match ($statusClass) {
    'ok' => '✓',
    'bad' => '✕',
    'warn' => '◷',
    default => '•',
    };

    // Warna badge status pembayaran
    $paymentClass = match (true) {
    $paymentValue === 'lunas' => 'ok',
    $paymentValue === 'dp' => 'warn',
    default => '',
    };
    $paymentIcon = match ($paymentClass) {
    'ok' => '✓',
    'warn' => '◷',
    default => '•',
    };

    $firstItem = $order->items->first();
    $productName = $firstItem?->product?->name ?? '—';
    $extraItems = $order->items->count() - 1;
    $totalQty = $order->items->sum('quantity');
    $areaCount = $order->designs->filter(fn ($d) => $d->type->value === 'pelanggan')->count();
    @endphp

    <article class="order-card">
        <div class="order-card-info">
            <strong class="order-card-code">{{ $order->code }}</strong>
            <span class="order-card-meta">
                {{ $productName }}@if ($extraItems > 0) +{{ $extraItems }} lainnya @endif
                × {{ $totalQty }}@if ($areaCount > 0) · {{ $areaCount }} area @endif
            </span>
            <span class="order-card-meta">Dibuat {{ $order->created_at->translatedFormat('j M Y') }}</span>
        </div>

        <div class="order-card-badges">
            <span class="order-badge {{ $statusClass }}">
                <span aria-hidden="true">{{ $statusIcon }}</span> {{ $order->status->label() }}
            </span>
            <span class="order-badge {{ $paymentClass }}">
                <span aria-hidden="true">{{ $paymentIcon }}</span> {{ $order->payment_status->label() }}
            </span>
        </div>

        <div class="order-card-total">
            <span>Total</span>
            <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
        </div>

        <a class="btn ghost order-card-btn" href="{{ route('orders.show', $order) }}">Lihat detail</a>
    </article>
    @endforeach
</div>

{{ $orders->links('pagination.kazoku') }}
@endif
@endsection