@extends('layouts.admin')

@section('title', 'Portofolio — Panel Admin')

@section('admin')
    <div class="head">
        <div>
            <span class="eyebrow">Kelola karya</span>
            <h1>Portofolio</h1>
        </div>
        <a class="btn" href="#create-portfolio">Tambah portofolio</a>
    </div>

    <div class="admin-management portfolio-management">
        <section>
            <h2>Daftar karya</h2>
            <div class="portfolio-admin-grid">
                @forelse ($portfolios as $portfolio)
                    <article class="portfolio-admin-card">
                        <img class="portfolio-admin-image" src="{{ asset('storage/'.$portfolio->image) }}" alt="{{ $portfolio->title }}" loading="lazy">
                        <div class="body">
                            <span class="tag">{{ $portfolio->category->name }}</span>
                            <h3>{{ $portfolio->title }}</h3>
                            <div class="actions">
                                <a class="btn ghost sm" href="{{ route('admin.portfolios.edit', $portfolio) }}">Ubah</a>
                                <button class="btn danger sm" type="button" data-dialog-open="delete-portfolio-{{ $portfolio->id }}">Hapus</button>
                            </div>
                        </div>
                        <dialog class="confirm-dialog" id="delete-portfolio-{{ $portfolio->id }}" aria-labelledby="delete-title-{{ $portfolio->id }}">
                            <div class="confirm-dialog-inner">
                                <span class="confirm-dialog-icon" aria-hidden="true">!</span>
                                <h2 id="delete-title-{{ $portfolio->id }}">Hapus karya ini?</h2>
                                <p>Karya “{{ $portfolio->title }}” akan dihapus dari portofolio. Tindakan ini tidak dapat dibatalkan.</p>
                                <div class="dialog-actions">
                                    <form method="dialog">
                                        <button class="btn ghost" type="submit">Batal</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.portfolios.destroy', $portfolio) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn danger" type="submit">Ya, hapus karya</button>
                                    </form>
                                </div>
                            </div>
                        </dialog>
                    </article>
                @empty
                    <p>Belum ada portofolio.</p>
                @endforelse
            </div>
        </section>

        <section id="create-portfolio">
            <h2>Tambah portofolio</h2>
            @if ($categories->isEmpty())
                <div class="alert err">Belum ada kategori. <a href="{{ route('admin.categories.index') }}">Tambahkan kategori</a> dulu.</div>
            @else
                <form method="POST" action="{{ route('admin.portfolios.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('admin.portfolios._form', ['portfolio' => $newPortfolio])
                    <div class="actions section">
                        <button class="btn" type="submit">Simpan</button>
                    </div>
                </form>
            @endif
        </section>
    </div>

    <script>
        (function () {
            var buttons = document.querySelectorAll('[data-dialog-open]');

            Array.prototype.forEach.call(buttons, function (button) {
                button.addEventListener('click', function () {
                    var dialog = document.getElementById(button.getAttribute('data-dialog-open'));

                    if (dialog && typeof dialog.showModal === 'function') {
                        dialog.showModal();
                    }
                });
            });
        }());
    </script>
@endsection
