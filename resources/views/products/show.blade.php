@extends('layouts.app')

@section('title', $product->name.' — Kazoku Store')

@section('content')
    <p class="crumb"><a href="{{ route('products.index') }}">‹ Semua produk</a></p>

    <div class="detail">
        <div class="pic">
            @if ($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
            @else
                <div class="ph big">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}</div>
            @endif
        </div>

        <div class="info">
            <span class="tag">{{ $product->category->name }}</span>
            <h1>{{ $product->name }}</h1>
            <div class="price big">mulai Rp {{ number_format($product->base_price, 0, ',', '.') }}</div>

            @if ($product->description)
                <p class="desc">{!! nl2br(e($product->description)) !!}</p>
            @endif

            @if ($product->options->isNotEmpty())
                <h2>Ukuran</h2>
                <div class="chips">
                    @foreach ($product->options as $option)
                        <span class="chip">{{ $option->name }}@if ($option->extra_price > 0) <small>+Rp {{ number_format($option->extra_price, 0, ',', '.') }}</small>@endif</span>
                    @endforeach
                </div>
            @endif

            @if ($product->colors->isNotEmpty())
                <h2>Warna</h2>
                <div class="chips">
                    @foreach ($product->colors as $color)
                        <span class="chip">{{ $color->name }}</span>
                    @endforeach
                </div>
            @endif

            @if ($product->printAreas->isNotEmpty())
                <h2>Area sablon</h2>
                <table class="mini">
                    <thead><tr><th>Posisi</th><th>Ukuran</th><th>Biaya</th></tr></thead>
                    <tbody>
                    @foreach ($product->printAreas as $area)
                        <tr>
                            <td>{{ $area->name }}</td>
                            <td>{{ $area->size_label }}</td>
                            <td>{{ $area->price > 0 ? 'Rp '.number_format($area->price, 0, ',', '.') : 'Gratis' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p class="hint">Biaya semua area yang Anda pilih ditambahkan ke harga satuan.</p>
            @endif

            @if ($hasPreview)
                <p class="hint">✓ Produk ini mendukung preview desain pada mockup saat Anda memesan.</p>
            @endif

            <div class="actions section">
                <a class="btn" href="{{ route('orders.create', $product) }}">Pesan</a>
                @if ($whatsapp)
                    <a class="btn ghost" href="{{ $whatsapp }}" target="_blank" rel="noopener">Konsultasi via WhatsApp</a>
                @endif
            </div>
            @guest
                <p class="hint">Anda akan diminta masuk atau mendaftar dulu, lalu kembali ke pesanan ini.</p>
            @endguest
        </div>
    </div>
@endsection
