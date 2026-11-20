@extends('layouts.admin')

@section('title', 'Kelola Produk — Panel Admin')

@section('admin')
@php
$mockups = $product->colors
->filter(fn ($c) => filled($c->mockup_image))
->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'url' => route('media.mockup', $c)])
->values();
@endphp
<div class="head">
    <h1>{{ $product->name }}</h1>
    <a class="btn ghost" href="{{ route('admin.products.index') }}">Kembali</a>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.products._form')
    <div class="actions section"><button class="btn" type="submit">Simpan produk</button></div>
</form>

{{-- ============ Ukuran ============ --}}
<h2 class="section">Ukuran</h2>
<p class="hint">Tambahan harga ditambahkan ke harga dasar. Isi 0 bila tidak ada tambahan.</p>

@foreach ($product->options as $option)
<form method="POST" action="{{ route('admin.options.update', $option) }}" class="row-form line">
    @csrf @method('PUT')
    <div class="f"><label>Ukuran</label><input type="text" name="name" value="{{ $option->name }}" required
            maxlength="50"></div>
    <div class="f"><label>Tambahan harga (Rp)</label><input type="number" name="extra_price" min="0" step="1"
            value="{{ $option->extra_price }}"></div>
    <button class="btn ghost sm" type="submit">Simpan</button>
    <button class="btn danger sm" type="submit" form="del-option-{{ $option->id }}">Hapus</button>
</form>
<form id="del-option-{{ $option->id }}" method="POST" action="{{ route('admin.options.destroy', $option) }}"
    onsubmit="return confirm('Hapus ukuran ini?')">
    @csrf @method('DELETE')
</form>
@endforeach

<form method="POST" action="{{ route('admin.products.options.store', $product) }}" class="row-form line add">
    @csrf
    <div class="f"><label>Ukuran baru</label><input type="text" name="name" required maxlength="50"
            placeholder="mis. M"></div>
    <div class="f"><label>Tambahan harga (Rp)</label><input type="number" name="extra_price" min="0" step="1" value="0">
    </div>
    <button class="btn sm" type="submit">Tambah ukuran</button>
</form>

{{-- ============ Warna + mockup ============ --}}
<h2 class="section">Warna dan mockup</h2>
<p class="hint">Satu file mockup per warna (JPG/PNG/WEBP, maks. 4 MB). Semua mockup satu produk harus berukuran piksel
    sama. Warna tanpa file mockup tidak mendapat preview.</p>

@foreach ($product->colors as $color)
<form method="POST" action="{{ route('admin.colors.update', $color) }}" enctype="multipart/form-data"
    class="row-form line">
    @csrf @method('PUT')
    <div class="f"><label>Warna</label><input type="text" name="name" value="{{ $color->name }}" required
            maxlength="50"></div>
    <div class="f">
        <label>Mockup</label>
        @if ($color->mockup_image)
        <img class="thumb" src="{{ route('media.mockup', $color) }}" alt="Mockup {{ $color->name }}">
        <label class="check"><input type="checkbox" name="remove_mockup" value="1"> Hapus file mockup</label>
        @else
        <span class="badge">belum ada</span>
        @endif
        <input type="file" name="mockup_image" accept="image/jpeg,image/png,image/webp">
    </div>
    <button class="btn ghost sm" type="submit">Simpan</button>
    <button class="btn danger sm" type="submit" form="del-color-{{ $color->id }}">Hapus</button>
</form>
<form id="del-color-{{ $color->id }}" method="POST" action="{{ route('admin.colors.destroy', $color) }}"
    onsubmit="return confirm('Hapus warna ini beserta file mockupnya?')">
    @csrf @method('DELETE')
</form>
@endforeach

<form method="POST" action="{{ route('admin.products.colors.store', $product) }}" enctype="multipart/form-data"
    class="row-form line add">
    @csrf
    <div class="f"><label>Warna baru</label><input type="text" name="name" required maxlength="50"
            placeholder="mis. Hitam"></div>
    <div class="f"><label>Mockup (opsional)</label><input type="file" name="mockup_image"
            accept="image/jpeg,image/png,image/webp"></div>
    <button class="btn sm" type="submit">Tambah warna</button>
</form>

{{-- ============ Area sablon ============ --}}
<h2 class="section">Area sablon</h2>
<p class="hint">
    Ukuran tiap area tetap. Biaya area ditambahkan ke harga satuan bila area dipilih.
    Posisi preview adalah persen terhadap gambar mockup (X, Y = sudut kiri atas; lebar dan tinggi area).
    Isi keempatnya atau kosongkan semuanya.
</p>

<div class="editor" id="ed">
    <p class="hint" id="ed-msg" hidden></p>
    <div id="ed-ui" hidden>
        <div class="ed-bar">
            <label>Warna mockup
                <select id="ed-color"></select>
            </label>
            <div id="ed-areas" class="chips"></div>
        </div>
        <div class="stage" id="ed-stage"><img id="ed-img" alt="Mockup" draggable="false"></div>
        <div class="actions">
            <button type="button" class="btn sm" id="ed-save">Simpan posisi area terpilih</button>
            <button type="button" class="btn ghost sm" id="ed-reset">Buat / atur ulang kotak</button>
            <button type="button" class="btn ghost sm" id="ed-clear">Hapus posisi</button>
        </div>
        <p class="hint" id="ed-info"></p>
    </div>
</div>

@foreach ($product->printAreas as $area)
<form method="POST" action="{{ route('admin.areas.update', $area) }}" class="row-form line area-form"
    data-area="{{ $area->id }}" data-name="{{ $area->name }}">
    @csrf @method('PUT')
    <div class="f"><label>Posisi</label><input type="text" name="name" value="{{ $area->name }}" required
            maxlength="100"></div>
    <div class="f s"><label>Lebar (cm)</label><input type="number" name="width_cm" min="0.1" step="0.1"
            value="{{ $area->width_cm }}" required></div>
    <div class="f s"><label>Tinggi (cm)</label><input type="number" name="height_cm" min="0.1" step="0.1"
            value="{{ $area->height_cm }}" required></div>
    <div class="f"><label>Biaya (Rp)</label><input type="number" name="price" min="0" step="1"
            value="{{ $area->price }}"></div>
    <div class="f s"><label>X (%)</label><input type="number" name="preview_x" min="0" max="100" step="0.01"
            value="{{ $area->preview_x }}"></div>
    <div class="f s"><label>Y (%)</label><input type="number" name="preview_y" min="0" max="100" step="0.01"
            value="{{ $area->preview_y }}"></div>
    <div class="f s"><label>Lebar (%)</label><input type="number" name="preview_w" min="0" max="100" step="0.01"
            value="{{ $area->preview_w }}"></div>
    <div class="f s"><label>Tinggi (%)</label><input type="number" name="preview_h" min="0" max="100" step="0.01"
            value="{{ $area->preview_h }}"></div>
    <button class="btn ghost sm" type="submit">Simpan</button>
    <button class="btn danger sm" type="submit" form="del-area-{{ $area->id }}">Hapus</button>
</form>
<form id="del-area-{{ $area->id }}" method="POST" action="{{ route('admin.areas.destroy', $area) }}"
    onsubmit="return confirm('Hapus area ini?')">
    @csrf @method('DELETE')
</form>
@endforeach

<form method="POST" action="{{ route('admin.products.areas.store', $product) }}" class="row-form line add">
    @csrf
    <div class="f"><label>Posisi baru</label><input type="text" name="name" required maxlength="100"
            placeholder="mis. Dada kiri"></div>
    <div class="f s"><label>Lebar (cm)</label><input type="number" name="width_cm" min="0.1" step="0.1" required></div>
    <div class="f s"><label>Tinggi (cm)</label><input type="number" name="height_cm" min="0.1" step="0.1" required>
    </div>
    <div class="f"><label>Biaya (Rp)</label><input type="number" name="price" min="0" step="1" value="0"></div>
    <div class="f s"><label>X (%)</label><input type="number" name="preview_x" min="0" max="100" step="0.01"></div>
    <div class="f s"><label>Y (%)</label><input type="number" name="preview_y" min="0" max="100" step="0.01"></div>
    <div class="f s"><label>Lebar (%)</label><input type="number" name="preview_w" min="0" max="100" step="0.01"></div>
    <div class="f s"><label>Tinggi (%)</label><input type="number" name="preview_h" min="0" max="100" step="0.01"></div>
    <button class="btn sm" type="submit">Tambah area</button>
</form>

<script>
(function() {
    var mockups = @json($mockups);
    var msg = document.getElementById('ed-msg');
    var ui = document.getElementById('ed-ui');
    var forms = Array.prototype.slice.call(document.querySelectorAll('.area-form'));

    if (!mockups.length) {
        msg.textContent = 'Editor posisi aktif setelah ada minimal satu warna dengan file mockup.';
        msg.hidden = false;
        return;
    }
    if (!forms.length) {
        msg.textContent = 'Editor posisi aktif setelah ada minimal satu area sablon yang tersimpan.';
        msg.hidden = false;
        return;
    }
    ui.hidden = false;

    var stage = document.getElementById('ed-stage');
    var img = document.getElementById('ed-img');
    var colorSel = document.getElementById('ed-color');
    var chipsEl = document.getElementById('ed-areas');
    var info = document.getElementById('ed-info');
    var names = ['preview_x', 'preview_y', 'preview_w', 'preview_h'];
    var keys = ['x', 'y', 'w', 'h'];
    var byId = {};
    var sel = null;
    var drag = null;

    forms.forEach(function(f) {
        byId[f.dataset.area] = f;
    });

    function field(form, name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function clamp(v, lo, hi) {
        return Math.max(lo, Math.min(hi, v));
    }

    function round2(v) {
        return Math.round(v * 100) / 100;
    }

    function read(form) {
        var out = {};
        for (var i = 0; i < 4; i++) {
            var v = parseFloat(field(form, names[i]).value);
            if (isNaN(v)) {
                return null;
            }
            out[keys[i]] = v;
        }
        return out;
    }

    function write(form, b) {
        for (var i = 0; i < 4; i++) {
            field(form, names[i]).value = b ? String(round2(b[keys[i]])) : '';
        }
    }

    function remember(key, val) {
        try {
            sessionStorage.setItem(key, val);
        } catch (e) {}
    }

    function recall(key) {
        try {
            return sessionStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    function hue(id) {
        var i = forms.indexOf(byId[id]);
        return (i * 67) % 360;
    }

    function styleBox(el, b, id) {
        el.style.left = b.x + '%';
        el.style.top = b.y + '%';
        el.style.width = b.w + '%';
        el.style.height = b.h + '%';
        var c = 'hsl(' + hue(id) + ',70%,48%)';
        el.style.borderColor = c;
        el.style.background = 'hsla(' + hue(id) + ',70%,48%,0.18)';
    }

    function render() {
        Array.prototype.slice.call(stage.querySelectorAll('.box')).forEach(function(n) {
            n.remove();
        });

        forms.forEach(function(f) {
            var id = f.dataset.area;
            var b = read(f);
            if (!b) {
                return;
            }
            var box = document.createElement('div');
            box.className = 'box' + (id === sel ? ' sel' : ' dim');
            box.dataset.id = id;
            var label = document.createElement('span');
            label.textContent = f.dataset.name;
            box.appendChild(label);
            if (id === sel) {
                ['nw', 'ne', 'sw', 'se'].forEach(function(h) {
                    var hd = document.createElement('i');
                    hd.className = 'handle ' + h;
                    hd.dataset.h = h;
                    box.appendChild(hd);
                });
            }
            styleBox(box, b, id);
            stage.appendChild(box);
        });

        chipsEl.innerHTML = '';
        forms.forEach(function(f) {
            var id = f.dataset.area;
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'chip' + (id === sel ? ' on' : '') + (read(f) ? '' : ' nopos');
            chip.textContent = f.dataset.name + (read(f) ? '' : ' (belum ditempatkan)');
            chip.addEventListener('click', function() {
                select(id);
            });
            chipsEl.appendChild(chip);
        });

        var cur = sel && byId[sel] ? byId[sel] : null;
        info.textContent = cur ?
            'Area terpilih: ' + cur.dataset.name +
            '. Geser kotak untuk memindah, tarik sudut untuk mengubah ukuran, lalu klik "Simpan posisi area terpilih".' :
            '';
    }

    function paintSelected() {
        var box = stage.querySelector('.box.sel');
        var b = sel ? read(byId[sel]) : null;
        if (box && b) {
            styleBox(box, b, sel);
        }
    }

    function select(id) {
        sel = id;
        remember('ed-area-{{ $product->id }}', id);
        render();
    }

    // ---- warna mockup ----
    mockups.forEach(function(m) {
        var o = document.createElement('option');
        o.value = m.url;
        o.textContent = m.name;
        colorSel.appendChild(o);
    });
    img.src = mockups[0].url;
    colorSel.addEventListener('change', function() {
        img.src = colorSel.value;
    });

    // ---- seret dan ubah ukuran ----
    stage.addEventListener('pointerdown', function(e) {
        var boxEl = e.target.closest ? e.target.closest('.box') : null;
        if (!boxEl) {
            return;
        }
        var id = boxEl.dataset.id;
        if (id !== sel) {
            select(id);
            return;
        }

        var b = read(byId[id]);
        if (!b) {
            return;
        }
        drag = {
            id: id,
            mode: e.target.dataset && e.target.dataset.h ? e.target.dataset.h : 'move',
            sx: e.clientX,
            sy: e.clientY,
            b: b,
            rect: img.getBoundingClientRect()
        };
        try {
            stage.setPointerCapture(e.pointerId);
        } catch (err) {}
        e.preventDefault();
    });

    stage.addEventListener('pointermove', function(e) {
        if (!drag) {
            return;
        }
        var dx = (e.clientX - drag.sx) / drag.rect.width * 100;
        var dy = (e.clientY - drag.sy) / drag.rect.height * 100;
        var b = drag.b;
        var n = {
            x: b.x,
            y: b.y,
            w: b.w,
            h: b.h
        };
        var MIN = 2;
        var m = drag.mode;

        if (m === 'move') {
            n.x = clamp(b.x + dx, 0, 100 - b.w);
            n.y = clamp(b.y + dy, 0, 100 - b.h);
        } else {
            if (m.indexOf('e') > -1) {
                n.w = clamp(b.w + dx, MIN, 100 - b.x);
            }
            if (m.indexOf('s') > -1) {
                n.h = clamp(b.h + dy, MIN, 100 - b.y);
            }
            if (m.indexOf('w') > -1) {
                var nx = clamp(b.x + dx, 0, b.x + b.w - MIN);
                n.w = b.w + (b.x - nx);
                n.x = nx;
            }
            if (m.indexOf('n') > -1) {
                var ny = clamp(b.y + dy, 0, b.y + b.h - MIN);
                n.h = b.h + (b.y - ny);
                n.y = ny;
            }
        }
        write(byId[drag.id], n);
        paintSelected();
    });

    function endDrag() {
        drag = null;
    }
    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);

    // ---- isian angka diubah manual ----
    forms.forEach(function(f) {
        names.forEach(function(n) {
            field(f, n).addEventListener('input', render);
        });
    });

    // ---- tombol ----
    document.getElementById('ed-reset').addEventListener('click', function() {
        if (!sel) {
            return;
        }
        var f = byId[sel];
        var wcm = parseFloat(field(f, 'width_cm').value);
        var hcm = parseFloat(field(f, 'height_cm').value);
        var w = 30,
            h = 30;
        if (wcm > 0 && hcm > 0 && img.naturalWidth > 0 && img.naturalHeight > 0) {
            // samakan rasio kotak dengan rasio ukuran area (cm)
            h = w * (img.naturalWidth / img.naturalHeight) * (hcm / wcm);
            if (h > 90) {
                w = w * 90 / h;
                h = 90;
            }
        }
        write(f, {
            x: round2((100 - w) / 2),
            y: round2((100 - h) / 2),
            w: w,
            h: h
        });
        render();
    });

    document.getElementById('ed-clear').addEventListener('click', function() {
        if (!sel) {
            return;
        }
        write(byId[sel], null);
        render();
    });

    document.getElementById('ed-save').addEventListener('click', function() {
        if (!sel) {
            return;
        }
        remember('ed-scroll', '1');
        byId[sel].requestSubmit();
    });

    // ---- awal ----
    var saved = recall('ed-area-{{ $product->id }}');
    sel = saved && byId[saved] ? saved : forms[0].dataset.area;
    render();

    if (recall('ed-scroll') === '1') {
        remember('ed-scroll', '0');
        document.getElementById('ed').scrollIntoView();
    }
})();
</script>
@endsection