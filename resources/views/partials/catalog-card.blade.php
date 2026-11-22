@php
    $sizes = $product->options->pluck('name');
    $sizeText = $sizes->count() > 1 ? $sizes->first().'–'.$sizes->last() : $sizes->first();
    $meta = collect([
        $sizeText ? 'Ukuran '.$sizeText : null,
        $product->colors->count() ? $product->colors->count().' warna' : null,
    ])->filter()->implode(' · ');
@endphp
<a class="cat-card" href="{{ route('products.show', $product) }}">
    <div class="cat-pic">
        @if ($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="cat-ph" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}</span>
        @endif
    </div>
    <div class="cat-body">
        <span class="cat-cat">{{ $product->category->name }}</span>
        <h3>{{ $product->name }}</h3>
        @if ($meta !== '')
            <p class="cat-meta">{{ $meta }}</p>
        @endif
        <div class="cat-foot">
            <span class="cat-price">Rp {{ number_format($product->base_price, 0, ',', '.') }} / pcs</span>
            <span class="cat-cta">Pesan <i aria-hidden="true">→</i></span>
        </div>
    </div>
</a>
