@extends('layouts.app')

@section('title', 'Testimoni — Kazoku Store')

@section('content')
    <h1>Testimoni</h1>

    @if ($testimonials->isEmpty())
        <p class="empty">Belum ada testimoni.</p>
    @else
        <div class="quotes">
            @foreach ($testimonials as $testimonial)
                @include('partials.testimonial-card', ['testimonial' => $testimonial])
            @endforeach
        </div>

        {{ $testimonials->links('pagination.kazoku') }}
    @endif
@endsection
