@extends('layouts.app')

@section('title', 'Terlalu banyak permintaan — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Terlalu banyak permintaan</h1>
        <p>Mohon tunggu sebentar sebelum mencoba lagi.</p>
        <div class="actions section">
            <a class="btn" href="{{ route('home') }}">Ke beranda</a>
            <a class="btn ghost" href="javascript:history.back()">Kembali</a>
        </div>
    </div>
@endsection
