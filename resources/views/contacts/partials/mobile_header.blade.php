{{-- Mobile Header - Only visible on screens < 768px --}}
<div class="mobile-header-wrapper">
    {{-- Top Row: Title + Badge --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">جهات الاتصال</h4>
            <span class="badge bg-primary rounded-pill" id="mobileTotalBadge">{{ $contacts->total() }}</span>
        </div>
    </div>

    {{-- Action Buttons Row --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('contacts.create') }}"
            class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm px-3"
            style="height: 38px; border-radius: 10px;">
            <i class="bi bi-plus-lg"></i>
            <span>إضافة جهة</span>
        </a>
        <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 shadow-sm px-3"
            data-bs-toggle="modal" data-bs-target="#importModal" style="height: 38px; border-radius: 10px;">
            <i class="bi bi-file-earmark-arrow-up"></i>
            <span>استيراد ملف</span>
        </button>
        <a href="{{ route('shares.index') }}"
            class="btn btn-outline-info btn-sm d-flex align-items-center gap-1 shadow-sm px-3 position-relative"
            style="height: 38px; border-radius: 10px;">
            <i class="bi bi-share"></i>
            <span>الطلبات</span>
            @if (Auth::user()->pending_share_requests_count > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size: 0.6rem;">
                    {{ Auth::user()->pending_share_requests_count }}
                </span>
            @endif
        </a>
    </div>

    {{-- Compact Stats Widget --}}
    <div class="stats-widget card border-0 shadow mb-3 overflow-hidden">
        <div class="card-body p-0">
            {{-- Top Gradient Bar --}}
            <div style="height: 3px; background: linear-gradient(90deg, #20c997, #ffc107, #dc3545);"></div>

            <div class="p-3 d-flex align-items-center justify-content-between">
                {{-- Left: Stats Info --}}
                <div class="d-flex align-items-center gap-3">
                    {{-- Circular Progress --}}
                    <div class="circular-progress"
                        style="--progress: {{ min(100, $usagePercent) }}; --color: {{ $usagePercent > 90 ? '#dc3545' : ($usagePercent > 70 ? '#ffc107' : '#20c997') }};">
                        <div class="progress-value">
                            <span class="fs-6 fw-bold">{{ $usagePercent }}%</span>
                        </div>
                    </div>

                    {{-- Text Info --}}
                    <div>
                        <div class="text-muted small">الاستخدام</div>
                        <div class="fw-bold">
                            <span class="text-body">{{ number_format($contactCount) }}</span>
                            <span class="text-muted">/</span>
                            <span class="text-muted">{{ number_format($contactLimit) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Status Badge --}}
                <div>
                    @if ($remainingSlots <= 0)
                        <span class="badge bg-danger px-3 py-2">
                            <i class="bi bi-x-circle me-1"></i>ممتلئ
                        </span>
                    @elseif($remainingSlots <= 10)
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $remainingSlots }} متبقي
                        </span>
                    @else
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i>{{ $remainingSlots }} متبقي
                        </span>
                    @endif
                </div>
            </div>

            {{-- Warning Alert (if nearly full) --}}
            @if ($usagePercent > 90)
                <div
                    class="bg-danger bg-opacity-10 border-top border-danger border-opacity-25 px-3 py-2 d-flex align-items-center gap-2 small">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                    <span class="text-danger">قاربت على الحد الأقصى!</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Search Input --}}
    <div class="mb-3">
        <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <span class="input-group-text bg-body border-0 ps-3">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" class="form-control border-0 py-2" id="mobileSearchInput"
                placeholder="بحث بالاسم أو الرقم..." autocomplete="off" value="{{ request('q') }}">
            <button class="btn btn-light border-0 px-3 {{ request('q') ? '' : 'd-none' }}" type="button"
                id="mobileClearSearch">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    {{-- Filter Chips (Horizontal Scroll) --}}
    <div class="filter-chips-container mb-3">
        <div class="d-flex gap-2 overflow-auto pb-2 no-scrollbar">
            <button type="button"
                class="filter-chip btn rounded-pill px-3 py-2 text-nowrap {{ request('contact_filter') == '' ? 'active' : '' }}"
                data-filter="">
                الكل
            </button>
            <button type="button"
                class="filter-chip btn rounded-pill px-3 py-2 text-nowrap {{ request('contact_filter') == 'featured' ? 'active' : '' }}"
                data-filter="featured">
                <i class="bi bi-star-fill text-warning me-1"></i>المميزة
            </button>
            <button type="button"
                class="filter-chip btn rounded-pill px-3 py-2 text-nowrap {{ request('contact_filter') == 'never' ? 'active' : '' }}"
                data-filter="never">
                <i class="bi bi-clock-history me-1"></i>لم يتم التواصل
            </button>
            <button type="button"
                class="filter-chip btn rounded-pill px-3 py-2 text-nowrap {{ request('contact_filter') == 'range' ? 'active' : '' }}"
                data-filter="range">
                <i class="bi bi-calendar-range me-1"></i>فترة محددة
            </button>
        </div>
    </div>

    {{-- Date Range Picker (Hidden by default, shown when "range" filter is selected) --}}
    <div id="mobileDateRangeContainer" class="mb-3 {{ request('contact_filter') == 'range' ? '' : 'd-none' }}">
        <input type="text" class="form-control shadow-sm flatpickr-input" id="mobileDateRangePicker"
            placeholder="اختر الفترة..." style="border-radius: 12px;" readonly
            value="{{ request('date_from') && request('date_to') ? request('date_from') . ' إلى ' . request('date_to') : '' }}"
            data-default-from="{{ request('date_from') }}" data-default-to="{{ request('date_to') }}">
    </div>
</div>
