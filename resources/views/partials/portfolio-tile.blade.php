<figure class="pf-tile">
    <img src="{{ asset('storage/'.$portfolio->image) }}" alt="{{ $portfolio->title }}" loading="lazy">
    <figcaption>
        <h3>{{ $portfolio->title }}</h3>
        <span>{{ $portfolio->category->name }}</span>
    </figcaption>
</figure>
