@extends('layouts.admin')

@section('title', 'Panel Admin — Kazoku Store')

@section('admin')
    <h1>Panel Admin</h1>
    <p class="sub">Halo, {{ auth()->user()->name }}. Pesanan dan pembayaran menyusul di tahap berikutnya.</p>

    <div class="stats">
        @foreach ($counts as $label => $count)
            <div class="stat"><strong>{{ $count }}</strong><span>{{ $label }}</span></div>
        @endforeach
    </div>
@endsection
