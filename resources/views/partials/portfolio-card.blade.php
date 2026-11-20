<figure class="item static">
    <img src="{{ asset('storage/'.$portfolio->image) }}" alt="{{ $portfolio->title }}" loading="lazy">
    <figcaption class="body">
        <span class="tag">{{ $portfolio->category->name }}</span>
        <h3>{{ $portfolio->title }}</h3>
    </figcaption>
</figure>
