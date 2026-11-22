@extends('layouts.app')

@section('title', 'Profil & Kontak — Kazoku Store')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Kenalan lebih dekat</span>
        <h1>Profil &amp; kontak</h1>
        <p>Ada pertanyaan atau ide yang ingin dibuat? Kami siap membantu.</p>
    </div>
</div>

<div class="contact">
    <section>
        <span class="eyebrow">Tentang kami</span>
        <h2>Kazoku Store</h2>
        <p>Jasa custom printing. Pilih produk, unggah desain, dan pantau pesanan Anda secara online.</p>

        <h2>Alamat</h2>
        @if (filled($address))
        <p>{!! nl2br(e($address)) !!}</p>
        @else
        <p class="empty">Alamat belum diisi.</p>
        @endif
        @if (filled($mapsUrl))
        <p><a class="btn ghost sm" href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer">Lihat di peta <span
                    aria-hidden="true">↗</span></a></p>
        @endif

        <h2>Jam buka</h2>
        @if (filled($openingHours))
        <p>{!! nl2br(e($openingHours)) !!}</p>
        @else
        <p class="empty">Jam buka belum diisi.</p>
        @endif
    </section>

    <section>
        <span class="eyebrow">Kami siap membantu</span>
        <h2>Hubungi kami</h2>
        @if ($whatsapp)
        <p>WhatsApp: {{ $whatsappNumber }}</p>
        <a class="btn" href="{{ $whatsapp }}" target="_blank" rel="noopener">Chat via WhatsApp</a>
        @else
        <p class="empty">Nomor WhatsApp belum diisi.</p>
        @endif

        @if (filled($dpPercent) || filled($freeRevisionLimit))
        <h2 class="section">Ketentuan</h2>
        <ul>
            @if (filled($dpPercent))
            <li>Pembayaran DP sebesar {{ $dpPercent }}% dari total.</li>
            @endif
            @if (filled($freeRevisionLimit))
            <li>Revisi desain gratis hingga {{ $freeRevisionLimit }} kali; revisi berikutnya dikenakan biaya.</li>
            @endif
        </ul>
        @endif
    </section>
</div>
@endsection