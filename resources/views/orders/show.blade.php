@extends('layouts.app')

@section('title', 'Pesanan '.$order->code.' — Kazoku Store')

@section('content')
@php
$justCreated = session('status') && str_starts_with(session('status'), 'Pesanan berhasil dibuat.');
$customerDesigns = $order->designs->filter(fn ($d) => $d->type->value === 'pelanggan');
$finalDesigns = $order->designs->filter(fn ($d) => $d->type->value === 'final');
$totalQty = (int) $order->items->sum('quantity');
$areaPerPiece = (int) $customerDesigns->sum('area_price');
$statusTone = match ($order->status->value) {
'selesai' => 'ok',
'dibatalkan' => 'bad',
'baru', 'menunggu_verifikasi', 'konsultasi' => 'warn',
default => '',
};
$rupiah = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
$dateText = $order->created_at->locale('id')->translatedFormat('d M Y');
$pending = $order->payments->contains(fn ($p) => $p->status->value === 'menunggu');
@endphp

@if ($justCreated)
<div class="od-toast" id="od-toast" role="status">
    <span class="od-toast-icon" aria-hidden="true">✓</span>
    <span>Pesanan berhasil dikirim</span>
    <button type="button" aria-label="Tutup pemberitahuan" onclick="this.closest('.od-toast').remove()">×</button>
</div>
<script>
setTimeout(function() {
    var t = document.getElementById('od-toast');
    if (t) t.remove();
}, 6000);
</script>
@endif

<div class="od-page">
    <header class="od-head">
        @if ($justCreated)
        <span class="od-check" aria-hidden="true">✓</span>
        <h1>Pesanan terkirim</h1>
        <p>Pesananmu sudah terkirim ke admin. Simpan kode pesananmu, dan lanjutkan dengan pembayaran.</p>
        @else
        <a class="crumb-link" href="{{ route('orders.index') }}">&larr; Pesanan Saya</a>
        <h1>Detail pesanan</h1>
        <p>Pantau status, pembayaran, dan rincian pesananmu di sini.</p>
        @endif
    </header>

    @if ($order->status->value === 'dibatalkan')
    <div class="alert err"><strong>Pesanan dibatalkan.</strong> {{ $order->cancel_reason }}</div>
    @endif

    @if ($canConfirm)
    <form method="POST" action="{{ route('orders.received', $order) }}"
        onsubmit="return confirm('Konfirmasi bahwa pesanan sudah diterima / diambil?')" class="od-card od-confirm">
        @csrf
        <div>
            <strong>Pesananmu sudah siap.</strong>
            @if ($order->remainingBalance() > 0)
            <div class="hint">Sisa tagihan harus lunas sebelum kamu dapat mengonfirmasi.</div>
            @endif
        </div>
        <button class="btn" type="submit">Konfirmasi pesanan sudah diterima / diambil</button>
    </form>
    @endif

    <div class="od-layout">
        {{-- ============ Kiri: rincian pesanan ============ --}}
        <section class="od-card" aria-label="Rincian pesanan">
            <div class="od-top">
                <div>
                    <span class="od-label">Kode pesanan</span>
                    <div class="od-code">{{ $order->code }}</div>
                </div>
                <span class="order-badge {{ $statusTone }}">{{ $order->status->label() }}</span>
            </div>

            <dl class="od-rows">
                <div>
                    <dt>Tanggal pesan</dt>
                    <dd>{{ $dateText }}</dd>
                </div>
                <div>
                    <dt>Produk</dt>
                    <dd>
                        @foreach ($order->items as $row)
                        <span
                            class="od-line-item">{{ collect([$row->product->name, $row->color_name, $row->option_name])->filter()->implode(' · ') }}
                            · {{ $row->quantity }} pcs</span>
                        @endforeach
                    </dd>
                </div>
                <div>
                    <dt>Pengiriman</dt>
                    <dd>{{ collect([$order->delivery_method->label(), $order->courier?->name, $order->shipping_destination])->filter()->implode(' · ') }}
                    </dd>
                </div>
                @if (filled($order->shipping_address))
                <div>
                    <dt>Alamat</dt>
                    <dd>{!! nl2br(e($order->shipping_address)) !!}</dd>
                </div>
                @endif
                @if ($order->tracking_no)
                <div>
                    <dt>Nomor resi</dt>
                    <dd>{{ $order->tracking_no }}@if ($order->courier) ({{ $order->courier->name }})@endif</dd>
                </div>
                @endif
                <div>
                    <dt>Metode pembayaran</dt>
                    <dd>{{ $order->payment_method->label() }}</dd>
                </div>
                <div>
                    <dt>Status pembayaran</dt>
                    <dd>{{ $order->payment_status->label() }}</dd>
                </div>
                <div>
                    <dt>Desain</dt>
                    <dd>{{ $order->design_type->label() }}</dd>
                </div>
            </dl>

            @if ($customerDesigns->isNotEmpty())
            <h2 class="od-label od-section">Rincian area sablon ({{ $customerDesigns->count() }})</h2>
            <div class="od-areas">
                @foreach ($customerDesigns as $design)
                <div class="od-area">
                    <div>
                        <strong>{{ $design->area_name }}</strong>
                        <span>{{ str_replace('x', '×', (string) $design->print_size) }}</span>
                    </div>
                    <div class="od-area-side">
                        @if ($design->printArea?->side)
                        <span class="od-pill">Posisi {{ $design->printArea->side->label() }}</span>
                        @endif
                        <a href="{{ route('files.design', [$order, $design]) }}">Unduh</a>
                    </div>
                </div>
                @endforeach
            </div>
            <p class="od-note">{{ $customerDesigns->count() }} area dipilih.</p>
            @endif

            <div class="od-lines">
                @foreach ($order->items as $row)
                <div>
                    <span>{{ $row->product->name }}@if ($row->option_name) ({{ $row->option_name }})@endif ×
                        {{ $row->quantity }}</span>
                    <span>{{ $rupiah(max(0, $row->unit_price - $areaPerPiece) * $row->quantity) }}</span>
                </div>
                @endforeach
                @foreach ($customerDesigns as $design)
                <div>
                    <span>{{ $design->area_name }} × {{ $totalQty }}</span>
                    <span>{{ $design->area_price > 0 ? $rupiah($design->area_price * $totalQty) : 'Gratis' }}</span>
                </div>
                @endforeach
                <div>
                    <span>Ongkir</span>
                    <span>{{ $order->shipping_cost > 0 ? $rupiah($order->shipping_cost) : 'Gratis' }}</span>
                </div>
                @if ($order->revision_fee > 0)
                <div>
                    <span>Biaya revisi ({{ $order->revision_count }} kali)</span>
                    <span>{{ $rupiah($order->revision_fee) }}</span>
                </div>
                @endif
            </div>

            <div class="od-total">
                <span>Total</span>
                <strong>{{ $rupiah($order->grand_total) }}</strong>
            </div>
            @if ($upfront > 0)
            <div class="od-sub">
                <span>{{ $order->payment_method->value === 'dp' ? 'DP yang dibayar sekarang' : 'Dibayar sekarang' }}</span>
                <span>{{ $rupiah($upfront) }}</span>
            </div>
            @endif
            <div class="od-sub">
                <span>Sisa tagihan</span>
                <span>{{ $rupiah($order->remainingBalance()) }}</span>
            </div>

            <p class="od-foot"><a href="{{ route('orders.invoice', $order) }}" target="_blank" rel="noopener">Lihat
                    nota</a></p>
        </section>

        {{-- ============ Kanan: langkah berikutnya ============ --}}
        <aside class="od-card od-next" aria-label="Langkah berikutnya">
            <h2>Langkah berikutnya</h2>

            <ol class="od-steps">
                <li>
                    <span class="od-num">1</span>
                    <div>
                        <strong>Unggah bukti pembayaran</strong>
                        @if ($nextPayment)
                        <p>Transfer sesuai nominal {{ $nextPayment['type']->label() }}
                            ({{ $rupiah($nextPayment['amount']) }}), lalu unggah bukti transfer.</p>
                        @if (filled($paymentInstructions))
                        <div class="od-transfer">
                            <span>Transfer ke</span>
                            <p>{!! nl2br(e($paymentInstructions)) !!}</p>
                        </div>
                        @endif
                        @elseif ($order->payment_method->value === 'di_akhir' && $order->status->value !== 'dibatalkan')
                        <p>Bayar di akhir: tidak perlu mengunggah bukti. Admin menandai lunas setelah pembayaran
                            diterima.</p>
                        @elseif ($pending)
                        <p>Bukti kamu sedang diperiksa admin.</p>
                        @elseif ($order->remainingBalance() === 0 && $order->payments->isNotEmpty())
                        <p>Pembayaran sudah lunas. Terima kasih.</p>
                        @else
                        <p>Belum ada pembayaran yang perlu diunggah.</p>
                        @endif
                    </div>
                </li>
                <li>
                    <span class="od-num">2</span>
                    <div>
                        <strong>Konsultasi desain (opsional)</strong>
                        <p>Butuh bantuan desain? Kamu bisa bertanya ke admin lewat WhatsApp. Pesanan tetap diproses
                            lewat website.</p>
                        @if ($whatsapp)
                        <a class="od-link" href="{{ $whatsapp }}" target="_blank" rel="noopener">Chat admin lewat
                            WhatsApp ↗</a>
                        @endif
                    </div>
                </li>
                <li>
                    <span class="od-num">3</span>
                    <div>
                        <strong>Pantau status</strong>
                        <p>Lihat perkembangan di halaman Pesanan Saya.</p>
                    </div>
                </li>
            </ol>

            @if ($nextPayment)
            <form method="POST" action="{{ route('payments.store', $order) }}" enctype="multipart/form-data"
                class="od-upload">
                @csrf
                <label for="proof_file">Bukti transfer</label>
                <input id="proof_file" type="file" name="proof_file"
                    accept="image/png,image/jpeg,image/webp,application/pdf" required>
                <div class="hint">JPG, PNG, WEBP, atau PDF, maksimal 5 MB.</div>
                @error('proof_file') <div class="err-text">{{ $message }}</div> @enderror
                <button class="btn od-btn" type="submit">Unggah bukti pembayaran</button>
            </form>
            @endif

            <a class="btn ghost od-btn" href="{{ route('orders.index') }}">Lihat Pesanan Saya</a>
        </aside>
    </div>

    {{-- ============ Riwayat pembayaran ============ --}}
    @if ($order->payments->isNotEmpty())
    <section class="od-card od-wide">
        <h2>Riwayat pembayaran</h2>
        <div class="od-table">
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
                        <td class="r">{{ $rupiah($payment->amount) }}</td>
                        <td>
                            <span
                                class="order-badge {{ $payment->status->value === 'terverifikasi' ? 'ok' : ($payment->status->value === 'ditolak' ? 'bad' : 'warn') }}">{{ $payment->status->label() }}</span>
                            @if ($payment->reject_reason)<div class="hint">Alasan: {{ $payment->reject_reason }}</div>
                            @endif
                        </td>
                        <td class="r">@if ($payment->proof_file)<a
                                href="{{ route('files.proof', [$order, $payment]) }}">Bukti</a>@endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

    {{-- ============ Desain final dari admin ============ --}}
    @if ($finalDesigns->isNotEmpty())
    <section class="od-card od-wide">
        <h2>Desain final dari admin</h2>
        <div class="od-areas">
            @foreach ($finalDesigns as $design)
            <div class="od-area">
                <div><strong>{{ $design->area_name ?: 'Desain final' }}</strong></div>
                <div class="od-area-side"><a href="{{ route('files.design', [$order, $design]) }}">Unduh file</a></div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection