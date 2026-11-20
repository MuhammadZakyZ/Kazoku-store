@extends('layouts.admin')

@section('title', 'Portofolio — Panel Admin')

@section('admin')
    <div class="head">
        <h1>Portofolio</h1>
        <a class="btn" href="{{ route('admin.portfolios.create') }}">Tambah portofolio</a>
    </div>

    <table>
        <thead><tr><th>Gambar</th><th>Judul</th><th>Kategori</th><th></th></tr></thead>
        <tbody>
        @forelse ($portfolios as $portfolio)
            <tr>
                <td><img class="thumb" src="{{ asset('storage/'.$portfolio->image) }}" alt=""></td>
                <td>{{ $portfolio->title }}</td>
                <td>{{ $portfolio->category->name }}</td>
                <td class="actions">
                    <a class="btn ghost sm" href="{{ route('admin.portfolios.edit', $portfolio) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.portfolios.destroy', $portfolio) }}" onsubmit="return confirm('Hapus portofolio ini?')">
                        @csrf @method('DELETE')
                        <button class="btn danger sm" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada portofolio.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
