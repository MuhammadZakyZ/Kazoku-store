<div class="grid2">
    <div>
        <label for="title">Judul</label>
        <input id="title" type="text" name="title" value="{{ old('title', $portfolio->title) }}" required maxlength="150">
    </div>
    <div>
        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id" required>
            <option value="">— pilih —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $portfolio->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<label for="image">Gambar</label>
<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(! $portfolio->exists)>
<div class="hint">JPG, PNG, atau WEBP, maksimal 2 MB.</div>
@if ($portfolio->image)
    <img class="thumb" src="{{ asset('storage/'.$portfolio->image) }}" alt="">
@endif
