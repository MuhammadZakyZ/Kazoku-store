@extends('layouts.admin')

@section('title', 'Kategori — Panel Admin')

@section('admin')
    <div class="page-heading">
        <div>
            <span class="eyebrow">Atur katalog</span>
            <h1>Kelola kategori</h1>
            <p>Kelompokkan produk dan karya agar lebih mudah ditemukan.</p>
        </div>
    </div>

    <div class="admin-management category-management">
        <section>
            <h2>Daftar kategori</h2>
            <table>
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
        </section>

        <section>
            <h2>Tambah kategori</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <label for="name">Nama kategori baru</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100">
                <button class="btn section" type="submit">Tambah</button>
            </form>
        </section>
    </div>
@endsection
