@extends('layouts.app')

@section('title', 'جهات الاتصال')

@section('content')
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h2 class="mb-0 fw-bold">جهات الاتصال</h2>
            <span class="badge bg-secondary" id="totalBadge">{{ $contacts->total() }}</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-excel"></i>
                <span class="d-none d-sm-inline ms-1">استيراد</span>
            </button>
            <a href="{{ route('contacts.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-sm-inline ms-1">إضافة</span>
            </a>
        </div>
    </div>

    <!-- Search & Bulk Actions -->
    <div class="card mb-3">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                <!-- Search Input -->
                <div class="flex-grow-1">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput"
                            placeholder="بحث بالاسم أو الرقم..." autocomplete="off">
                        <button class="btn btn-outline-secondary d-none" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <!-- Bulk Actions -->
                <div class="d-flex gap-2 align-items-center" id="bulkActions" style="display: none !important;">
                    <span class="text-muted small">محدد: <strong id="selectedCount">0</strong></span>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-sm-inline ms-1">حذف</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card">
        <div class="card-body p-0">
            @if ($contacts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="contactsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="ps-3">
                                    <input type="checkbox" class="form-check-input" id="selectAll" title="تحديد الكل">
                                </th>
                                <th>الاسم</th>
                                <th class="d-none d-md-table-cell">الهاتف</th>
                                <th class="d-none d-lg-table-cell">التاريخ</th>
                                <th style="width: 90px;" class="text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="contactsBody">
                            @foreach ($contacts as $contact)
                                <tr id="contact-row-{{ $contact->id }}" data-name="{{ strtolower($contact->name) }}"
                                    data-phone="{{ $contact->phone }}">
                                    <td class="ps-3">
                                        <input type="checkbox" class="form-check-input contact-checkbox"
                                            value="{{ $contact->id }}">
                                    </td>
                                    <td>
                                        <div class="fw-semibold contact-name">{{ $contact->name }}</div>
                                        <small class="text-muted d-md-none contact-phone"
                                            dir="ltr">{{ $contact->phone }}</small>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <code class="contact-phone" dir="ltr">{{ $contact->phone }}</code>
                                    </td>
                                    <td class="text-muted small d-none d-lg-table-cell">
                                        {{ $contact->created_at->diffForHumans() }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary edit-btn"
                                                data-id="{{ $contact->id }}" data-name="{{ $contact->name }}"
                                                data-phone="{{ $contact->phone }}" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger delete-btn"
                                                data-id="{{ $contact->id }}" data-name="{{ $contact->name }}"
                                                title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- No Results Message -->
                <div class="text-center py-4 d-none" id="noResults">
                    <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">لا توجد نتائج مطابقة</p>
                </div>

                <!-- Pagination -->
                @if ($contacts->hasPages())
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center p-3 border-top gap-3"
                        id="pagination">
                        <div class="text-muted small d-none d-sm-block">
                            عرض {{ $contacts->firstItem() }}-{{ $contacts->lastItem() }} من {{ $contacts->total() }} جهة
                            اتصال
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $contacts->links() }}
                        </nav>
                    </div>
                @endif
            @else
                <div class="text-center py-5 px-3">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 70px; height: 70px;">
                        <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="fw-bold">لا توجد جهات اتصال</h6>
                    <p class="text-muted small mb-3">أضف جهات اتصال أو استوردها من Excel</p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="bi bi-file-earmark-excel me-1"></i>استيراد
                        </button>
                        <a href="{{ route('contacts.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>إضافة جهة
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('contacts.preview') }}" method="POST" enctype="multipart/form-data"
                    id="importForm">
                    @csrf
                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-file-earmark-excel text-success me-2"></i>استيراد جهات الاتصال
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            الملف يجب أن يحتوي على: <strong>name</strong> و <strong>phone</strong>
                        </div>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-eye me-1"></i>معاينة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-pencil text-primary me-2"></i>تعديل جهة الاتصال
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editContactId">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">الاسم</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-semibold">رقم الهاتف</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone" dir="ltr"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i>حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-trash text-danger" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-0" id="deleteMessage">حذف جهة الاتصال؟</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-trash text-danger" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-0">حذف <strong id="bulkDeleteCount">0</strong> جهة اتصال؟</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <form id="bulkDeleteForm" action="{{ route('contacts.bulk-delete') }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="ids" id="bulkDeleteIds">
                        <button type="submit" class="btn btn-danger btn-sm">حذف الكل</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));

        const searchInput = document.getElementById('searchInput');
        const clearSearchBtn = document.getElementById('clearSearch');
        const contactsBody = document.getElementById('contactsBody');
        const noResults = document.getElementById('noResults');
        const pagination = document.getElementById('pagination');

        // ========== SEARCH ==========
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            clearSearchBtn.classList.toggle('d-none', !query);
            filterContacts(query);
        });

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.classList.add('d-none');
            filterContacts('');
            searchInput.focus();
        });

        function filterContacts(query) {
            const rows = contactsBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.dataset.name || '';
                const phone = row.dataset.phone || '';
                const matches = name.includes(query) || phone.includes(query);
                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            noResults.classList.toggle('d-none', visibleCount > 0);
            if (pagination) pagination.style.display = query ? 'none' : '';

            // Update select all for visible only
            updateBulkActions();
        }

        // ========== SELECT ALL ==========
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const visibleCheckboxes = [...document.querySelectorAll('.contact-checkbox')].filter(
                cb => cb.closest('tr').style.display !== 'none'
            );
            visibleCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        document.querySelectorAll('.contact-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const visibleCheckboxes = [...document.querySelectorAll('.contact-checkbox')].filter(
                cb => cb.closest('tr').style.display !== 'none'
            );
            const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
            document.getElementById('selectedCount').textContent = checkedCount;
            document.getElementById('bulkActions').style.display = checkedCount > 0 ? 'flex' : 'none';

            // Update select all state
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.checked = visibleCheckboxes.length > 0 && checkedCount === visibleCheckboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < visibleCheckboxes.length;
            }
        }

        // ========== EDIT ==========
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editContactId').value = this.dataset.id;
                document.getElementById('editName').value = this.dataset.name;
                document.getElementById('editPhone').value = this.dataset.phone;
                document.getElementById('editForm').action = `/contacts/${this.dataset.id}`;
                editModal.show();
            });
        });

        document.getElementById('editForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editContactId').value;
            fetch(`/contacts/${id}`, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`contact-row-${id}`);
                        row.querySelectorAll('.contact-name').forEach(el => el.textContent = data.contact.name);
                        row.querySelectorAll('.contact-phone').forEach(el => el.textContent = data.contact
                            .phone);
                        row.dataset.name = data.contact.name.toLowerCase();
                        row.dataset.phone = data.contact.phone;
                        row.querySelector('.edit-btn').dataset.name = data.contact.name;
                        row.querySelector('.edit-btn').dataset.phone = data.contact.phone;
                        editModal.hide();
                        showToast('success', 'تم التحديث');
                    } else {
                        showToast('error', data.message || 'خطأ');
                    }
                });
        });

        // ========== DELETE ==========
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('deleteMessage').innerHTML =
                    `حذف <strong>${this.dataset.name}</strong>؟`;
                document.getElementById('deleteForm').action = `/contacts/${this.dataset.id}`;
                deleteModal.show();
            });
        });

        // ========== BULK DELETE ==========
        document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
            const ids = [...document.querySelectorAll('.contact-checkbox:checked')].map(cb => cb.value);
            document.getElementById('bulkDeleteCount').textContent = ids.length;
            document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
            bulkDeleteModal.show();
        });

        // ========== TOAST ==========
        function showToast(type, message) {
            const alert = document.createElement('div');
            alert.className =
                `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed shadow-sm`;
            alert.style.cssText = 'top: 70px; left: 1rem; right: 1rem; z-index: 9999; max-width: 300px;';
            alert.innerHTML =
                `<i class="bi bi-${type === 'success' ? 'check-circle' : 'x-circle'} me-1"></i>${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 2500);
        }

        // ========== IMPORT FORM - BLOCK UI ==========
        document.getElementById('importForm')?.addEventListener('submit', function() {
            showLoadingOverlay('جاري تحميل الملف...', 'يرجى الانتظار حتى يتم قراءة البيانات');
        });
    </script>
@endpush
