@extends('layouts.admin')

@section('title', 'Pengaturan — Panel Admin')

@section('admin')
<h1>Pengaturan</h1>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')

    <label for="whatsapp_number">Nomor WhatsApp admin</label>
    <input id="whatsapp_number" type="tel" name="whatsapp_number"
        value="{{ old('whatsapp_number', $values['whatsapp_number']) }}" placeholder="08xxxxxxxxxx">
    <div class="hint">Dipakai tombol konsultasi. Disimpan dalam format 628xxxxxxxxxx.</div>

    <div class="grid2">
        <div>
            <label for="dp_percent">Persen DP (%)</label>
            <input id="dp_percent" type="number" name="dp_percent" min="0" max="100" step="1"
                value="{{ old('dp_percent', $values['dp_percent']) }}" required>
        </div>
        <div>
            <label for="free_revision_limit">Batas revisi gratis (kali)</label>
            <input id="free_revision_limit" type="number" name="free_revision_limit" min="0" max="100" step="1"
                value="{{ old('free_revision_limit', $values['free_revision_limit']) }}" required>
        </div>
    </div>

    <label for="address">Alamat usaha</label>
    <textarea id="address" name="address" rows="3" maxlength="500">{{ old('address', $values['address']) }}</textarea>

    <label for="opening_hours">Jam buka</label>
    <textarea id="opening_hours" name="opening_hours" rows="3" maxlength="500"
        placeholder="mis. Senin–Sabtu, 09.00–17.00 WIB">{{ old('opening_hours', $values['opening_hours']) }}</textarea>

    <label for="payment_instructions">Informasi pembayaran (rekening)</label>
    <textarea id="payment_instructions" name="payment_instructions" rows="4" maxlength="1000"
        placeholder="mis. Bank BCA 1234567890 a.n. Nama Pemilik">{{ old('payment_instructions', $values['payment_instructions']) }}</textarea>
    <div class="hint">Ditampilkan kepada pelanggan di halaman pesanan sebagai tujuan transfer.</div>

    <div class="actions section"><button class="btn" type="submit">Simpan pengaturan</button></div>
</form>
@endsection