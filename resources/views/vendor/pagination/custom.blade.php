@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination Navigation">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true"><span>&lsaquo;</span></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span aria-disabled="true"><span>{{ $element }}</span></span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"><span>{{ $page }}</span></span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
        @else
            <span aria-disabled="true"><span>&rsaquo;</span></span>
        @endif
    </nav>
@endif
