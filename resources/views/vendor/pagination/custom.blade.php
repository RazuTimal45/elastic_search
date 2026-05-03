@if ($paginator->hasPages())
    <nav class="custom-pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="disabled">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="separator">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
        @else
            <span class="disabled">&raquo;</span>
        @endif
    </nav>
@endif

<style>
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 50px;
        font-family: 'Inter', sans-serif;
    }

    .custom-pagination a,
    .custom-pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border-radius: 12px;
        background: #1e293b;
        border: 1px solid #334155;
        color: #f8fafc;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .custom-pagination a:hover {
        background: #334155;
        border-color: #6366f1;
        transform: translateY(-2px);
        color: #6366f1;
    }

    .custom-pagination .active {
        background: #6366f1 !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .custom-pagination .disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .custom-pagination .separator {
        border: none;
        background: transparent;
        min-width: 20px;
        color: #94a3b8;
    }
</style>