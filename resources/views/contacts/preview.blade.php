@extends('layouts.app')

@section('title', 'معاينة الاستيراد')

@section('content')
    <div class="mb-4">
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-right me-1"></i>العودة للقائمة
        </a>
        <a href="{{ route('contacts.remap') }}" class="btn btn-outline-primary btn-sm mb-3 ms-2">
            <i class="bi bi-gear me-1"></i>تعديل الأعمدة
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
    <!-- Advanced Filters Removed -->

    <!-- Data Table -->
    <div class="card">
        <div
            class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 py-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h6 class="mb-0 fw-bold">البيانات</h6>
                <!-- Selection Counter -->
                <span class="badge bg-primary" id="selectionCounter" style="display: none;">
                    <i class="bi bi-check2-square me-1"></i><span id="selectedCount">0</span> محدد
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn" style="display: none;">
                    <i class="bi bi-x-circle me-1"></i>إلغاء التحديد
                </button>
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
                            <!-- Store Column Removed -->
                            <th>الاسم</th>
                            <th class="d-none d-sm-table-cell">الهاتف</th>
                            <th style="width: 80px;">الحالة</th>
                            <th class="d-none d-xl-table-cell">الأخطاء</th>
                        </tr>
                    </thead>
                    <tbody id="previewTable">
                        <!-- Rows will be rendered dynamically by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination Controls -->
        <div class="card-footer bg-white d-flex justify-content-center py-3 px-3">
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0" id="paginationControls">
                    <!-- Pagination buttons will be generated dynamically -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Import Button -->
    <div class="card mt-3">
        <div class="card-body py-3">
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary flex-fill flex-sm-grow-0">
                    إلغاء
                </a>
                <button type="button" class="btn btn-outline-danger flex-fill flex-sm-grow-0" id="deselectAllBtnBottom"
                    style="display: none;">
                    <i class="bi bi-x-circle me-1"></i>إلغاء التحديد
                </button>
                <form action="{{ route('contacts.confirm-import') }}" method="POST" id="importForm" class="flex-fill">
                    @csrf
                    <input type="hidden" name="selected_rows" id="selectedRowsInput">
                    <button type="submit" class="btn btn-success w-100" id="importBtn">
                        <i class="bi bi-check-lg me-1"></i>
                        <span id="importBtnText">استيراد الكل</span>
                        (<span id="importBtnCount">{{ $preview['summary']['valid'] }}</span>)
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        tr.removed {
            display: none !important;
        }

        /* Force dark mode colors for stats */
        [data-bs-theme="dark"] .text-success,
        [data-bs-theme="dark"] #validCount {
            color: #20c997 !important;
        }

        [data-bs-theme="dark"] .text-danger,
        [data-bs-theme="dark"] #errorCount {
            color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .text-warning,
        [data-bs-theme="dark"] #duplicateCount {
            color: #ffc107 !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // All row data from the server
        const rowStates = @json($preview['rows']);
        const selectedIndices = new Set(); // For manual selection (cross-page persistent)

        // Contact limit info from server
        const remainingSlots = {{ $remainingSlots }};
        const contactLimit = {{ $contactLimit }};

        // Pagination state
        let currentPage = 1;
        let rowsPerPage = 100;
        let currentFilter = 'all'; // 'all', 'valid', 'error'

        // Apply all filters and refresh table
        function applyFilters() {
            currentPage = 1;
            renderTable();
            renderTable();
            updateAll();
        }



        // Get filtered rows based on ALL filters (status)
        function getFilteredRows() {
            return rowStates.map((row, index) => ({
                    ...row,
                    originalIndex: index
                }))
                // Status filter (all, valid, error)
                .filter(row => {
                    if (currentFilter === 'all') return true;
                    if (currentFilter === 'valid') return row.status === 'valid';
                    if (currentFilter === 'error') return row.status === 'error' || row.status === 'duplicate';
                    return true;
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
            const displayStatus = row.status;

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



            return `
                 <tr data-status="${displayStatus}" data-index="${index}" 
                    data-name="${escapeHtml(row.name)}" data-phone="${escapeHtml(row.phone)}">
                    <td class="ps-3">
                        <input type="checkbox" class="form-check-input row-checkbox" data-index="${index}" ${selectedIndices.has(index) ? 'checked' : ''}>
                    </td>
                    <td class="text-muted d-none d-md-table-cell">${row.row_number}</td>
                    <td>${nameHtml}</td>
                    <td class="d-none d-sm-table-cell">${phoneHtml}</td>
                    <td>${statusBadge}</td>
                    <td class="d-none d-xl-table-cell">${errorsHtml}</td>
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
            // Row checkboxes - track selection globally
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const index = parseInt(this.dataset.index);
                    if (this.checked) {
                        selectedIndices.add(index);
                    } else {
                        selectedIndices.delete(index);
                    }
                    updateSelectionUI();
                    updateAll();
                });
            });
        }

        // Select All - only for visible rows on current page
        document.getElementById('selectAll').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                const index = parseInt(cb.dataset.index);
                cb.checked = isChecked;
                if (isChecked) {
                    selectedIndices.add(index);
                } else {
                    selectedIndices.delete(index);
                }
            });
            updateSelectionUI();
            updateAll();
        });

        // Deselect All button (top)
        document.getElementById('deselectAllBtn').addEventListener('click', deselectAllHandler);
        // Deselect All button (bottom)
        document.getElementById('deselectAllBtnBottom').addEventListener('click', deselectAllHandler);

        function deselectAllHandler() {
            selectedIndices.clear();
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('selectAll').checked = false;
            updateSelectionUI();
            updateAll();
        }

        // Update selection counter and button visibility
        function updateSelectionUI() {
            const count = selectedIndices.size;
            const counter = document.getElementById('selectionCounter');
            const deselectBtn = document.getElementById('deselectAllBtn');
            const deselectBtnBottom = document.getElementById('deselectAllBtnBottom');
            const selectedCountEl = document.getElementById('selectedCount');

            if (count > 0) {
                counter.style.display = 'inline-flex';
                deselectBtn.style.display = 'inline-block';
                deselectBtnBottom.style.display = 'inline-block';
                selectedCountEl.textContent = count;
            } else {
                counter.style.display = 'none';
                deselectBtn.style.display = 'none';
                deselectBtnBottom.style.display = 'none';
            }
        }

        function updateAll() {
            // Get filtered rows based on ALL active filters (status, store)
            const filteredRows = getFilteredRows();

            // Count valid rows in filtered result
            let validCount = 0;
            filteredRows.forEach(row => {
                if (row.status === 'valid') validCount++;
            });

            // Update UI counts
            document.getElementById('validCount').textContent = validCount;

            // Smart Import Logic
            const hasSelection = selectedIndices.size > 0;
            let rowsToImport = [];
            let importCount = 0;

            if (hasSelection) {
                // Import ALL selected VALID rows (global, regardless of current filter)
                rowStates.forEach((row, index) => {
                    if (selectedIndices.has(index) && row.status === 'valid') {
                        if (importCount < remainingSlots) {
                            rowsToImport.push({
                                name: row.name,
                                phone: row.phone
                            });
                            importCount++;
                        }
                    }
                });
                document.getElementById('importBtnText').textContent = 'استيراد المحدد';
            } else {
                // Import all valid rows from filtered result
                filteredRows.forEach(row => {
                    if (row.status === 'valid') {
                        if (importCount < remainingSlots) {
                            rowsToImport.push({
                                name: row.name,
                                phone: row.phone
                            });
                            importCount++;
                        }
                    }
                });
                document.getElementById('importBtnText').textContent = 'استيراد الكل';
            }

            // Update button count and disabled state
            document.getElementById('importBtnCount').textContent = importCount;
            document.getElementById('importBtn').disabled = importCount === 0;

            // Update hidden input for form submission
            document.getElementById('selectedRowsInput').value = JSON.stringify(rowsToImport);
        }

        // Filter buttons (status: all/valid/error)
        ['showAll', 'showValid', 'showErrors'].forEach(id => {
            document.getElementById(id).addEventListener('click', function() {
                document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = id === 'showAll' ? 'all' : (id === 'showValid' ? 'valid' : 'error');
                currentPage = 1;
                renderTable();
            });
        });



        // Initial render

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
