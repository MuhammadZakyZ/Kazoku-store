@extends('layouts.app')

@section('title', 'Halaman tidak ditemukan — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Halaman tidak ditemukan</h1>
        <p>Halaman yang Anda cari tidak ada atau bukan milik akun Anda.</p>
        <div class="actions section">
            <a class="btn" href="{{ route('home') }}">Ke beranda</a>
            <a class="btn ghost" href="javascript:history.back()">Kembali</a>
        </div>
    </div>
@endsection
