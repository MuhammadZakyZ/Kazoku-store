@extends('layouts.app')

@section('title', 'Akses ditolak — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Akses ditolak</h1>
        <p>Anda tidak memiliki izin untuk membuka halaman ini.</p>
        <div class="actions section">
            <a class="btn" href="{{ route('home') }}">Ke beranda</a>
            <a class="btn ghost" href="javascript:history.back()">Kembali</a>
        </div>
    </div>
@endsection
