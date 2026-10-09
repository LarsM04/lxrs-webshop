{{-- Eigen paginering. Die van Laravel zelf is gemaakt voor Tailwind, en dat
     gebruikt dit project niet. Vorige, waar je bent, volgende: meer is er bij
     een paar pagina's niet nodig. --}}
@if ($paginator->hasPages())
    <nav class="paginering" aria-label="Paginering">
        @if ($paginator->onFirstPage())
            <span class="knop knop--rand" aria-disabled="true">&larr; Vorige</span>
        @else
            <a class="knop knop--rand" href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; Vorige</a>
        @endif

        <span class="paginering__stand">Pagina {{ $paginator->currentPage() }} van {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="knop knop--rand" href="{{ $paginator->nextPageUrl() }}" rel="next">Volgende &rarr;</a>
        @else
            <span class="knop knop--rand" aria-disabled="true">Volgende &rarr;</span>
        @endif
    </nav>
@endif
