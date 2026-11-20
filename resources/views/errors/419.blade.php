@extends('layouts.app')

@section('title', 'Sesi kedaluwarsa — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Sesi kedaluwarsa</h1>
        <p>Halaman sudah terlalu lama terbuka. Muat ulang halaman, lalu coba lagi.</p>
        <div class="actions section">
            <a class="btn" href="{{ route('home') }}">Ke beranda</a>
            <a class="btn ghost" href="javascript:history.back()">Kembali</a>
        </div>
    </div>
@endsection
