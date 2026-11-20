@if ($paginator->hasPages())
    <nav class="pager" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="btn ghost sm off">‹ Sebelumnya</span>
        @else
            <a class="btn ghost sm" href="{{ $paginator->previousPageUrl() }}">‹ Sebelumnya</a>
        @endif

        <span>Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="btn ghost sm" href="{{ $paginator->nextPageUrl() }}">Berikutnya ›</a>
        @else
            <span class="btn ghost sm off">Berikutnya ›</span>
        @endif
    </nav>
@endif
