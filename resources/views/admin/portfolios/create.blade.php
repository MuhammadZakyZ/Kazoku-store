@extends('layouts.admin')

@section('title', 'Tambah Portofolio — Panel Admin')

@section('admin')
    <h1>Tambah portofolio</h1>

    @if ($categories->isEmpty())
        <div class="alert err">Belum ada kategori. <a href="{{ route('admin.categories.index') }}">Tambahkan kategori</a> dulu.</div>
    @else
        <form method="POST" action="{{ route('admin.portfolios.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.portfolios._form')
            <div class="actions section">
                <button class="btn" type="submit">Simpan</button>
                <a class="btn ghost" href="{{ route('admin.portfolios.index') }}">Batal</a>
            </div>
        </form>
    @endif
@endsection
