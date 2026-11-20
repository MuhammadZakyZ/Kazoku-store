@extends('layouts.admin')

@section('title', 'Ubah Portofolio — Panel Admin')

@section('admin')
    <h1>Ubah portofolio</h1>

    <form method="POST" action="{{ route('admin.portfolios.update', $portfolio) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.portfolios._form')
        <div class="hint">Biarkan kolom gambar kosong untuk mempertahankan gambar saat ini.</div>
        <div class="actions section">
            <button class="btn" type="submit">Simpan</button>
            <a class="btn ghost" href="{{ route('admin.portfolios.index') }}">Batal</a>
        </div>
    </form>
@endsection
