@extends('layouts.app')

@section('title', 'Testimoni — Kazoku Store')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">Cerita dari pelanggan</span>
            <h1>Testimoni pelanggan</h1>
            <p>Setiap karya punya cerita. Ini beberapa pengalaman pelanggan Kazoku Store.</p>
        </div>
    </div>

    @if ($testimonials->isEmpty())
        <div class="empty-state">
            <span class="empty-icon" aria-hidden="true">“</span>
            <h2>Belum ada cerita</h2>
            <p>Testimoni pelanggan akan tampil di sini setelah tersedia.</p>
        </div>
    @else
        <div class="quotes">
            @foreach ($testimonials as $testimonial)
                @include('partials.testimonial-card', ['testimonial' => $testimonial])
            @endforeach
        </div>

        {{ $testimonials->links('pagination.kazoku') }}
    @endif
@endsection
