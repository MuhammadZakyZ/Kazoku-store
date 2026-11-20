<blockquote class="quote">
    <div class="stars" aria-label="Rating {{ $testimonial->rating }} dari 5">{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</div>
    <p>{{ $testimonial->message }}</p>
    <footer>— {{ $testimonial->customer_name }}</footer>
</blockquote>
