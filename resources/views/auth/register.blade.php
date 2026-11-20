@extends('layouts.app')

@section('title', 'Daftar — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Daftar</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label for="name">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name') <div class="err-text">{{ $message }}</div> @enderror

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email') <div class="err-text">{{ $message }}</div> @enderror

            <label for="phone">Nomor WhatsApp</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="08xxxxxxxxxx">
            <div class="hint">Dipakai admin untuk konsultasi desain. Akan disimpan dalam format 628xxxxxxxxxx.</div>
            @error('phone') <div class="err-text">{{ $message }}</div> @enderror

            <label for="password">Kata sandi</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            <div class="hint">Minimal 8 karakter, mengandung huruf dan angka.</div>
            @error('password') <div class="err-text">{{ $message }}</div> @enderror

            <label for="password_confirmation">Ulangi kata sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

            <button type="submit" class="btn block">Daftar</button>
        </form>

        <p class="sub">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
@endsection
