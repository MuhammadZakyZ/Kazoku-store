@extends('layouts.app')

@section('title', 'Atur Ulang Kata Sandi — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Atur ulang kata sandi</h1>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
            @error('email') <div class="err-text">{{ $message }}</div> @enderror

            <label for="password">Kata sandi baru</label>
            <input id="password" type="password" name="password" required autofocus autocomplete="new-password">
            <div class="hint">Minimal 8 karakter, mengandung huruf dan angka.</div>
            @error('password') <div class="err-text">{{ $message }}</div> @enderror

            <label for="password_confirmation">Ulangi kata sandi baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

            <button type="submit" class="btn block">Simpan kata sandi</button>
        </form>
    </div>
@endsection
