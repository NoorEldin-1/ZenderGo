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
                            <th>الاسم</th>
                            <th class="d-none d-sm-table-cell">الهاتف</th>
                            <th style="width: 80px;">الحالة</th>
                            <th class="d-none d-lg-table-cell">الأخطاء</th>
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
                    سيتم استيراد <strong class="text-success" id="importCount">{{ $preview['summary']['valid'] }}</strong>
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

        // Pagination state
        let currentPage = 1;
        let rowsPerPage = 100;
        let currentFilter = 'all'; // 'all', 'valid', 'error'

        // Get filtered rows based on current filter (excluding removed rows)
        function getFilteredRows() {
            return rowStates.map((row, index) => ({
                    ...row,
                    originalIndex: index
                }))
                .filter(row => !removedRows.has(row.originalIndex))
                .filter(row => {
                    if (currentFilter === 'all') return true;
                    if (currentFilter === 'valid') return row.status === 'valid' && !ignoredRows.has(row.originalIndex);
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

            return `
                <tr data-status="${displayStatus}" data-index="${index}" 
                    data-name="${escapeHtml(row.name)}" data-phone="${escapeHtml(row.phone)}"
                    class="${isIgnored ? 'ignored' : ''}">
                    <td class="ps-3">
                        <input type="checkbox" class="form-check-input row-checkbox" data-index="${index}">
                    </td>
                    <td class="text-muted d-none d-md-table-cell">${row.row_number}</td>
                    <td>${nameHtml}</td>
                    <td class="d-none d-sm-table-cell">${phoneHtml}</td>
                    <td>${statusBadge}</td>
                    <td class="d-none d-lg-table-cell">${errorsHtml}</td>
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
            let valid = 0;
            rowStates.forEach((row, i) => {
                if (!removedRows.has(i) && !ignoredRows.has(i) && row.status === 'valid') valid++;
            });
            document.getElementById('validCount').textContent = valid;
            document.getElementById('importCount').textContent = valid;
            document.getElementById('importBtnCount').textContent = valid;
            document.getElementById('importBtn').disabled = valid === 0;

            const selectedRows = [];
            rowStates.forEach((row, i) => {
                if (!removedRows.has(i) && !ignoredRows.has(i) && row.status === 'valid') {
                    selectedRows.push({
                        name: row.name,
                        phone: row.phone
                    });
                }
            });
            document.getElementById('selectedRowsInput').value = JSON.stringify(selectedRows);
            updateBulkActions();
        }

        // Filter buttons
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
