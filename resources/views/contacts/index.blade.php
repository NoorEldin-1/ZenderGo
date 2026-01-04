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
            <a href="{{ route('shares.index') }}" class="btn btn-outline-info btn-sm position-relative" title="طلبات المشاركة">
                <i class="bi bi-share"></i>
                @if (Auth::user()->pending_share_requests_count > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ Auth::user()->pending_share_requests_count }}
                    </span>
                @endif
            </a>
            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                data-bs-target="#importModal">
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
                <div class="d-flex gap-2 align-items-center" id="bulkActions" style="display: none;">
                    <span class="text-muted small">
                        محدد: <strong id="selectedCount">0</strong>
                        <span id="crossPageIndicator" class="badge bg-info ms-1 d-none" title="إجمالي المحدد من كل الصفحات">
                            الكل: <span id="totalSelectedCount">0</span>
                        </span>
                    </span>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-sm-inline ms-1">حذف</span>
                    </button>
                    <button type="button" class="btn btn-info btn-sm" id="shareBtn" data-bs-toggle="modal"
                        data-bs-target="#shareModal">
                        <i class="bi bi-share"></i>
                        <span class="d-none d-sm-inline ms-1">مشاركة</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSelectionBtn"
                        title="إلغاء كل التحديدات">
                        <i class="bi bi-x-lg"></i>
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
                                        @if (!empty($contact->share_history))
                                            <small class="d-block mt-1">
                                                <i class="bi bi-share-fill text-info me-1" style="font-size: 0.7rem;"></i>
                                                @foreach ($contact->share_history as $share)
                                                    <span
                                                        class="badge {{ $share->status === 'accepted' ? 'bg-success' : ($share->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}"
                                                        style="font-size: 0.65rem;" dir="ltr"
                                                        title="{{ $share->status === 'accepted' ? 'مقبول' : ($share->status === 'pending' ? 'قيد الانتظار' : 'مرفوض') }}">
                                                        {{ $share->shared_with }}
                                                    </span>
                                                @endforeach
                                            </small>
                                        @endif
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
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
                        <!-- Instructions -->
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>تنبيه:</strong> تأكد من أن ملف Excel يحتوي على الأعمدة التالية في الصف الأول:
                        </div>

                        <!-- Required Columns Table -->
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm mb-0 small">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-center">Store_Name</th>
                                        <th class="text-center">Cust_FullName</th>
                                        <th class="text-center">Cust_Mobile</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-muted text-center">
                                        <td>اسم المتجر (اختياري)</td>
                                        <td>اسم العميل <span class="text-danger">*</span></td>
                                        <td>01012345678 <span class="text-danger">*</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info py-2 mb-3 small">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>ملاحظات:</strong>
                            <ul class="mb-0 mt-1">
                                <li>الأعمدة المطلوبة: <code>Cust_FullName</code> و <code>Cust_Mobile</code></li>
                                <li>عمود <code>Store_Name</code> اختياري</li>
                                <li>أسماء الأعمدة البديلة مدعومة: <code>name</code>, <code>phone</code>, <code>mobile</code>
                                </li>
                                <li>سيتم إضافة الصفر تلقائياً لأرقام الهواتف الناقصة</li>
                            </ul>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-semibold">اختر ملف Excel أو CSV:</label>
                            <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">الحد الأقصى: 10MB | الصيغ المدعومة: xlsx, xls, csv</div>
                        </div>
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

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('shares.store') }}" method="POST" id="shareForm">
                    @csrf
                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-share text-info me-2"></i>مشاركة جهات الاتصال
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 mb-3 small">
                            <i class="bi bi-info-circle me-1"></i>
                            سيتم إرسال طلب مشاركة للمستخدم. عند الموافقة، ستُضاف جهات الاتصال إلى حسابه.
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">رقم هاتف المستلم</label>
                            <input type="tel" class="form-control" name="phone" id="sharePhone"
                                placeholder="01012345678" dir="ltr" required>
                            <div class="form-text">أدخل رقم هاتف المستخدم المسجل في النظام</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">رسالة (اختياري)</label>
                            <textarea class="form-control" name="message" rows="2" placeholder="أضف رسالة للمستلم..." maxlength="500"></textarea>
                        </div>

                        <div class="bg-light rounded p-2">
                            <small class="text-muted">
                                <i class="bi bi-people me-1"></i>
                                سيتم مشاركة <strong id="shareCount">0</strong> جهة اتصال
                            </small>
                        </div>

                        <!-- Hidden inputs for selected contacts -->
                        <div id="shareContactsInputs"></div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="bi bi-send me-1"></i>إرسال الطلب
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ========== SELECTION MANAGER (Singleton Pattern with SessionStorage) ==========
        const SelectionManager = {
            STORAGE_KEY: 'contacts_selection',
            _cache: null, // In-memory cache for performance

            // Get current selection as Set for O(1) lookup
            getSelection() {
                if (this._cache) return this._cache;
                try {
                    const stored = sessionStorage.getItem(this.STORAGE_KEY);
                    this._cache = stored ? new Set(JSON.parse(stored)) : new Set();
                } catch (e) {
                    this._cache = new Set();
                }
                return this._cache;
            },

            // Save selection Set to storage
            saveSelection() {
                try {
                    sessionStorage.setItem(this.STORAGE_KEY, JSON.stringify([...this._cache]));
                } catch (e) {
                    console.warn('Failed to save selection to sessionStorage');
                }
            },

            // Add single ID
            add(id) {
                this.getSelection().add(String(id));
                this.saveSelection();
            },

            // Remove single ID
            remove(id) {
                this.getSelection().delete(String(id));
                this.saveSelection();
            },

            // Toggle ID based on checkbox state
            toggle(id, isSelected) {
                if (isSelected) {
                    this.add(id);
                } else {
                    this.remove(id);
                }
            },

            // Bulk add multiple IDs
            addMany(ids) {
                const selection = this.getSelection();
                ids.forEach(id => selection.add(String(id)));
                this.saveSelection();
            },

            // Bulk remove multiple IDs
            removeMany(ids) {
                const selection = this.getSelection();
                ids.forEach(id => selection.delete(String(id)));
                this.saveSelection();
            },

            // Check if ID is selected - O(1) lookup
            has(id) {
                return this.getSelection().has(String(id));
            },

            // Get total count
            count() {
                return this.getSelection().size;
            },

            // Get all IDs as array
            getAll() {
                return [...this.getSelection()];
            },

            // Clear all selections
            clear() {
                this._cache = new Set();
                sessionStorage.removeItem(this.STORAGE_KEY);
            }
        };

        // ========== DOM REFERENCES ==========
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));

        const searchInput = document.getElementById('searchInput');
        const clearSearchBtn = document.getElementById('clearSearch');
        const contactsBody = document.getElementById('contactsBody');
        const noResults = document.getElementById('noResults');
        const pagination = document.getElementById('pagination');

        // UI Elements for selection display
        const selectedCountEl = document.getElementById('selectedCount');
        const totalSelectedCountEl = document.getElementById('totalSelectedCount');
        const crossPageIndicator = document.getElementById('crossPageIndicator');
        const bulkActionsEl = document.getElementById('bulkActions');
        const selectAllEl = document.getElementById('selectAll');

        // ========== INITIALIZATION - Restore Selection State ==========
        (function initializeSelectionState() {
            // Restore checkbox states from SelectionManager
            document.querySelectorAll('.contact-checkbox').forEach(cb => {
                if (SelectionManager.has(cb.value)) {
                    cb.checked = true;
                }
            });
            // Setup row click handlers for initial page load
            setupRowClickHandlers();
            // Update UI to reflect restored state
            updateBulkActions();
        })();

        // Setup row click handlers (for initial load)
        function setupRowClickHandlers() {
            document.querySelectorAll('#contactsBody tr').forEach(row => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function(e) {
                    // Don't toggle if clicking on buttons, links, or the checkbox itself
                    if (e.target.closest('button, a, input[type="checkbox"]')) return;

                    const checkbox = this.querySelector('.contact-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
            });
        }

        // ========== SEARCH (Server-Side for All Pages) ==========
        let searchDebounceTimer = null;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearSearchBtn.classList.toggle('d-none', !query);

            // Debounce: wait 300ms after user stops typing
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.classList.add('d-none');
            performSearch('');
            searchInput.focus();
        });

        function performSearch(query) {
            // Show loading state
            contactsBody.innerHTML =
                '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-success me-2"></div>جاري البحث...</td></tr>';
            noResults.classList.add('d-none');

            // Build URL with search query
            const url = new URL(window.location.href);
            url.searchParams.set('q', query);
            url.searchParams.delete('page'); // Reset to page 1 on search

            fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    renderContacts(data.contacts);
                    renderPagination(data.pagination, query);
                    updateBulkActions();

                    // Update total badge
                    document.getElementById('totalBadge').textContent = data.pagination.total;
                })
                .catch(err => {
                    console.error('Search error:', err);
                    contactsBody.innerHTML =
                        '<tr><td colspan="5" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>حدث خطأ أثناء البحث</td></tr>';
                });
        }

        function renderContacts(contacts) {
            if (contacts.length === 0) {
                contactsBody.innerHTML = '';
                noResults.classList.remove('d-none');
                return;
            }

            noResults.classList.add('d-none');

            contactsBody.innerHTML = contacts.map(contact => {
                const shareHistoryHtml = contact.share_history && contact.share_history.length > 0 ?
                    `<small class="d-block mt-1">
                        <i class="bi bi-share-fill text-info me-1" style="font-size: 0.7rem;"></i>
                        ${contact.share_history.map(s => `
                                        <span class="badge ${s.status === 'accepted' ? 'bg-success' : (s.status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary')}" 
                                              style="font-size: 0.65rem;" dir="ltr"
                                              title="${s.status === 'accepted' ? 'مقبول' : (s.status === 'pending' ? 'قيد الانتظار' : 'مرفوض')}">
                                            ${s.shared_with}
                                        </span>
                                    `).join('')}
                       </small>` :
                    '';

                return `
                <tr id="contact-row-${contact.id}" data-name="${escapeHtml(contact.name.toLowerCase())}" data-phone="${escapeHtml(contact.phone)}">
                    <td class="ps-3">
                        <input type="checkbox" class="form-check-input contact-checkbox" value="${contact.id}"
                            ${SelectionManager.has(String(contact.id)) ? 'checked' : ''}>
                    </td>
                    <td>
                        <div class="fw-semibold contact-name">${escapeHtml(contact.name)}</div>
                        ${shareHistoryHtml}
                    </td>
                    <td class="d-none d-md-table-cell">
                        <code class="contact-phone" dir="ltr">${escapeHtml(contact.phone)}</code>
                    </td>
                    <td class="text-muted small d-none d-lg-table-cell">${formatDate(contact.created_at)}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary edit-btn"
                                data-id="${contact.id}" data-name="${escapeHtml(contact.name)}" data-phone="${escapeHtml(contact.phone)}" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger delete-btn"
                                data-id="${contact.id}" data-name="${escapeHtml(contact.name)}" title="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `
            }).join('');

            // Re-attach event listeners
            attachContactEventListeners();
        }

        function renderPagination(paginationData, query) {
            if (!pagination) return;

            const {
                current_page,
                last_page,
                total,
                first_item,
                last_item
            } = paginationData;

            if (last_page <= 1) {
                pagination.innerHTML = '';
                return;
            }

            // Build base URL with search query if present
            const baseUrl = new URL(window.location.pathname, window.location.origin);
            if (query) baseUrl.searchParams.set('q', query);

            let paginationHtml = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';

            // Previous button
            if (current_page > 1) {
                const prevUrl = new URL(baseUrl);
                prevUrl.searchParams.set('page', current_page - 1);
                paginationHtml += `<li class="page-item"><a class="page-link" href="${prevUrl.toString()}">السابق</a></li>`;
            } else {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">السابق</span></li>';
            }

            // Page numbers (show max 5 pages)
            let startPage = Math.max(1, current_page - 2);
            let endPage = Math.min(last_page, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            for (let i = startPage; i <= endPage; i++) {
                const pageUrl = new URL(baseUrl);
                pageUrl.searchParams.set('page', i);
                if (i === current_page) {
                    paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    paginationHtml +=
                        `<li class="page-item"><a class="page-link" href="${pageUrl.toString()}">${i}</a></li>`;
                }
            }

            // Next button
            if (current_page < last_page) {
                const nextUrl = new URL(baseUrl);
                nextUrl.searchParams.set('page', current_page + 1);
                paginationHtml += `<li class="page-item"><a class="page-link" href="${nextUrl.toString()}">التالي</a></li>`;
            } else {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">التالي</span></li>';
            }

            paginationHtml += '</ul></nav>';

            // Add info text
            paginationHtml +=
                `<div class="text-center text-muted small mt-2">عرض ${first_item || 0}-${last_item || 0} من ${total} جهة اتصال</div>`;

            pagination.innerHTML = paginationHtml;
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'الآن';
            if (diffMins < 60) return `منذ ${diffMins} دقيقة`;
            if (diffHours < 24) return `منذ ${diffHours} ساعة`;
            if (diffDays < 7) return `منذ ${diffDays} يوم`;
            return date.toLocaleDateString('ar-EG');
        }

        function attachContactEventListeners() {
            // Re-attach checkbox listeners
            document.querySelectorAll('.contact-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    if (this.checked) {
                        SelectionManager.add(this.value);
                    } else {
                        SelectionManager.remove(this.value);
                    }
                    updateBulkActions();
                });
            });

            // Row click to toggle selection (except when clicking buttons/checkboxes)
            document.querySelectorAll('#contactsBody tr').forEach(row => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function(e) {
                    // Don't toggle if clicking on buttons, links, or the checkbox itself
                    if (e.target.closest('button, a, input[type="checkbox"]')) return;

                    const checkbox = this.querySelector('.contact-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
            });

            // Re-attach edit button listeners
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent row click
                    document.getElementById('editContactId').value = this.dataset.id;
                    document.getElementById('editName').value = this.dataset.name;
                    document.getElementById('editPhone').value = this.dataset.phone;
                    document.getElementById('editForm').action = `/contacts/${this.dataset.id}`;
                    editModal.show();
                });
            });

            // Re-attach delete button listeners
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent row click
                    document.getElementById('deleteContactName').textContent = this.dataset.name;
                    document.getElementById('deleteForm').action = `/contacts/${this.dataset.id}`;
                    deleteModal.show();
                });
            });
        }

        // ========== SELECT ALL (Current Page Only) ==========
        selectAllEl?.addEventListener('change', function() {
            const visibleCheckboxes = [...document.querySelectorAll('.contact-checkbox')].filter(
                cb => cb.closest('tr').style.display !== 'none'
            );
            const ids = visibleCheckboxes.map(cb => cb.value);

            if (this.checked) {
                // Add all visible to selection
                SelectionManager.addMany(ids);
                visibleCheckboxes.forEach(cb => cb.checked = true);
            } else {
                // Remove all visible from selection
                SelectionManager.removeMany(ids);
                visibleCheckboxes.forEach(cb => cb.checked = false);
            }

            updateBulkActions();
        });

        // ========== INDIVIDUAL CHECKBOX CHANGE ==========
        document.querySelectorAll('.contact-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                SelectionManager.toggle(this.value, this.checked);
                updateBulkActions();
            });
        });

        // ========== CLEAR ALL SELECTIONS ==========
        document.getElementById('clearSelectionBtn')?.addEventListener('click', function() {
            SelectionManager.clear();
            document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = false);
            updateBulkActions();
            showToast('success', 'تم إلغاء كل التحديدات');
        });

        // ========== UPDATE BULK ACTIONS UI ==========
        function updateBulkActions() {
            const visibleCheckboxes = [...document.querySelectorAll('.contact-checkbox')].filter(
                cb => cb.closest('tr').style.display !== 'none'
            );

            // Count checked on current page
            const currentPageChecked = visibleCheckboxes.filter(cb => cb.checked).length;

            // Get total from SelectionManager (all pages)
            const totalSelected = SelectionManager.count();

            // Count selections from other pages
            const currentPageIds = new Set(visibleCheckboxes.map(cb => cb.value));
            const otherPagesCount = SelectionManager.getAll().filter(id => !currentPageIds.has(id)).length;

            // Update UI
            selectedCountEl.textContent = currentPageChecked;
            totalSelectedCountEl.textContent = totalSelected;

            // Show/hide cross-page indicator
            if (otherPagesCount > 0) {
                crossPageIndicator.classList.remove('d-none');
            } else {
                crossPageIndicator.classList.add('d-none');
            }

            // Show/hide bulk actions
            bulkActionsEl.style.display = totalSelected > 0 ? 'flex' : 'none';

            // Update select all state
            if (selectAllEl) {
                const allVisibleChecked = visibleCheckboxes.length > 0 && currentPageChecked === visibleCheckboxes.length;
                selectAllEl.checked = allVisibleChecked;
                selectAllEl.indeterminate = currentPageChecked > 0 && currentPageChecked < visibleCheckboxes.length;
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

        // ========== BULK DELETE (Uses ALL selections from SelectionManager) ==========
        document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
            const allSelectedIds = SelectionManager.getAll();
            document.getElementById('bulkDeleteCount').textContent = allSelectedIds.length;
            document.getElementById('bulkDeleteIds').value = JSON.stringify(allSelectedIds);
            bulkDeleteModal.show();
        });

        // Clear selection after successful bulk delete
        document.getElementById('bulkDeleteForm')?.addEventListener('submit', function() {
            // Clear selection on form submit (will be processed by server)
            SelectionManager.clear();
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

        // ========== SHARE MODAL ==========
        document.getElementById('shareModal')?.addEventListener('show.bs.modal', function() {
            const allSelectedIds = SelectionManager.getAll();
            document.getElementById('shareCount').textContent = allSelectedIds.length;

            // Populate hidden inputs for contacts
            const container = document.getElementById('shareContactsInputs');
            container.innerHTML = allSelectedIds.map(id =>
                `<input type="hidden" name="contacts[]" value="${id}">`
            ).join('');
        });

        // Clear selection after successful share
        document.getElementById('shareForm')?.addEventListener('submit', function() {
            SelectionManager.clear();
        });

        // ========== IMPORT FORM - BLOCK UI ==========
        document.getElementById('importForm')?.addEventListener('submit', function() {
            showLoadingOverlay('جاري تحميل الملف...', 'يرجى الانتظار حتى يتم قراءة البيانات');
        });
    </script>
@endpush
