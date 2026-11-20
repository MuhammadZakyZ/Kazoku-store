@extends('layouts.admin')

@section('title', 'Pesanan — Panel Admin')

@section('admin')
    <h1>Pesanan</h1>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="row-form">
        <div class="f">
            <label for="q">Cari kode, nama, atau email</label>
            <input id="q" type="text" name="q" value="{{ $q }}" maxlength="100">
        </div>
        <div class="f">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">Terapkan</button>
        <a class="btn ghost" href="{{ route('admin.orders.index') }}">Reset</a>
    </form>

    <table class="section">
        <thead>
        <tr><th>Kode</th><th>Tanggal</th><th>Pemesan</th><th>Produk</th><th class="r">Total</th><th>Status</th><th>Pembayaran</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($orders as $order)
            <tr>
                <td><strong>{{ $order->code }}</strong></td>
                <td>{{ $order->created_at->format('d M Y') }}</td>
                <td>{{ $order->user->name }}</td>
                <td>{{ $order->items->first()?->product?->name ?? '—' }}</td>
                <td class="r">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                <td><span class="badge {{ $order->status->value === 'selesai' ? 'ok' : ($order->status->value === 'dibatalkan' ? 'bad' : '') }}">{{ $order->status->label() }}</span></td>
                <td>
                    <span class="badge {{ $order->payment_status->value === 'lunas' ? 'ok' : '' }}">{{ $order->payment_status->label() }}</span>
                    @if ($order->waiting_payments_count > 0)<span class="badge bad">bukti menunggu</span>@endif
                </td>
                <td><a class="btn ghost sm" href="{{ route('admin.orders.show', $order) }}">Buka</a></td>
            </tr>
        @empty
            <tr><td colspan="8">Tidak ada pesanan yang cocok.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $orders->links('pagination.kazoku') }}
@endsection
