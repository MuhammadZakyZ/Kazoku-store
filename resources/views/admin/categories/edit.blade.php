@extends('layouts.admin')

@section('title', 'Ubah Kategori — Panel Admin')

@section('admin')
    <h1>Ubah kategori</h1>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="row-form">
        @csrf @method('PUT')
        <div class="f">
            <label for="name">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="100">
        </div>
        <button class="btn" type="submit">Simpan</button>
        <a class="btn ghost" href="{{ route('admin.categories.index') }}">Batal</a>
    </form>
@endsection
