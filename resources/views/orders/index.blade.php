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
        <div class="panel">
            <table>
                <thead>
                <tr><th>Kode</th><th>Tanggal</th><th>Produk</th><th class="r">Total</th><th>Status</th><th>Pembayaran</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td><strong>{{ $order->code }}</strong></td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>{{ $order->items->first()?->product?->name ?? '—' }}</td>
                        <td class="r">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $order->status->value === 'selesai' ? 'ok' : ($order->status->value === 'dibatalkan' ? 'bad' : '') }}">{{ $order->status->label() }}</span></td>
                        <td><span class="badge {{ $order->payment_status->value === 'lunas' ? 'ok' : '' }}">{{ $order->payment_status->label() }}</span></td>
                        <td><a class="btn ghost sm" href="{{ route('orders.show', $order) }}">Lihat</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $orders->links('pagination.kazoku') }}
    @endif
@endsection
