@extends('layouts.app')

@section('title', 'معاينة الاستيراد')

@section('content')
    <div class="mb-4">
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-right me-1"></i>العودة للقائمة
        </a>
        <h2 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>معاينة الاستيراد
        </h2>
    </div>

    <!-- File Info -->
    <div class="alert alert-info d-flex align-items-center mb-3">
        <i class="bi bi-file-earmark-excel fs-5 me-2"></i>
        <div>
            <strong>الملف:</strong> {{ $preview['filename'] }}
        </div>
    </div>

    <!-- Contact Limit Warning -->
    @if ($remainingSlots <= 0)
        <div class="alert alert-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>لا يمكن استيراد جهات اتصال!</strong>
            <p class="mb-0 mt-1">لقد وصلت للحد الأقصى ({{ number_format($contactLimit) }} جهة اتصال). يرجى حذف بعض جهات
                الاتصال أولاً قبل الاستيراد.</p>
        </div>
    @elseif($preview['summary']['valid'] > $remainingSlots)
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>تنبيه: تجاوز الحد المسموح!</strong>
            <p class="mb-0 mt-1">
                لديك <strong>{{ number_format($remainingSlots) }}</strong> جهة اتصال متبقية فقط من الحد الأقصى
                ({{ number_format($contactLimit) }}).
                <br>عدد جهات الاتصال الصالحة في الملف: <strong>{{ $preview['summary']['valid'] }}</strong>
                <br><strong>يرجى حذف بعض جهات الاتصال الحالية أولاً لتتمكن من استيراد كل الجهات.</strong>
            </p>
        </div>
    @elseif($remainingSlots <= 20)
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            متبقي <strong>{{ number_format($remainingSlots) }}</strong> جهة اتصال من الحد الأقصى
            ({{ number_format($contactLimit) }}).
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-2 mb-4">
        <div class="col-3">
            <div class="card border-0 bg-light text-center py-2 py-md-3">
                <h4 class="mb-0 fw-bold" id="totalCount">{{ $preview['summary']['total'] }}</h4>
                <small class="text-muted d-none d-sm-block">إجمالي</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card border-0 bg-success bg-opacity-10 text-center py-2 py-md-3">
                <h4 class="mb-0 fw-bold text-success" id="validCount">{{ $preview['summary']['valid'] }}</h4>
                <small class="text-success d-none d-sm-block">صالح ✓</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card border-0 bg-danger bg-opacity-10 text-center py-2 py-md-3">
                <h4 class="mb-0 fw-bold text-danger" id="errorCount">{{ $preview['summary']['errors'] }}</h4>
                <small class="text-danger d-none d-sm-block">خطأ ✗</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card border-0 bg-warning bg-opacity-10 text-center py-2 py-md-3">
                <h4 class="mb-0 fw-bold text-warning" id="duplicateCount">{{ $preview['summary']['duplicates'] }}</h4>
                <small class="text-warning d-none d-sm-block">مكرر ⚠</small>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Panel -->
    <div class="card mb-3" id="filtersCard">
        <div class="card-header bg-white py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-funnel text-primary me-2"></i>الفلاتر المتقدمة
                </h6>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                    <i class="bi bi-arrow-clockwise me-1"></i>إعادة تعيين
                </button>
            </div>
        </div>
        <div class="card-body py-3">
            <div class="row g-3">
                <!-- Phone Prefix Filter -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold mb-2">
                        <i class="bi bi-phone me-1"></i>فلتر رقم الهاتف
                    </label>
                    <div class="d-flex flex-wrap gap-2" id="phonePrefixFilters">
                        <button type="button" class="btn btn-outline-primary btn-sm prefix-btn active" data-prefix="all">
                            الكل
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm prefix-btn" data-prefix="010">
                            010
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm prefix-btn" data-prefix="011">
                            011
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm prefix-btn" data-prefix="012">
                            012
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm prefix-btn" data-prefix="015">
                            015
                        </button>
                    </div>
                </div>

                <!-- Store Filter -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold mb-2">
                        <i class="bi bi-shop me-1"></i>فلتر المتجر
                    </label>
                    <div class="dropdown w-100">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-start" type="button"
                            id="storeFilterBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span id="storeFilterLabel">الكل</span>
                        </button>
                        <div class="dropdown-menu w-100 p-2" id="storeDropdown"
                            style="max-height: 300px; overflow-y: auto;">
                            <input type="text" class="form-control form-control-sm mb-2" id="storeSearch"
                                placeholder="بحث عن متجر...">
                            <div id="storeCheckboxes">
                                <!-- Will be populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filters Summary -->
            <div class="mt-3 pt-2 border-top" id="filterSummary" style="display: none;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small">الفلاتر النشطة:</span>
                    <div id="activeFilterBadges"></div>
                    <span class="badge bg-primary" id="filteredCountBadge">0 نتيجة</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div
            class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 py-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h6 class="mb-0 fw-bold">البيانات</h6>
                <div class="btn-group btn-group-sm" id="bulkActions" style="display: none;">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="removeSelectedBtn">
                        <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">حذف</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="ignoreSelectedBtn">
                        <i class="bi bi-eye-slash"></i> <span class="d-none d-sm-inline">تجاهل</span>
                    </button>
                </div>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary active" id="showAll">الكل</button>
                <button type="button" class="btn btn-outline-success" id="showValid">صالح</button>
                <button type="button" class="btn btn-outline-danger" id="showErrors">خطأ</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="ps-3">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th class="d-none d-md-table-cell" style="width: 50px;">#</th>
                            <th class="d-none d-lg-table-cell">المتجر</th>
                            <th>الاسم</th>
                            <th class="d-none d-sm-table-cell">الهاتف</th>
                            <th style="width: 80px;">الحالة</th>
                            <th class="d-none d-xl-table-cell">الأخطاء</th>
                            <th style="width: 70px;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="previewTable">
                        <!-- Rows will be rendered dynamically by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination Controls -->
        <div
            class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 py-3 px-3">
            <div class="d-flex align-items-center gap-2 order-2 order-md-0">
                <span class="text-muted small">عرض</span>
                <select class="form-select form-select-sm" id="rowsPerPage" style="width: auto;">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
                <span class="text-muted small">صف</span>
            </div>
            <div class="d-flex align-items-center order-1 order-md-1">
                <span class="badge bg-light text-dark px-3 py-2 fs-6" id="paginationInfo">عرض 1-100 من 0</span>
            </div>
            <nav aria-label="Page navigation" class="order-0 order-md-2">
                <ul class="pagination mb-0" id="paginationControls">
                    <!-- Pagination buttons will be generated dynamically -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Import Button -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <p class="mb-0 text-center text-md-start" id="importMessage">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    سيتم استيراد <strong class="text-success"
                        id="importCount">{{ $preview['summary']['valid'] }}</strong>
                    جهة اتصال
                </p>
                <div class="d-flex gap-2 w-100 w-md-auto">
                    <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary flex-fill flex-md-grow-0">
                        إلغاء
                    </a>
                    <form action="{{ route('contacts.confirm-import') }}" method="POST" id="importForm"
                        class="flex-fill flex-md-grow-0">
                        @csrf
                        <input type="hidden" name="selected_rows" id="selectedRowsInput">
                        <button type="submit" class="btn btn-success w-100" id="importBtn">
                            <i class="bi bi-check-lg me-1"></i>استيراد
                            <span id="importBtnCount">{{ $preview['summary']['valid'] }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        tr.ignored {
            opacity: 0.5;
            background: #f8f9fa;
        }

        tr.removed {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // All row data from the server
        const rowStates = @json($preview['rows']);
        const ignoredRows = new Set();
        const removedRows = new Set();

        // Contact limit info from server
        const remainingSlots = {{ $remainingSlots }};
        const contactLimit = {{ $contactLimit }};

        // Pagination state
        let currentPage = 1;
        let rowsPerPage = 100;
        let currentFilter = 'all'; // 'all', 'valid', 'error'

        // ========== ADVANCED FILTERS STATE ==========
        let selectedPhonePrefix = 'all'; // 'all', '010', '011', '012', '015'
        let selectedStores = new Set(); // Empty = all stores
        let uniqueStores = []; // Will be populated on init

        // Initialize unique stores from data
        function initStoreFilter() {
            const stores = new Set();
            rowStates.forEach(row => {
                if (row.store_name && row.store_name.trim()) {
                    stores.add(row.store_name.trim());
                }
            });
            uniqueStores = [...stores].sort();
            renderStoreCheckboxes();
        }

        // Render store checkboxes in dropdown
        function renderStoreCheckboxes(searchQuery = '') {
            const container = document.getElementById('storeCheckboxes');
            const query = searchQuery.toLowerCase();

            let html = `
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" id="storeAll" 
                        ${selectedStores.size === 0 ? 'checked' : ''}>
                    <label class="form-check-label small" for="storeAll">
                        <strong>الكل</strong> <span class="text-muted">(${uniqueStores.length})</span>
                    </label>
                </div>
                <hr class="my-2">
            `;

            const filteredStores = query ?
                uniqueStores.filter(s => s.toLowerCase().includes(query)) :
                uniqueStores;

            if (filteredStores.length === 0) {
                html += '<p class="text-muted small mb-0">لا توجد نتائج</p>';
            } else {
                filteredStores.forEach(store => {
                    const id = `store_${store.replace(/[^a-zA-Z0-9]/g, '_')}`;
                    const checked = selectedStores.has(store) ? 'checked' : '';
                    html += `
                        <div class="form-check mb-1">
                            <input class="form-check-input store-checkbox" type="checkbox" 
                                id="${id}" value="${escapeHtml(store)}" ${checked}>
                            <label class="form-check-label small text-truncate d-block" for="${id}" 
                                style="max-width: 200px;" title="${escapeHtml(store)}">
                                ${escapeHtml(store)}
                            </label>
                        </div>
                    `;
                });
            }

            container.innerHTML = html;
            attachStoreCheckboxListeners();
        }

        // Attach store checkbox event listeners
        function attachStoreCheckboxListeners() {
            // "All" checkbox
            document.getElementById('storeAll')?.addEventListener('change', function() {
                if (this.checked) {
                    selectedStores.clear();
                    document.querySelectorAll('.store-checkbox').forEach(cb => cb.checked = false);
                }
                applyFilters();
            });

            // Individual store checkboxes
            document.querySelectorAll('.store-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    if (this.checked) {
                        selectedStores.add(this.value);
                    } else {
                        selectedStores.delete(this.value);
                    }
                    // Update "All" checkbox
                    const allCb = document.getElementById('storeAll');
                    if (allCb) allCb.checked = selectedStores.size === 0;
                    applyFilters();
                });
            });
        }

        // Update store filter button label
        function updateStoreFilterLabel() {
            const label = document.getElementById('storeFilterLabel');
            if (selectedStores.size === 0) {
                label.textContent = 'الكل';
            } else if (selectedStores.size === 1) {
                label.textContent = [...selectedStores][0];
            } else {
                label.textContent = `${selectedStores.size} متاجر`;
            }
        }

        // Apply all filters and refresh table
        function applyFilters() {
            currentPage = 1;
            updateStoreFilterLabel();
            updateFilterSummary();
            renderTable();
            updateAll();
        }

        // Update filter summary badges
        function updateFilterSummary() {
            const summary = document.getElementById('filterSummary');
            const badges = document.getElementById('activeFilterBadges');
            const countBadge = document.getElementById('filteredCountBadge');

            let badgesHtml = '';
            let hasFilters = false;

            // Phone filter badge
            if (selectedPhonePrefix !== 'all') {
                hasFilters = true;
                badgesHtml += `<span class="badge bg-primary me-1">${selectedPhonePrefix}</span>`;
            }

            // Store filter badges
            if (selectedStores.size > 0) {
                hasFilters = true;
                if (selectedStores.size <= 2) {
                    selectedStores.forEach(s => {
                        badgesHtml += `<span class="badge bg-success me-1">${escapeHtml(s)}</span>`;
                    });
                } else {
                    badgesHtml += `<span class="badge bg-success me-1">${selectedStores.size} متاجر</span>`;
                }
            }

            // Update count
            const filteredCount = getFilteredRows().length;
            countBadge.textContent = `${filteredCount} نتيجة`;

            summary.style.display = hasFilters ? 'block' : 'none';
            badges.innerHTML = badgesHtml;
        }

        // Get filtered rows based on ALL filters (status + phone + store)
        function getFilteredRows() {
            return rowStates.map((row, index) => ({
                    ...row,
                    originalIndex: index
                }))
                .filter(row => !removedRows.has(row.originalIndex))
                // Status filter (all, valid, error)
                .filter(row => {
                    if (currentFilter === 'all') return true;
                    if (currentFilter === 'valid') return row.status === 'valid' && !ignoredRows.has(row.originalIndex);
                    if (currentFilter === 'error') return row.status === 'error' || row.status === 'duplicate';
                    return true;
                })
                // Phone prefix filter
                .filter(row => {
                    if (selectedPhonePrefix === 'all') return true;
                    return row.phone && row.phone.startsWith(selectedPhonePrefix);
                })
                // Store filter
                .filter(row => {
                    if (selectedStores.size === 0) return true; // All stores
                    return selectedStores.has(row.store_name);
                });
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Render a single row
        function renderRow(row) {
            const index = row.originalIndex;
            const isIgnored = ignoredRows.has(index);
            const displayStatus = isIgnored ? 'ignored' : row.status;

            let statusBadge = '';
            if (displayStatus === 'valid') {
                statusBadge = '<span class="badge bg-success status-badge">✓</span>';
            } else if (displayStatus === 'error') {
                statusBadge = '<span class="badge bg-danger status-badge">✗</span>';
            } else if (displayStatus === 'duplicate') {
                statusBadge = '<span class="badge bg-warning text-dark status-badge">⚠</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary status-badge">-</span>';
            }

            let errorsHtml = '-';
            if (row.errors && row.errors.length > 0) {
                errorsHtml = '<ul class="mb-0 pe-3 text-danger small">' +
                    row.errors.map(e => `<li>${escapeHtml(e)}</li>`).join('') + '</ul>';
            }

            const nameHtml = !row.name ?
                '<span class="text-danger fst-italic">فارغ</span>' :
                `<div>${escapeHtml(row.name)}</div><small class="text-muted d-sm-none" dir="ltr">${escapeHtml(row.phone)}</small>`;

            const phoneHtml = !row.phone ?
                '<span class="text-danger fst-italic">فارغ</span>' :
                `<code dir="ltr">${escapeHtml(row.phone)}</code>`;

            const storeNameHtml = row.store_name ? escapeHtml(row.store_name) : '<span class="text-muted">-</span>';

            return `
                <tr data-status="${displayStatus}" data-index="${index}" 
                    data-name="${escapeHtml(row.name)}" data-phone="${escapeHtml(row.phone)}" data-store="${escapeHtml(row.store_name || '')}"
                    class="${isIgnored ? 'ignored' : ''}">
                    <td class="ps-3">
                        <input type="checkbox" class="form-check-input row-checkbox" data-index="${index}">
                    </td>
                    <td class="text-muted d-none d-md-table-cell">${row.row_number}</td>
                    <td class="d-none d-lg-table-cell">${storeNameHtml}</td>
                    <td>${nameHtml}</td>
                    <td class="d-none d-sm-table-cell">${phoneHtml}</td>
                    <td>${statusBadge}</td>
                    <td class="d-none d-xl-table-cell">${errorsHtml}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary btn-sm ignore-btn"
                                title="تجاهل" data-index="${index}">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-btn"
                                title="حذف" data-index="${index}">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        // Render current page
        function renderTable() {
            const filteredRows = getFilteredRows();
            const totalRows = filteredRows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            // Ensure current page is valid
            if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, totalRows);
            const pageRows = filteredRows.slice(startIndex, endIndex);

            // Render rows
            const tbody = document.getElementById('previewTable');
            tbody.innerHTML = pageRows.map(renderRow).join('');

            // Update pagination info
            const paginationInfo = document.getElementById('paginationInfo');
            if (totalRows > 0) {
                paginationInfo.textContent = `عرض ${startIndex + 1}-${endIndex} من ${totalRows}`;
            } else {
                paginationInfo.textContent = 'لا توجد نتائج';
            }

            // Render pagination controls
            renderPaginationControls(totalPages);

            // Re-attach event listeners
            attachRowEventListeners();
        }

        // Render pagination controls
        function renderPaginationControls(totalPages) {
            const controls = document.getElementById('paginationControls');

            if (totalPages <= 1) {
                controls.innerHTML = '';
                return;
            }

            let html = '';

            // Previous button with icon
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="السابق">
                    <i class="bi bi-chevron-right me-1"></i>السابق
                </a>
            </li>`;

            // Page numbers (show max 7 pages)
            const maxVisiblePages = 7;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link" aria-disabled="true">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}" ${i === currentPage ? 'aria-current="page"' : ''}>
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link" aria-disabled="true">...</span></li>`;
                }
                html +=
                    `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
            }

            // Next button with icon
            html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="التالي">
                    التالي<i class="bi bi-chevron-left ms-1"></i>
                </a>
            </li>`;

            controls.innerHTML = html;

            // Attach pagination click handlers
            controls.querySelectorAll('a[data-page]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.dataset.page);
                    if (page >= 1 && page <= totalPages && page !== currentPage) {
                        currentPage = page;
                        renderTable();
                        // Scroll to top of table
                        document.querySelector('.table-responsive').scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }

        // Attach event listeners to row elements
        function attachRowEventListeners() {
            // Row checkboxes
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
            });

            // Remove buttons
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    removeRow(parseInt(this.dataset.index));
                    updateAll();
                    renderTable();
                });
            });

            // Ignore buttons
            document.querySelectorAll('.ignore-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    ignoreRow(parseInt(this.dataset.index));
                    updateAll();
                    renderTable();
                });
            });
        }

        // Select All - only for visible rows on current page
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                const row = cb.closest('tr');
                if (!row.classList.contains('removed')) {
                    cb.checked = this.checked;
                }
            });
            updateBulkActions();
        });

        // Bulk remove
        document.getElementById('removeSelectedBtn').addEventListener('click', function() {
            document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                removeRow(parseInt(cb.dataset.index));
                cb.checked = false;
            });
            updateAll();
            renderTable();
        });

        // Bulk ignore
        document.getElementById('ignoreSelectedBtn').addEventListener('click', function() {
            document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                ignoreRow(parseInt(cb.dataset.index));
                cb.checked = false;
            });
            updateAll();
            renderTable();
        });

        // Rows per page change
        document.getElementById('rowsPerPage').addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            renderTable();
        });

        function removeRow(index) {
            removedRows.add(index);
            ignoredRows.delete(index);
        }

        function ignoreRow(index) {
            if (!removedRows.has(index)) {
                if (ignoredRows.has(index)) {
                    ignoredRows.delete(index);
                } else {
                    ignoredRows.add(index);
                }
            }
        }

        function updateBulkActions() {
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            document.getElementById('bulkActions').style.display = count > 0 ? 'flex' : 'none';
        }

        function updateAll() {
            // Get filtered rows based on ALL active filters (status, phone, store)
            const filteredRows = getFilteredRows();

            // Count valid rows in filtered result
            let valid = 0;
            filteredRows.forEach(row => {
                if (!ignoredRows.has(row.originalIndex) && row.status === 'valid') valid++;
            });

            // Limit to remaining slots
            const canImport = Math.min(valid, remainingSlots);
            const limitExceeded = valid > remainingSlots;

            // Update UI counts
            document.getElementById('validCount').textContent = valid;

            if (limitExceeded) {
                document.getElementById('importCount').innerHTML =
                    `<span class="text-warning">${canImport}</span> <small class="text-danger">(من ${valid})</small>`;
                document.getElementById('importBtnCount').textContent = canImport;
            } else {
                document.getElementById('importCount').textContent = canImport;
                document.getElementById('importBtnCount').textContent = canImport;
            }

            document.getElementById('importBtn').disabled = canImport === 0;

            // Build selected rows for import - ONLY filtered valid rows, LIMITED to remainingSlots
            const selectedRows = [];
            let addedCount = 0;

            for (const row of filteredRows) {
                if (addedCount >= remainingSlots) break; // Stop at limit

                if (!ignoredRows.has(row.originalIndex) && row.status === 'valid') {
                    selectedRows.push({
                        name: row.name,
                        phone: row.phone,
                        store_name: row.store_name || null
                    });
                    addedCount++;
                }
            }

            document.getElementById('selectedRowsInput').value = JSON.stringify(selectedRows);
            updateBulkActions();
        }

        // Filter buttons (status: all/valid/error)
        ['showAll', 'showValid', 'showErrors'].forEach(id => {
            document.getElementById(id).addEventListener('click', function() {
                document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = id === 'showAll' ? 'all' : (id === 'showValid' ? 'valid' : 'error');
                currentPage = 1;
                renderTable();
                updateFilterSummary();
            });
        });

        // ========== ADVANCED FILTER EVENT LISTENERS ==========

        // Phone prefix buttons
        document.querySelectorAll('.prefix-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.prefix-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedPhonePrefix = this.dataset.prefix;
                applyFilters();
            });
        });

        // Store search input
        document.getElementById('storeSearch')?.addEventListener('input', function() {
            renderStoreCheckboxes(this.value);
        });

        // Reset filters button
        document.getElementById('resetFilters')?.addEventListener('click', function() {
            // Reset phone prefix
            selectedPhonePrefix = 'all';
            document.querySelectorAll('.prefix-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('.prefix-btn[data-prefix="all"]')?.classList.add('active');

            // Reset stores
            selectedStores.clear();
            renderStoreCheckboxes();

            // Reset status filter
            currentFilter = 'all';
            document.querySelectorAll('#showAll, #showValid, #showErrors').forEach(b => b.classList.remove(
                'active'));
            document.getElementById('showAll')?.classList.add('active');

            // Reset search
            const storeSearch = document.getElementById('storeSearch');
            if (storeSearch) storeSearch.value = '';

            applyFilters();
        });

        // Initial render
        initStoreFilter(); // Initialize store filter from data
        updateAll();
        renderTable();

        // ========== IMPORT FORM - BLOCK UI ==========
        document.getElementById('importForm')?.addEventListener('submit', function() {
            const count = document.getElementById('importBtnCount')?.textContent || '0';
            showLoadingOverlay(
                'جاري استيراد ' + count + ' جهة اتصال...',
                'قد تستغرق هذه العملية بعض الوقت، يرجى الانتظار'
            );
        });
    </script>
@endpush
