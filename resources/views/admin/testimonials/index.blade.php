@extends('layouts.admin')

@section('title', 'Testimoni — Panel Admin')

@section('admin')
    <div class="page-heading">
        <div>
            <span class="eyebrow">Suara pelanggan</span>
            <h1>Kelola testimoni</h1>
            <p>Atur cerita pelanggan yang tampil di halaman publik.</p>
        </div>
    </div>

    <div class="admin-management testimonial-management">
        <section>
            <h2>Daftar testimoni</h2>
            <table>
                <thead><tr><th>Pelanggan</th><th>Isi</th><th>Rating</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($testimonials as $testimonial)
                    <tr>
                        <td>{{ $testimonial->customer_name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($testimonial->message, 90) }}</td>
                        <td>{{ $testimonial->rating }}/5</td>
                        <td>{!! $testimonial->is_published ? '<span class="badge ok">tayang</span>' : '<span class="badge">tersembunyi</span>' !!}</td>
                        <td class="actions">
                            <form method="POST" action="{{ route('admin.testimonials.publish', $testimonial) }}">
                                @csrf @method('PATCH')
                                <button class="btn ghost sm" type="submit">{{ $testimonial->is_published ? 'Sembunyikan' : 'Tayangkan' }}</button>
                            </form>
                            <a class="btn ghost sm" href="{{ route('admin.testimonials.edit', $testimonial) }}">Ubah</a>
                            <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Hapus testimoni ini?')">
                                @csrf @method('DELETE')
                                <button class="btn danger sm" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada testimoni.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <section>
            <h2>Tambah testimoni</h2>
            <form method="POST" action="{{ route('admin.testimonials.store') }}">
                @csrf
                <label for="customer_name">Nama pelanggan</label>
                <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100">
                <label for="rating">Rating (1–5)</label>
                <input id="rating" type="number" name="rating" min="1" max="5" value="{{ old('rating', 5) }}" required>
                <label for="message">Isi testimoni</label>
                <textarea id="message" name="message" rows="3" required maxlength="1000">{{ old('message') }}</textarea>
                <label class="check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published'))> Langsung tayangkan</label>
                <button class="btn section" type="submit">Tambah testimoni</button>
            </form>
        </section>
    </div>
@endsection
