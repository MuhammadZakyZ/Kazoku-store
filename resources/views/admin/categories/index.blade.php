@extends('layouts.admin')

@section('title', 'Kategori — Panel Admin')

@section('admin')
    <h1>Kategori</h1>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="row-form">
        @csrf
        <div class="f">
            <label for="name">Nama kategori baru</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100">
        </div>
        <button class="btn" type="submit">Tambah</button>
    </form>

    <table class="section">
        <thead><tr><th>Nama</th><th>Produk</th><th>Portofolio</th><th></th></tr></thead>
        <tbody>
        @forelse ($categories as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td>{{ $category->products_count }}</td>
                <td>{{ $category->portfolios_count }}</td>
                <td class="actions">
                    <a class="btn ghost sm" href="{{ route('admin.categories.edit', $category) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf @method('DELETE')
                        <button class="btn danger sm" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada kategori.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
