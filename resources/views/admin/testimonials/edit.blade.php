@extends('layouts.admin')

@section('title', 'Ubah Testimoni — Panel Admin')

@section('admin')
    <h1>Ubah testimoni</h1>

    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}">
        @csrf @method('PUT')
        <div class="grid2">
            <div>
                <label for="customer_name">Nama pelanggan</label>
                <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name', $testimonial->customer_name) }}" required maxlength="100">
            </div>
            <div>
                <label for="rating">Rating (1–5)</label>
                <input id="rating" type="number" name="rating" min="1" max="5" value="{{ old('rating', $testimonial->rating) }}" required>
            </div>
        </div>
        <label for="message">Isi testimoni</label>
        <textarea id="message" name="message" rows="4" required maxlength="1000">{{ old('message', $testimonial->message) }}</textarea>
        <label class="check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $testimonial->is_published))> Tayangkan</label>
        <div class="actions section">
            <button class="btn" type="submit">Simpan</button>
            <a class="btn ghost" href="{{ route('admin.testimonials.index') }}">Batal</a>
        </div>
    </form>
@endsection
