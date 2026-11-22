@extends('layouts.app')

@section('title', 'Pesanan '.$order->code.' — Kazoku Store')

@section('content')
@if (session('status') && str_starts_with(session('status'), 'Pesanan berhasil dibuat.'))
<section class="order-success" role="status">
    <span class="order-success-icon" aria-hidden="true">✓</span>
    <div>
        <h2>Pesanan berhasil dikirim!</h2>
        <p>{{ session('status') }}</p>
    </div>
    <a class="btn ghost" href="{{ route('orders.index') }}">Pesanan saya</a>
</section>
@endif

<div class="head">
    <h1>Pesanan {{ $order->code }}</h1>
    <span
        class="badge {{ $order->status->value === 'selesai' ? 'ok' : ($order->status->value === 'dibatalkan' ? 'bad' : '') }}">{{ $order->status->label() }}</span>
</div>
<p class="hint">
    Dibuat {{ $order->created_at->format('d M Y, H:i') }} WIB ·
    <a href="{{ route('orders.invoice', $order) }}" target="_blank" rel="noopener">Lihat nota</a>
</p>

@if ($order->status->value === 'dibatalkan')
<div class="alert err"><strong>Pesanan dibatalkan.</strong> {{ $order->cancel_reason }}</div>
@endif

@if ($order->tracking_no)
<div class="alert ok">Nomor resi: <strong>{{ $order->tracking_no }}</strong>@if ($order->courier)
    ({{ $order->courier->name }})@endif</div>
@endif

@if ($canConfirm)
<form method="POST" action="{{ route('orders.received', $order) }}"
    onsubmit="return confirm('Konfirmasi bahwa pesanan sudah diterima / diambil?')" class="line">
    @csrf
    <button class="btn" type="submit">Konfirmasi pesanan sudah diterima / diambil</button>
    @if ($order->remainingBalance() > 0)
    <div class="hint">Sisa tagihan harus lunas sebelum Anda dapat mengonfirmasi.</div>
    @endif
</form>
@endif

<div class="panel">
    <h2>Pesanan</h2>
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

    <h2 class="section">Desain</h2>
    <p>{{ $order->design_type->label() }}</p>

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
                    {{ $design->area_price > 0 ? 'Rp '.number_format($design->area_price, 0, ',', '.') : 'Gratis' }}
                </td>
                <td class="r"><a href="{{ route('files.design', [$order, $design]) }}">Unduh file</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @elseif ($order->design_type->value === 'konsultasi' && $whatsapp)
    <p><a class="btn ghost" href="{{ $whatsapp }}" target="_blank" rel="noopener">Konsultasi desain via WhatsApp</a></p>
    <p class="hint">Sebutkan kode pesanan {{ $order->code }} saat menghubungi admin.</p>
    @endif

    @if ($finalDesigns->isNotEmpty())
    <h2 class="section">Desain final dari admin</h2>
    <table class="mini">
        <tbody>
            @foreach ($finalDesigns as $design)
            <tr>
                <td>{{ $design->area_name ?: 'Desain final' }}</td>
                <td class="r"><a href="{{ route('files.design', [$order, $design]) }}">Unduh file</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h2 class="section">Pengambilan</h2>
    <p>{{ $order->delivery_method->label() }}</p>
    @if ($order->shipping_destination)
    <p>{{ $order->courier?->name }} · {{ $order->shipping_destination }}<br>{!! nl2br(e($order->shipping_address)) !!}
    </p>
    @endif

    <h2 class="section">Tagihan</h2>
    <table class="mini">
        <tbody>
            <tr>
                <td>Ongkir</td>
                <td class="r">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
            </tr>
            @if ($order->revision_fee > 0)
            <tr>
                <td>Biaya revisi ({{ $order->revision_count }} kali)</td>
                <td class="r">Rp {{ number_format($order->revision_fee, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Total tagihan</strong></td>
                <td class="r"><strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Metode pembayaran</td>
                <td class="r">{{ $order->payment_method->label() }}</td>
            </tr>
            <tr>
                <td>Status pembayaran</td>
                <td class="r">{{ $order->payment_status->label() }}</td>
            </tr>
            <tr>
                <td>Pembayaran awal yang diminta</td>
                <td class="r">Rp {{ number_format($upfront, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Sisa tagihan</td>
                <td class="r">Rp {{ number_format($order->remainingBalance(), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="panel section">
    <h2>Pembayaran</h2>

    @if (filled($paymentInstructions))
    <div class="alert ok"><strong>Transfer ke:</strong><br>{!! nl2br(e($paymentInstructions)) !!}</div>
    @endif

    @if ($order->payments->isNotEmpty())
    <table class="mini">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th class="r">Jumlah</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->payments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('d M Y, H:i') }}</td>
                <td>{{ $payment->type->label() }}</td>
                <td class="r">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td>
                    <span
                        class="badge {{ $payment->status->value === 'terverifikasi' ? 'ok' : ($payment->status->value === 'ditolak' ? 'bad' : '') }}">{{ $payment->status->label() }}</span>
                    @if ($payment->reject_reason)<div class="hint">Alasan: {{ $payment->reject_reason }}</div>@endif
                </td>
                <td class="r">@if ($payment->proof_file)<a
                        href="{{ route('files.proof', [$order, $payment]) }}">Bukti</a>@endif</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if ($nextPayment)
    <form method="POST" action="{{ route('payments.store', $order) }}" enctype="multipart/form-data" class="section">
        @csrf
        <p>Unggah bukti <strong>{{ $nextPayment['type']->label() }}</strong> sebesar <strong>Rp
                {{ number_format($nextPayment['amount'], 0, ',', '.') }}</strong>.</p>
        <label for="proof_file">Bukti transfer</label>
        <input id="proof_file" type="file" name="proof_file" accept="image/png,image/jpeg,image/webp,application/pdf"
            required>
        <div class="hint">JPG, PNG, WEBP, atau PDF, maksimal 5 MB.</div>
        @error('proof_file') <div class="err-text">{{ $message }}</div> @enderror
        <div class="actions section"><button class="btn" type="submit">Kirim bukti</button></div>
    </form>
    @elseif ($order->payment_method->value === 'di_akhir' && $order->status->value !== 'dibatalkan')
    <p class="hint">Metode bayar di akhir: tidak perlu mengunggah bukti. Admin menandai lunas setelah pembayaran
        diterima.</p>
    @elseif ($order->payments->contains(fn ($p) => $p->status->value === 'menunggu'))
    <p class="hint">Bukti Anda sedang diperiksa admin.</p>
    @elseif ($order->remainingBalance() === 0 && $order->payments->isNotEmpty())
    <p class="hint">Pembayaran sudah lunas. Terima kasih.</p>
    @endif
</div>

<p class="sub"><a href="{{ route('orders.index') }}">‹ Pesanan Saya</a></p>
@endsection