@extends('layouts.app')

@section('title', 'Masuk — Kazoku Store')

@section('content')
    <div class="card">
        <h1>Masuk</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            @error('email') <div class="err-text">{{ $message }}</div> @enderror

            <label for="password">Kata sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password') <div class="err-text">{{ $message }}</div> @enderror

            <label class="check">
                <input type="checkbox" name="remember" value="1"> Ingat saya
            </label>

            <button type="submit" class="btn block">Masuk</button>
        </form>

        <p class="sub">
            <a href="{{ route('password.request') }}">Lupa kata sandi?</a><br>
            Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
        </p>
    </div>
@endsection
