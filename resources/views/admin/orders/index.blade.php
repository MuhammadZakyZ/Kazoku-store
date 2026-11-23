@extends('layouts.admin')

@section('title', 'Pesanan Baru — Panel Admin')

@section('admin')
@php
$statusStyle = [
'baru' => ['', '●'],
'menunggu_verifikasi' => ['gold', '◷'],
'konsultasi' => ['gold', '◷'],
'diproses' => ['', '→'],
'siap_diambil' => ['', '→'],
'dikirim' => ['', '→'],
'selesai' => ['green', '✓'],
'dibatalkan' => ['red', '✕'],
];
$paymentStyle = [
'belum_bayar' => ['', '●'],
'dp' => ['gold', '◷'],
'lunas' => ['green', '✓'],
];
$summaryCards = [
['value' => 'baru', 'label' => 'Pesanan baru'],
['value' => 'menunggu_verifikasi', 'label' => 'Menunggu verifikasi'],
['value' => 'diproses', 'label' => 'Diproses'],
['value' => 'dibatalkan', 'label' => 'Dibatalkan'],
];
@endphp

<div class="orders-head">
    <span class="eyebrow">Panel Admin</span>
    <h1>Pesanan baru</h1>
    <p class="hint">Ringkasan jumlah pesanan, sorting berdasarkan status.</p>
</div>

<div class="order-stats">
    @foreach ($summaryCards as $card)
    <a class="order-stat {{ $loop->first ? 'hl' : '' }}"
        href="{{ route('admin.orders.index', array_filter(['status' => $card['value'], 'q' => $q])) }}">
        <strong>{{ $statusCounts[$card['value']] ?? 0 }}</strong>
        <span>{{ $card['label'] }}</span>
    </a>
    @endforeach
</div>

<div class="orders-toolbar">
    <nav class="status-pills" aria-label="Filter status pesanan">
        <a class="pill {{ $status === null ? 'on' : '' }}"
            href="{{ route('admin.orders.index', array_filter(['q' => $q])) }}">Semua</a>
        @foreach ($statuses as $s)
        <a class="pill {{ $status === $s->value ? 'on' : '' }}"
            href="{{ route('admin.orders.index', array_filter(['status' => $s->value, 'q' => $q])) }}">{{ $s->label() }}</a>
        @endforeach
    </nav>

    <form class="order-search" method="GET" action="{{ route('admin.orders.index') }}" role="search">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <label class="sr-only" for="q">Cari kode, nama, atau email</label>
        <input id="q" type="text" name="q" value="{{ $q }}" maxlength="100" placeholder="Cari kode / nama / email">
        <button class="btn ghost sm" type="submit">Cari</button>
    </form>
</div>

<div class="orders-card">
    <table class="order-table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Pembayaran</th>
                <th>Status</th>
                <th><span class="sr-only">Aksi</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
            @php
            [$sClass, $sIcon] = $statusStyle[$order->status->value] ?? ['', '●'];
            [$pClass, $pIcon] = $paymentStyle[$order->payment_status->value] ?? ['', '●'];
            $needsAction = in_array($order->status->value, ['baru', 'menunggu_verifikasi'], true);
            @endphp
            <tr>
                <td class="code">{{ $order->code }}</td>
                <td>{{ $order->user->name }}</td>
                <td class="nowrap">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                <td>
                    <span class="pb {{ $pClass }}"><i
                            aria-hidden="true">{{ $pIcon }}</i>{{ $order->payment_status->label() }}</span>
                    @if ($order->waiting_payments_count > 0)
                    <span class="pb red">Bukti menunggu</span>
                    @endif
                </td>
                <td><span class="pb {{ $sClass }}"><i
                            aria-hidden="true">{{ $sIcon }}</i>{{ $order->status->label() }}</span></td>
                <td class="act">
                    <a class="btn sm {{ $needsAction ? '' : 'ghost' }}"
                        href="{{ route('admin.orders.show', $order) }}">Buka</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty">Tidak ada pesanan yang cocok.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if (method_exists($orders, 'total'))
    <div class="orders-foot">
        Menampilkan {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} pesanan
    </div>
    @endif
</div>

{{ $orders->links('pagination.kazoku') }}
@endsection