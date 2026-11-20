@extends('layouts.app')

@section('title', 'Pesan '.$product->name.' — Kazoku Store')

@section('content')
    <p class="crumb"><a href="{{ route('products.show', $product) }}">‹ {{ $product->name }}</a></p>
    <h1>Pesan: {{ $product->name }}</h1>

    @if ($errors->any())
        <div class="alert err">
            <strong>Periksa kembali isian Anda:</strong>
            <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
            @if (old('design_type') === 'punya_desain')
                <div>File desain tidak bisa terisi ulang otomatis. Pilih kembali file untuk tiap area.</div>
            @endif
        </div>
    @endif

    <ol class="steps" id="steps">
        <li><button type="button" data-go="1">1. Produk</button></li>
        <li><button type="button" data-go="2">2. Desain</button></li>
        <li><button type="button" data-go="3">3. Pengiriman</button></li>
        <li><button type="button" data-go="4">4. Ringkasan</button></li>
    </ol>

    <form id="order-form" class="panel" method="POST" action="{{ route('orders.store', $product) }}" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- ===== Langkah 1: produk ===== --}}
        <fieldset class="step" data-step="1">
            <legend>Produk</legend>

            @if ($product->options->isNotEmpty())
                <label for="option_id">Ukuran</label>
                <select id="option_id" name="option_id" required>
                    <option value="">— pilih ukuran —</option>
                    @foreach ($product->options as $option)
                        <option value="{{ $option->id }}" @selected(old('option_id') == $option->id)>
                            {{ $option->name }}@if ($option->extra_price > 0) (+Rp {{ number_format($option->extra_price, 0, ',', '.') }})@endif
                        </option>
                    @endforeach
                </select>
            @endif

            @if ($product->colors->isNotEmpty())
                <label>Warna</label>
                <div class="chips" id="color-chips">
                    @foreach ($product->colors as $color)
                        <label class="chip pick">
                            <input type="radio" name="color_id" value="{{ $color->id }}" required
                                @checked((string) old('color_id', $loop->first ? $color->id : '') === (string) $color->id)>
                            {{ $color->name }}
                        </label>
                    @endforeach
                </div>
            @endif

            <label for="quantity">Jumlah</label>
            <input id="quantity" type="number" name="quantity" min="1" max="10000" step="1" value="{{ old('quantity', 1) }}" required>
        </fieldset>

        {{-- ===== Langkah 2: desain ===== --}}
        <fieldset class="step" data-step="2" hidden>
            <legend>Desain</legend>

            <div class="radios">
                <label class="chip pick"><input type="radio" name="design_type" value="punya_desain" @checked(old('design_type', 'punya_desain') === 'punya_desain')> Saya sudah punya desain</label>
                <label class="chip pick"><input type="radio" name="design_type" value="konsultasi" @checked(old('design_type') === 'konsultasi')> Saya ingin konsultasi desain</label>
            </div>

            <div id="consult-note" class="hint" hidden>
                Anda tidak perlu mengunggah desain sekarang. Setelah pesanan dibuat, hubungi admin lewat WhatsApp dengan menyebut kode pesanan.
                Biaya area sablon belum dihitung pada jalur ini.
            </div>

            <div id="design-block">
                @if ($product->printAreas->isEmpty())
                    <div class="alert err">Produk ini belum punya area sablon, jadi belum bisa dipesan dengan desain sendiri. Pilih konsultasi.</div>
                @else
                    <h2>Area sablon</h2>
                    <p class="hint">Pilih satu atau beberapa area, lalu unggah satu file per area (PNG, JPG, WEBP, atau PDF, maksimal 10 MB). Biaya semua area yang dipilih ditambahkan ke harga satuan.</p>

                    @foreach ($product->printAreas as $area)
                        <div class="area-card" data-area="{{ $area->id }}">
                            <label class="check">
                                <input type="checkbox" name="area_ids[]" value="{{ $area->id }}" @checked(in_array($area->id, array_map('intval', (array) old('area_ids', []))))>
                                <strong>{{ $area->name }}</strong>
                                <span class="hint">{{ $area->size_label }} · {{ $area->price > 0 ? '+Rp '.number_format($area->price, 0, ',', '.') : 'gratis' }}</span>
                            </label>
                            <input class="area-file" type="file" name="designs[{{ $area->id }}]" accept="image/png,image/jpeg,image/webp,application/pdf" disabled>
                        </div>
                    @endforeach
                    <div id="area-error" class="err-text" hidden>Pilih minimal satu area sablon.</div>
                @endif

                <div id="pv" hidden>
                    <h2>Preview</h2>
                    <p class="hint" id="pv-note"></p>
                    <div class="pv-stage" id="pv-stage"><img id="pv-img" alt="Mockup" draggable="false"></div>
                    <p class="hint">Preview hanya gambaran penempatan desain pada produk, bukan hasil cetak akhir.</p>
                </div>
            </div>
        </fieldset>

        {{-- ===== Langkah 3: pengiriman ===== --}}
        <fieldset class="step" data-step="3" hidden>
            <legend>Pengiriman</legend>

            <div class="radios">
                <label class="chip pick"><input type="radio" name="delivery_method" value="ambil" @checked(old('delivery_method', 'ambil') === 'ambil')> Ambil di toko</label>
                <label class="chip pick"><input type="radio" name="delivery_method" value="kirim" @checked(old('delivery_method') === 'kirim')> Kirim</label>
            </div>

            <div id="ship-block">
                @if (empty($data['couriers']))
                    <div class="alert err">Belum ada ekspedisi dengan tarif. Pilih ambil di toko, atau hubungi admin.</div>
                @else
                    <label for="courier_id">Ekspedisi</label>
                    <select id="courier_id" name="courier_id"></select>

                    <label for="shipping_rate_id">Tujuan</label>
                    <select id="shipping_rate_id" name="shipping_rate_id"></select>

                    <div class="hint">Ongkir: <strong id="ship-cost">Rp 0</strong></div>

                    <label for="shipping_address">Alamat lengkap</label>
                    <textarea id="shipping_address" name="shipping_address" rows="3" maxlength="500" placeholder="Nama penerima, jalan, nomor, RT/RW, kelurahan, kode pos">{{ old('shipping_address') }}</textarea>
                @endif
            </div>
        </fieldset>

        {{-- ===== Langkah 4: ringkasan ===== --}}
        <fieldset class="step" data-step="4" hidden>
            <legend>Ringkasan dan pembayaran</legend>

            <table class="mini">
                <tbody>
                <tr><td>Harga dasar</td><td class="r" id="s-base"></td></tr>
                <tr><td>Tambahan ukuran</td><td class="r" id="s-size"></td></tr>
                <tr><td>Biaya area sablon</td><td class="r" id="s-area"></td></tr>
                <tr><td><strong>Harga satuan</strong></td><td class="r"><strong id="s-unit"></strong></td></tr>
                <tr><td>Jumlah</td><td class="r" id="s-qty"></td></tr>
                <tr><td>Ongkir</td><td class="r" id="s-ship"></td></tr>
                <tr><td><strong>Total</strong></td><td class="r"><strong id="s-total"></strong></td></tr>
                </tbody>
            </table>
            <p class="hint">Total akhir dihitung ulang oleh server saat pesanan dikirim. Biaya revisi (bila ada) ditambahkan admin kemudian.</p>

            <h2>Metode pembayaran</h2>
            <div class="paylist">
                <label class="pay"><input type="radio" name="payment_method" value="dp" required @checked(old('payment_method', 'dp') === 'dp')>
                    <span><strong>DP</strong> — bayar <span id="s-dp"></span> sekarang, sisanya saat pelunasan.</span></label>
                <label class="pay"><input type="radio" name="payment_method" value="penuh_di_awal" @checked(old('payment_method') === 'penuh_di_awal')>
                    <span><strong>Bayar penuh di awal</strong> — <span id="s-full"></span>.</span></label>
                <label class="pay"><input type="radio" name="payment_method" value="di_akhir" @checked(old('payment_method') === 'di_akhir')>
                    <span><strong>Bayar di akhir</strong> — tanpa bukti bayar; admin menandai lunas saat pembayaran diterima.</span></label>
            </div>

            <div class="actions section">
                <button type="submit" class="btn" id="submit-btn">Kirim pesanan</button>
            </div>
        </fieldset>

        <div class="stepnav">
            <button type="button" class="btn ghost" id="btn-prev" hidden>‹ Kembali</button>
            <span class="hint" id="total-bar"></span>
            <button type="button" class="btn" id="btn-next">Lanjut ›</button>
        </div>
    </form>

    <script>
    (function () {
        var data = @json($data);
        var form = document.getElementById('order-form');
        var steps = Array.prototype.slice.call(form.querySelectorAll('.step'));
        var tabs = Array.prototype.slice.call(document.querySelectorAll('#steps button'));
        var btnPrev = document.getElementById('btn-prev');
        var btnNext = document.getElementById('btn-next');
        var totalBar = document.getElementById('total-bar');
        var current = 1;

        var areasById = {};
        data.areas.forEach(function (a) { areasById[a.id] = a; });
        var colorsById = {};
        data.colors.forEach(function (c) { colorsById[c.id] = c; });
        var optionsById = {};
        data.options.forEach(function (o) { optionsById[o.id] = o; });

        function rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); }
        function q(sel, root) { return (root || document).querySelector(sel); }
        function qa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
        function checkedVal(name) { var el = q('input[name="' + name + '"]:checked', form); return el ? el.value : null; }

        // ---------- keadaan pilihan ----------
        function designType() { return checkedVal('design_type'); }
        function delivery() { return checkedVal('delivery_method'); }
        function selectedAreaIds() {
            return qa('input[name="area_ids[]"]:checked', form).map(function (el) { return parseInt(el.value, 10); });
        }
        function selectedOption() {
            var el = q('#option_id', form);
            return el && el.value ? optionsById[el.value] : null;
        }
        function selectedColor() {
            var v = checkedVal('color_id');
            return v ? colorsById[v] : null;
        }

        // ---------- ekspedisi dan tujuan ----------
        var courierSel = q('#courier_id', form);
        var rateSel = q('#shipping_rate_id', form);
        var shipCostEl = q('#ship-cost', form);

        function fillCouriers() {
            if (!courierSel) { return; }
            courierSel.innerHTML = '<option value="">— pilih ekspedisi —</option>';
            data.couriers.forEach(function (c) {
                var o = document.createElement('option');
                o.value = c.id; o.textContent = c.name;
                courierSel.appendChild(o);
            });
            if (data.old.courier_id) { courierSel.value = data.old.courier_id; }
            fillRates();
            if (data.old.shipping_rate_id) { rateSel.value = data.old.shipping_rate_id; }
        }
        function fillRates() {
            if (!rateSel) { return; }
            rateSel.innerHTML = '<option value="">— pilih tujuan —</option>';
            var c = null;
            data.couriers.forEach(function (x) { if (String(x.id) === courierSel.value) { c = x; } });
            if (c) {
                c.rates.forEach(function (r) {
                    var o = document.createElement('option');
                    o.value = r.id; o.textContent = r.destination + ' (' + rp(r.rate) + ')';
                    o.setAttribute('data-rate', r.rate);
                    rateSel.appendChild(o);
                });
            }
        }
        function shippingCost() {
            if (delivery() !== 'kirim' || !rateSel) { return 0; }
            var opt = rateSel.options[rateSel.selectedIndex];
            return opt && opt.getAttribute('data-rate') ? parseInt(opt.getAttribute('data-rate'), 10) : 0;
        }

        // ---------- perhitungan (hanya tampilan; server menghitung ulang) ----------
        function calc() {
            var qty = parseInt(q('#quantity', form).value, 10);
            if (!(qty > 0)) { qty = 0; }
            var opt = selectedOption();
            var size = opt ? opt.extra : 0;
            var area = 0;
            if (designType() === 'punya_desain') {
                selectedAreaIds().forEach(function (id) { area += areasById[id].price; });
            }
            var unit = data.basePrice + size + area;
            var ship = shippingCost();
            var total = unit * qty + ship;
            var dp = Math.round(total * data.dpPercent / 100);
            return { size: size, area: area, unit: unit, qty: qty, ship: ship, total: total, dp: dp };
        }
        function paintSummary() {
            var c = calc();
            q('#s-base').textContent = rp(data.basePrice);
            q('#s-size').textContent = rp(c.size);
            q('#s-area').textContent = rp(c.area);
            q('#s-unit').textContent = rp(c.unit);
            q('#s-qty').textContent = c.qty;
            q('#s-ship').textContent = rp(c.ship);
            q('#s-total').textContent = rp(c.total);
            q('#s-dp').textContent = rp(c.dp);
            q('#s-full').textContent = 'bayar ' + rp(c.total);
            if (shipCostEl) { shipCostEl.textContent = rp(c.ship); }
            totalBar.textContent = 'Perkiraan total: ' + rp(c.total);
        }

        // ---------- blok yang bergantung pilihan ----------
        function toggleBlocks() {
            var consult = designType() === 'konsultasi';
            q('#design-block').hidden = consult;
            q('#consult-note').hidden = !consult;
            qa('#design-block input[name="area_ids[]"]').forEach(function (el) { el.disabled = consult; });

            qa('.area-card').forEach(function (card) {
                var cb = q('input[type=checkbox]', card);
                var file = q('input[type=file]', card);
                var on = cb.checked && !consult;
                file.disabled = !on;
                file.required = on;
                file.hidden = !on;
            });

            var ship = delivery() === 'kirim';
            q('#ship-block').hidden = !ship;
            qa('#ship-block select, #ship-block textarea').forEach(function (el) {
                el.disabled = !ship;
                el.required = ship;
            });
        }

        // ---------- preview ----------
        var pv = q('#pv'), pvImg = q('#pv-img'), pvStage = q('#pv-stage'), pvNote = q('#pv-note');
        var urls = {};

        function artFor(areaId) {
            var card = q('.area-card[data-area="' + areaId + '"]');
            var file = card ? q('input[type=file]', card).files[0] : null;
            if (!file) { return { kind: 'none' }; }
            if (file.type === 'application/pdf' || /\.pdf$/i.test(file.name)) { return { kind: 'pdf' }; }
            if (!/^image\//.test(file.type)) { return { kind: 'none' }; }
            var key = areaId + ':' + file.name + ':' + file.size + ':' + file.lastModified;
            if (!urls[key]) { urls[key] = URL.createObjectURL(file); }
            return { kind: 'image', url: urls[key] };
        }

        function renderPreview() {
            if (!pv) { return; }
            var show = data.hasPreview && designType() === 'punya_desain' && selectedAreaIds().length > 0;
            pv.hidden = !show;
            if (!show) { return; }

            var color = selectedColor();
            qa('.pv-box', pvStage).forEach(function (n) { n.remove(); });

            if (!color || !color.mockup) {
                pvImg.hidden = true;
                pvNote.textContent = 'Warna ini belum punya foto mockup, jadi preview belum tersedia. Pesanan tetap bisa dilanjutkan.';
                return;
            }
            pvImg.hidden = false;
            if (pvImg.getAttribute('src') !== color.mockup) { pvImg.src = color.mockup; }

            var missing = [];
            selectedAreaIds().forEach(function (id) {
                var a = areasById[id];
                if (a.x === null || a.y === null || a.w === null || a.h === null) { missing.push(a.name); return; }
                var box = document.createElement('div');
                box.className = 'pv-box';
                box.style.left = a.x + '%'; box.style.top = a.y + '%';
                box.style.width = a.w + '%'; box.style.height = a.h + '%';
                var art = artFor(id);
                if (art.kind === 'image') {
                    var im = document.createElement('img');
                    im.className = 'pv-art'; im.src = art.url; im.alt = 'Desain ' + a.name;
                    box.appendChild(im);
                } else {
                    var lb = document.createElement('span');
                    lb.className = 'pv-label';
                    lb.textContent = art.kind === 'pdf' ? 'PDF: preview tidak tersedia' : a.name;
                    box.appendChild(lb);
                }
                pvStage.appendChild(box);
            });

            pvNote.textContent = missing.length
                ? 'Area ini belum punya posisi preview: ' + missing.join(', ') + '.'
                : 'Pratinjau desain Anda pada warna terpilih.';
        }

        // ---------- navigasi langkah ----------
        function show(n) {
            current = n;
            steps.forEach(function (s) { s.hidden = parseInt(s.getAttribute('data-step'), 10) !== n; });
            tabs.forEach(function (t) { t.classList.toggle('on', parseInt(t.getAttribute('data-go'), 10) === n); });
            btnPrev.hidden = n === 1;
            btnNext.hidden = n === steps.length;
            if (n === 4) { paintSummary(); }
            window.scrollTo(0, 0);
        }

        function validateStep(n) {
            var step = steps[n - 1];
            if (n === 2 && designType() === 'punya_desain' && data.areas.length === 0) {
                var warn = q('#design-block .alert');
                if (warn) { warn.scrollIntoView({ block: 'center' }); }
                return false;
            }
            if (n === 3 && delivery() === 'kirim' && data.couriers.length === 0) {
                var warn3 = q('#ship-block .alert');
                if (warn3) { warn3.scrollIntoView({ block: 'center' }); }
                return false;
            }
            if (n === 2 && designType() === 'punya_desain' && data.areas.length > 0) {
                var err = q('#area-error');
                if (selectedAreaIds().length === 0) {
                    err.hidden = false;
                    err.scrollIntoView({ block: 'center' });
                    return false;
                }
                err.hidden = true;
            }
            var fields = qa('input, select, textarea', step);
            for (var i = 0; i < fields.length; i++) {
                var f = fields[i];
                if (f.disabled || f.type === 'hidden') { continue; }
                if (!f.checkValidity()) { f.reportValidity(); return false; }
            }
            return true;
        }

        function refresh() { toggleBlocks(); paintSummary(); renderPreview(); }

        // ---------- pasang kejadian ----------
        form.addEventListener('change', function (e) {
            if (e.target === courierSel) { fillRates(); }
            refresh();
        });
        form.addEventListener('input', function (e) {
            if (e.target.id === 'quantity') { paintSummary(); }
        });

        btnNext.addEventListener('click', function () {
            if (validateStep(current)) { show(current + 1); }
        });
        btnPrev.addEventListener('click', function () { show(current - 1); });
        tabs.forEach(function (t) {
            t.addEventListener('click', function () {
                var target = parseInt(t.getAttribute('data-go'), 10);
                if (target > current) {
                    for (var s = current; s < target; s++) {
                        if (!validateStep(s)) { show(s); return; }
                    }
                }
                show(target);
            });
        });

        form.addEventListener('submit', function (e) {
            for (var s = 1; s <= steps.length; s++) {
                if (!validateStep(s)) { e.preventDefault(); show(s); return; }
            }
            q('#submit-btn').disabled = true;
        });

        // ---------- awal ----------
        fillCouriers();
        refresh();
        show(1);
    })();
    </script>
@endsection
