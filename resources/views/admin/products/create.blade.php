@extends('layouts.admin')

@section('title', 'Tambah Produk — Panel Admin')

@section('admin')
    <div class="head product-head">
        <div>
            <a class="crumb-link" href="{{ route('admin.products.index') }}">&larr; Daftar produk</a>
            <h1>Tambah produk</h1>
            <p class="hint">Setelah disimpan, lanjut atur ukuran, warna, dan area sablon.</p>
        </div>
    </div>

    @if ($categories->isEmpty())
        <div class="alert err">Belum ada kategori. <a href="{{ route('admin.categories.index') }}">Tambahkan kategori</a> dulu.</div>
    @else
        <section class="manage-section">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.products._form')
                <div class="actions section">
                    <button class="btn" type="submit">Simpan dan lanjut</button>
                    <a class="btn ghost" href="{{ route('admin.products.index') }}">Batal</a>
                </div>
            </form>
        </section>
    @endif
@endsection
