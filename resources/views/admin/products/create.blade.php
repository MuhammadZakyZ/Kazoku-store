@extends('layouts.admin')

@section('title', 'Tambah Produk — Panel Admin')

@section('admin')
    <h1>Tambah produk</h1>

    @if ($categories->isEmpty())
        <div class="alert err">Belum ada kategori. <a href="{{ route('admin.categories.index') }}">Tambahkan kategori</a> dulu.</div>
    @else
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.products._form')
            <div class="actions section">
                <button class="btn" type="submit">Simpan dan lanjut</button>
                <a class="btn ghost" href="{{ route('admin.products.index') }}">Batal</a>
            </div>
        </form>
    @endif
@endsection
