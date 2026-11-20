@extends('layouts.app')

@section('title', 'Lupa Kata Sandi — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Lupa kata sandi</h1>
        <p class="sub">Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi.</p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            @error('email') <div class="err-text">{{ $message }}</div> @enderror

            <button type="submit" class="btn block">Kirim tautan reset</button>
        </form>

        <p class="sub"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
    </div>
@endsection
