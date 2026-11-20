@extends('layouts.admin')

@section('title', 'Pesanan '.$order->code.' — Panel Admin')

@section('admin')
<div class="head">
    <h1>{{ $order->code }}</h1>
    <div class="actions">
        <span
            class="badge {{ $order->status->value === 'selesai' ? 'ok' : ($order->status->value === 'dibatalkan' ? 'bad' : '') }}">{{ $order->status->label() }}</span>
        <span
            class="badge {{ $order->payment_status->value === 'lunas' ? 'ok' : '' }}">{{ $order->payment_status->label() }}</span>
        <a class="btn ghost sm" href="{{ route('admin.orders.index') }}">Kembali</a>
    </div>
</div>
<p class="hint">Dibuat {{ $order->created_at->format('d M Y, H:i') }} WIB · <a
        href="{{ route('orders.invoice', $order) }}" target="_blank" rel="noopener">Nota</a></p>

@if ($order->status->value === 'dibatalkan')
<div class="alert err"><strong>Dibatalkan.</strong> {{ $order->cancel_reason }}</div>
@endif

{{-- ===== Ubah status ===== --}}
@unless ($closed)
<div class="box-action">
    <h2>Ubah status</h2>
    @if ($statusOptions->isEmpty())
    <p class="hint">Tidak ada perubahan status yang tersedia dari status ini.</p>
    @else
    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="row-form" id="status-form">
        @csrf
        <div class="f">
            <label for="status">Status berikutnya</label>
            <select id="status" name="status" required>
                @foreach ($statusOptions as $opt)
                <option value="{{ $opt['value'] }}" @disabled($opt['blocker']) @selected(old('status')===$opt['value'])>
                    {{ $opt['label'] }}@if ($opt['blocker']) — tidak bisa: {{ $opt['blocker'] }}@endif
                </option>
                @endforeach
            </select>
        </div>
        <div class="f" id="resi-box" hidden>
            <label for="tracking_no">Nomor resi</label>
            <input id="tracking_no" type="text" name="tracking_no" maxlength="100"
                value="{{ old('tracking_no', $order->tracking_no) }}">
        </div>
        <button class="btn" type="submit">Ubah status</button>
    </form>
    <p class="hint">Diproses: pembayaran terverifikasi dan desain siap. Dikirim: nomor resi wajib. Selesai: pesanan
        harus lunas.</p>
    @endif
</div>
@endunless

<h2 class="section">Pemesan</h2>
<p>
    {{ $order->user->name }}<br>
    <span class="hint">{{ $order->user->email }} · {{ $order->user->phone }}
        @if ($customerWhatsapp)· <a href="{{ $customerWhatsapp }}" target="_blank" rel="noopener">Chat
            WhatsApp</a>@endif
    </span>
</p>

<h2 class="section">Produk</h2>
<table class="mini">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Ukuran</th>
            <th>Warna</th>
            <th class="r">Jumlah</th>
            <th class="r">Harga satuan</th>
            <th class="r">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->items as $row)
        <tr>
            <td>{{ $row->product->name }}</td>
            <td>{{ $row->option_name ?? '—' }}</td>
            <td>{{ $row->color_name ?? '—' }}</td>
            <td class="r">{{ $row->quantity }}</td>
            <td class="r">Rp {{ number_format($row->unit_price, 0, ',', '.') }}</td>
            <td class="r">Rp {{ number_format($row->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@php
$customerDesigns = $order->designs->filter(fn ($d) => $d->type->value === 'pelanggan');
$finalDesigns = $order->designs->filter(fn ($d) => $d->type->value === 'final');
@endphp

{{-- ===== Desain ===== --}}
<h2 class="section">Desain ({{ $order->design_type->label() }})</h2>
@if ($customerDesigns->isNotEmpty())
<table class="mini">
    <thead>
        <tr>
            <th>Area</th>
            <th>Ukuran</th>
            <th class="r">Biaya</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($customerDesigns as $design)
        <tr>
            <td>{{ $design->area_name }}</td>
            <td>{{ $design->print_size }}</td>
            <td class="r">
                {{ $design->area_price > 0 ? 'Rp '.number_format($design->area_price, 0, ',', '.') : 'Gratis' }}</td>
            <td class="r"><a href="{{ route('files.design', [$order, $design]) }}">Unduh file</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p class="hint">Jalur konsultasi: belum ada file dari pelanggan.</p>
@endif

<h3>Desain final</h3>
@if ($finalDesigns->isNotEmpty())
<table class="mini">
    <tbody>
        @foreach ($finalDesigns as $design)
        <tr>
            <td>{{ $design->area_name ?: 'Desain final' }} @if ($design->print_size)<span
                    class="hint">({{ $design->print_size }})</span>@endif</td>
            <td class="r">
                <a href="{{ route('files.design', [$order, $design]) }}">Unduh</a>
                @unless ($closed)
                <form method="POST" action="{{ route('admin.orders.finalDesign.destroy', [$order, $design]) }}"
                    class="inline" onsubmit="return confirm('Hapus desain final ini?')">
                    @csrf @method('DELETE')
                    <button class="btn danger sm" type="submit">Hapus</button>
                </form>
                @endunless
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p class="hint">Belum ada desain final.</p>
@endif

@unless ($closed)
<form method="POST" action="{{ route('admin.orders.finalDesign.store', $order) }}" enctype="multipart/form-data"
    class="row-form line add">
    @csrf
    <div class="f">
        <label for="area_id">Area (opsional)</label>
        <select id="area_id" name="area_id">
            <option value="">Umum (tanpa area)</option>
            @foreach ($areas as $area)
            <option value="{{ $area->id }}" @selected(old('area_id')==$area->id)>{{ $area->label }}</option>
            @endforeach
        </select>
    </div>
    <div class="f">
        <label for="file">File desain final</label>
        <input id="file" type="file" name="file" accept="image/png,image/jpeg,image/webp,application/pdf" required>
    </div>
    <button class="btn sm" type="submit">Unggah desain final</button>
</form>
<p class="hint">PNG, JPG, WEBP, atau PDF, maksimal 10 MB. Biaya area tidak dihitung otomatis; masukkan ke biaya revisi
    bila perlu.</p>
@endunless

{{-- ===== Pengiriman ===== --}}
<h2 class="section">Pengiriman</h2>
<p>
    {{ $order->delivery_method->label() }}
    @if ($order->shipping_destination)
    — {{ $order->courier?->name }}, {{ $order->shipping_destination }}<br>{!! nl2br(e($order->shipping_address)) !!}
    @endif
    @if ($order->tracking_no)<br>Resi: <strong>{{ $order->tracking_no }}</strong>@endif
</p>

{{-- ===== Pembayaran ===== --}}
<h2 class="section">Pembayaran</h2>
<table class="mini">
    <tbody>
        <tr>
            <td>Ongkir</td>
            <td class="r">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Biaya revisi ({{ $order->revision_count }} kali)</td>
            <td class="r">Rp {{ number_format($order->revision_fee, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total tagihan</strong></td>
            <td class="r"><strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Metode</td>
            <td class="r">{{ $order->payment_method->label() }}</td>
        </tr>
        <tr>
            <td>DP</td>
            <td class="r">Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Sisa tagihan</td>
            <td class="r">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

@if ($order->payments->isNotEmpty())
<table class="mini section">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th class="r">Jumlah</th>
            <th>Status</th>
            <th>Diperiksa</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->payments as $payment)
        <tr>
            <td>{{ $payment->created_at->format('d M Y, H:i') }}<div class="hint">{{ $payment->method->label() }}</div>
            </td>
            <td>{{ $payment->type->label() }}</td>
            <td class="r">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
            <td>
                <span
                    class="badge {{ $payment->status->value === 'terverifikasi' ? 'ok' : ($payment->status->value === 'ditolak' ? 'bad' : '') }}">{{ $payment->status->label() }}</span>
                @if ($payment->reject_reason)<div class="hint">{{ $payment->reject_reason }}</div>@endif
            </td>
            <td>@if ($payment->verifier){{ $payment->verifier->name }},
                {{ $payment->verified_at?->format('d M Y H:i') }}@else — @endif</td>
            <td class="r">@if ($payment->proof_file)<a
                    href="{{ route('files.proof', [$order, $payment]) }}">Bukti</a>@endif</td>
        </tr>
        @if ($payment->status->value === 'menunggu')
        <tr>
            <td colspan="6">
                <div class="row-form">
                    <form method="POST" action="{{ route('admin.orders.payments.verify', [$order, $payment]) }}"
                        onsubmit="return confirm('Verifikasi pembayaran ini?')">
                        @csrf
                        <button class="btn sm" type="submit">Verifikasi</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.payments.reject', [$order, $payment]) }}"
                        class="row-form">
                        @csrf
                        <div class="f"><input type="text" name="reject_reason" maxlength="500"
                                placeholder="Alasan penolakan (wajib)" required></div>
                        <button class="btn danger sm" type="submit">Tolak</button>
                    </form>
                </div>
            </td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@else
<p class="hint">Belum ada pembayaran dicatat.</p>
@endif

@if ($order->status->value !== 'dibatalkan' && $remaining > 0)
<div class="box-action">
    <h3>Tandai lunas</h3>
    @if ($pending)
    <p class="hint">Ada bukti yang menunggu. Verifikasi atau tolak dulu sebelum menandai lunas.</p>
    @else
    <form method="POST" action="{{ route('admin.orders.paid', $order) }}" class="row-form"
        onsubmit="return confirm('Tandai lunas sebesar Rp {{ number_format($remaining, 0, ',', '.') }}?')">
        @csrf
        <div class="f">
            <label for="channel">Cara pembayaran diterima</label>
            <select id="channel" name="channel" required>
                @foreach (\App\Enums\PaymentChannel::cases() as $channel)
                <option value="{{ $channel->value }}">{{ $channel->label() }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">Tandai lunas (Rp {{ number_format($remaining, 0, ',', '.') }})</button>
    </form>
    <p class="hint">Mencatat pembayaran terverifikasi sebesar sisa tagihan. Dipakai untuk bayar di akhir atau pembayaran
        tunai.</p>
    @endif
</div>
@endif

{{-- ===== Revisi ===== --}}
@unless ($closed)
<div class="box-action">
    <h2>Revisi desain</h2>
    <form method="POST" action="{{ route('admin.orders.revision', $order) }}" class="row-form">
        @csrf
        <div class="f s"><label for="revision_count">Jumlah revisi</label><input id="revision_count" type="number"
                name="revision_count" min="0" max="100" step="1"
                value="{{ old('revision_count', $order->revision_count) }}" required></div>
        <div class="f"><label for="revision_fee">Biaya revisi (Rp)</label><input id="revision_fee" type="number"
                name="revision_fee" min="0" step="1" value="{{ old('revision_fee', $order->revision_fee) }}" required>
        </div>
        <button class="btn" type="submit">Simpan revisi</button>
    </form>
    <p class="hint">Biaya diisi manual dan ditambahkan ke total tagihan.@if (filled($freeRevisionLimit)) Batas revisi
        gratis saat ini: {{ $freeRevisionLimit }} kali.@endif</p>
</div>
@endunless

{{-- ===== Batalkan ===== --}}
@if ($canCancel)
<div class="box-action danger-zone">
    <h2>Batalkan pesanan</h2>
    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="row-form"
        onsubmit="return confirm('Batalkan pesanan ini? Tindakan ini tidak dapat diurungkan.')">
        @csrf
        <div class="f">
            <label for="cancel_reason">Alasan pembatalan (wajib)</label>
            <input id="cancel_reason" type="text" name="cancel_reason" maxlength="500" required
                value="{{ old('cancel_reason') }}">
        </div>
        <button class="btn danger" type="submit">Batalkan pesanan</button>
    </form>
    <p class="hint">Kebijakan pengembalian DP mengikuti kesepakatan dengan mitra; sistem belum memprosesnya.</p>
</div>
@endif

<script>
(function() {
    var sel = document.getElementById('status');
    var box = document.getElementById('resi-box');
    if (!sel || !box) {
        return;
    }
    var input = box.querySelector('input');

    function sync() {
        var on = sel.value === 'dikirim';
        box.hidden = !on;
        input.required = on;
        input.disabled = !on;
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>
@endsection