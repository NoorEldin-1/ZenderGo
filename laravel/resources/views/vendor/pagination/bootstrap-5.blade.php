@if ($paginator->hasPages())
    <nav class="d-flex justify-content-center" role="navigation" aria-label="التنقل بين الصفحات">
        {{-- Mobile View (Simple) --}}
        <div class="d-flex d-sm-none">
            <ul class="pagination mb-0">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">
                            <i class="bi bi-chevron-right me-1"></i>السابق
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="bi bi-chevron-right me-1"></i>السابق
                        </a>
                    </li>
                @endif

                {{-- Current Page Indicator --}}
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $paginator->currentPage() }}</span>
                </li>

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            التالي<i class="bi bi-chevron-left ms-1"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">
                            التالي<i class="bi bi-chevron-left ms-1"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Desktop View (Full) --}}
        <div class="d-none d-sm-flex">
            <ul class="pagination mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="السابق">
                        <span class="page-link" aria-hidden="true">
                            <i class="bi bi-chevron-right me-1"></i>السابق
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                            aria-label="السابق">
                            <i class="bi bi-chevron-right me-1"></i>السابق
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="التالي">
                            التالي<i class="bi bi-chevron-left ms-1"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="التالي">
                        <span class="page-link" aria-hidden="true">
                            التالي<i class="bi bi-chevron-left ms-1"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
