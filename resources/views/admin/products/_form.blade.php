<div class="grid2">
    <div>
        <label for="name">Nama produk</label>
        <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required maxlength="150">
    </div>
    <div>
        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id" required>
            <option value="">— pilih —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="base_price">Harga dasar (Rp)</label>
        <input id="base_price" type="number" name="base_price" min="0" step="1" value="{{ old('base_price', $product->base_price) }}" required>
    </div>
    <div>
        <label for="image">Foto produk</label>
        <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
        <div class="hint">JPG, PNG, atau WEBP, maksimal 2 MB.</div>
        @if ($product->image)
            <img class="thumb" src="{{ asset('storage/'.$product->image) }}" alt="">
        @endif
    </div>
</div>

<label for="description">Deskripsi</label>
<textarea id="description" name="description" rows="4" maxlength="5000">{{ old('description', $product->description) }}</textarea>

<label class="check">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
    Aktif (tampil di katalog)
</label>
