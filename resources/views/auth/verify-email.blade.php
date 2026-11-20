@extends('layouts.app')

@section('title', 'Verifikasi Email — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Cek email Anda</h1>
        <p>
            Kami sudah mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>.
            Klik tautan itu untuk mengaktifkan fitur pemesanan.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn block">Kirim ulang tautan verifikasi</button>
        </form>

        <p class="sub">
            Salah email? Keluar dan daftar ulang dengan email yang benar.
        </p>
    </div>
@endsection
