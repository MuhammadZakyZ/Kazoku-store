<a class="item" href="{{ route('products.show', $product) }}">
    @if ($product->image)
        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy">
    @else
        <div class="ph">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}</div>
    @endif
    <div class="body">
        <span class="tag">{{ $product->category->name }}</span>
        <h3>{{ $product->name }}</h3>
        <div class="price">mulai Rp {{ number_format($product->base_price, 0, ',', '.') }}</div>
    </div>
</a>
