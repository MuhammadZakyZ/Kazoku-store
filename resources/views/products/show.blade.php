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
                <div class="product-area-sides">
                    @foreach (['depan' => 'Depan', 'belakang' => 'Belakang'] as $side => $sideLabel)
                        @php
                            $sideAreas = $product->printAreas->filter(fn ($area) => $area->side->value === $side)->values();
                        @endphp
                        @if ($sideAreas->isNotEmpty())
                            <section class="product-area-side">
                                <h3>Area {{ $sideLabel }}</h3>
                                <div class="product-area-list">
                                    @foreach ($sideAreas as $area)
                                        <div>
                                            <span>{{ $area->name }}</span>
                                            <small>{{ $area->size_label }}</small>
                                            <strong>{{ $area->price > 0 ? '+Rp '.number_format($area->price, 0, ',', '.') : 'Gratis' }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @endforeach
                </div>
                <p class="hint">Biaya area yang Anda pilih ditambahkan ke harga satuan.</p>
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
