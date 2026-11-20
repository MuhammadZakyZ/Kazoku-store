<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota {{ $order->code }} — Kazoku Store</title>
    <style>
        body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#111;margin:0;background:#f3f4f6}
        .sheet{max-width:780px;margin:1rem auto;background:#fff;padding:2rem;border:1px solid #ddd}
        h1{font-size:1.4rem;margin:0}
        .top{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;border-bottom:2px solid #111;padding-bottom:.8rem;margin-bottom:1rem}
        .muted{color:#555;font-size:.85rem}
        table{width:100%;border-collapse:collapse;margin:.6rem 0 1rem;font-size:.9rem}
        th,td{padding:.4rem .5rem;border-bottom:1px solid #ddd;text-align:left;vertical-align:top}
        th{font-size:.75rem;text-transform:uppercase;color:#555}
        .r{text-align:right}
        .tot td{font-weight:700;border-top:2px solid #111}
        h2{font-size:1rem;margin:1.2rem 0 .2rem}
        .actions{max-width:780px;margin:1rem auto;display:flex;gap:.5rem}
        .actions button,.actions a{font:inherit;padding:.5rem 1rem;border:1px solid #111;background:#111;color:#fff;border-radius:6px;text-decoration:none;cursor:pointer}
        .actions a{background:#fff;color:#111}
        @media print{body{background:#fff}.sheet{border:0;margin:0;padding:0}.actions{display:none}}
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Cetak / simpan PDF</button>
        <a href="{{ route('orders.show', $order) }}">Kembali</a>
    </div>

    <div class="sheet">
        <div class="top">
            <div>
                <h1>Kazoku Store</h1>
                @if (filled($address))<div class="muted">{!! nl2br(e($address)) !!}</div>@endif
                @if (filled($whatsappNumber))<div class="muted">WhatsApp: {{ $whatsappNumber }}</div>@endif
                @if (filled($openingHours))<div class="muted">{!! nl2br(e($openingHours)) !!}</div>@endif
            </div>
            <div style="text-align:right">
                <strong>NOTA PESANAN</strong><br>
                <span style="font-size:1.1rem">{{ $order->code }}</span><br>
                <span class="muted">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <div>
            <strong>Pemesan</strong><br>
            {{ $order->user->name }}<br>
            <span class="muted">{{ $order->user->email }} · {{ $order->user->phone }}</span>
        </div>

        <h2>Rincian</h2>
        <table>
            <thead><tr><th>Produk</th><th>Ukuran</th><th>Warna</th><th class="r">Jml</th><th class="r">Harga satuan</th><th class="r">Subtotal</th></tr></thead>
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

        @php($areas = $order->designs->filter(fn ($d) => $d->type->value === 'pelanggan'))
        @if ($areas->isNotEmpty())
            <div class="muted">Area sablon (biayanya sudah termasuk dalam harga satuan):
                {{ $areas->map(fn ($d) => $d->area_name.' '.$d->print_size.' (Rp '.number_format($d->area_price, 0, ',', '.').')')->implode('; ') }}
            </div>
        @endif

        <h2>Pengambilan</h2>
        <div>{{ $order->delivery_method->label() }}
            @if ($order->shipping_destination) — {{ $order->courier?->name }}, {{ $order->shipping_destination }}<br>{!! nl2br(e($order->shipping_address)) !!}@endif
        </div>

        <table style="margin-top:1rem">
            <tbody>
            <tr><td>Ongkir</td><td class="r">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td></tr>
            @if ($order->revision_fee > 0)
                <tr><td>Biaya revisi</td><td class="r">Rp {{ number_format($order->revision_fee, 0, ',', '.') }}</td></tr>
            @endif
            <tr class="tot"><td>Total tagihan</td><td class="r">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td></tr>
            <tr><td>Metode pembayaran</td><td class="r">{{ $order->payment_method->label() }}</td></tr>
            @if ($order->dp_amount > 0)
                <tr><td>DP</td><td class="r">Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</td></tr>
            @endif
            <tr><td>Sudah dibayar (terverifikasi)</td><td class="r">Rp {{ number_format($order->grand_total - $order->remainingBalance(), 0, ',', '.') }}</td></tr>
            <tr><td>Sisa tagihan</td><td class="r">Rp {{ number_format($order->remainingBalance(), 0, ',', '.') }}</td></tr>
            <tr><td>Status pembayaran</td><td class="r">{{ $order->payment_status->label() }}</td></tr>
            </tbody>
        </table>

        <div class="muted">Simpan nota ini dan sebutkan kode pesanan saat berkonsultasi atau mengambil pesanan.</div>
    </div>
</body>
</html>
