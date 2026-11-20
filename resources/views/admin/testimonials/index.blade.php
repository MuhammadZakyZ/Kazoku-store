@extends('layouts.admin')

@section('title', 'Testimoni — Panel Admin')

@section('admin')
    <h1>Testimoni</h1>
    <p class="hint">Testimoni hanya tampil di situs bila ditayangkan.</p>

    <form method="POST" action="{{ route('admin.testimonials.store') }}" class="line add">
        @csrf
        <div class="grid2">
            <div>
                <label for="customer_name">Nama pelanggan</label>
                <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100">
            </div>
            <div>
                <label for="rating">Rating (1–5)</label>
                <input id="rating" type="number" name="rating" min="1" max="5" value="{{ old('rating', 5) }}" required>
            </div>
        </div>
        <label for="message">Isi testimoni</label>
        <textarea id="message" name="message" rows="3" required maxlength="1000">{{ old('message') }}</textarea>
        <label class="check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published'))> Langsung tayangkan</label>
        <div class="actions section"><button class="btn" type="submit">Tambah testimoni</button></div>
    </form>

    <table class="section">
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
@endsection
