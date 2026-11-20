@extends('layouts.app')

@section('title', 'Pesanan Saya — Kazoku Store')

@section('content')
    <h1>Pesanan Saya</h1>

    @if ($orders->isEmpty())
        <p class="empty">Anda belum punya pesanan. <a href="{{ route('products.index') }}">Lihat produk</a>.</p>
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
