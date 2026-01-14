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

    <!-- Contact Usage Indicator -->
    <!-- Contact Usage Indicator -->
    <div class="card mb-4 border-0 shadow-sm overflow-hidden position-relative">
        <div class="card-body p-4">
            <!-- Background Gradient (Subtle) -->
            <div
                style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #20c997, #ffc107, #dc3545);">
            </div>

            <div class="row align-items-end mb-2">
                <div class="col-8">
                    <h5 class="fw-bold mb-1">إحصائيات جهات الاتصال</h5>
                    <p class="text-muted small mb-0">متابعة استهلاك الحد المسموح به</p>
                </div>
                <div class="col-4 text-end">
                    <div class="d-inline-block text-center">
                        <span
                            class="display-6 fw-bold @if ($usagePercent > 90) text-danger @elseif($usagePercent > 70) text-warning @else text-success @endif">
                            {{ $usagePercent }}<span class="fs-4">%</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress mb-3"
                style="height: 14px; border-radius: 10px; background-color: rgba(var(--bs-secondary-rgb), 0.1);">
                <div class="progress-bar @if ($usagePercent > 90) bg-danger @elseif($usagePercent > 70) bg-warning @else bg-success @endif progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: {{ min(100, $usagePercent) }}%; transition: width 1s ease-in-out;"
                    aria-valuenow="{{ $usagePercent }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="badge bg-light text-dark border p-2 rounded-3 d-flex align-items-center gap-2">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>
                            <strong>{{ number_format($contactCount) }}</strong>
                            <span class="text-muted small ms-1">مستخدم</span>
                        </span>
                    </div>
                    <span class="text-muted small">من أصل</span>
                    <strong class="text-body">{{ number_format($contactLimit) }}</strong>
                </div>

                <div class="">
                    @if ($remainingSlots <= 0)
                        <span class="badge bg-danger p-2">
                            <i class="bi bi-x-circle me-1"></i> ممتلئ
                        </span>
                    @elseif($remainingSlots <= 10)
                        <span class="badge bg-warning p-2" style="color: #000 !important;">
                            <i class="bi bi-exclamation-circle me-1"></i> متبقي {{ $remainingSlots }} فقط
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success p-2 border border-success-subtle">
                            <i class="bi bi-check-circle me-1"></i> حالة جيدة
                        </span>
                    @endif
                </div>
            </div>

            @if ($usagePercent > 90)
                <div class="alert alert-danger d-flex align-items-center p-2 mt-3 mb-0 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                    <div>
                        <strong>تنبيه هام!</strong> لقد قاربت على الوصول للحد الأقصى المسموح به لجهات الاتصال.
                    </div>
                </div>
            @endif
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
                            placeholder="بحث بالاسم أو الرقم..." autocomplete="off" value="{{ request('q') }}">
                        <button class="btn btn-outline-secondary {{ request('q') ? '' : 'd-none' }}" type="button"
                            id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <!-- Last Contacted Filter -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select form-select-sm" id="contactFilter" style="width: auto; min-width: 150px;">
                        <option value="" {{ request('contact_filter') == '' ? 'selected' : '' }}>جميع جهات الاتصال
                        </option>
                        <option value="featured" {{ request('contact_filter') == 'featured' ? 'selected' : '' }}>جهات مميزة
                            ⭐</option>
                        <option value="normal" {{ request('contact_filter') == 'normal' ? 'selected' : '' }}>جهات عادية
                        </option>
                        <option value="never" {{ request('contact_filter') == 'never' ? 'selected' : '' }}>لم يتم التواصل
                        </option>
                        <option value="range" {{ request('contact_filter') == 'range' ? 'selected' : '' }}>تم التواصل في
                            فترة</option>
                    </select>
                    <div id="dateRangeContainer" class="{{ request('contact_filter') == 'range' ? '' : 'd-none' }}">
                        <input type="text" class="form-control form-control-sm flatpickr-input" id="dateRangePicker"
                            placeholder="اختر الفترة..." style="min-width: 200px;" readonly
                            value="{{ request('date_from') && request('date_to') ? request('date_from') . ' إلى ' . request('date_to') : '' }}"
                            data-default-from="{{ request('date_from') }}" data-default-to="{{ request('date_to') }}">
                    </div>
                </div>
                <!-- Bulk Actions -->
                <div class="d-flex gap-2 align-items-center" id="bulkActions" style="display: none;">
                    <span class="text-muted small">
                        محدد: <strong id="selectedCount">0</strong>
                        <span id="crossPageIndicator" class="badge bg-info ms-1 d-none"
                            title="إجمالي المحدد من كل الصفحات">
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
                                <th class="d-none d-lg-table-cell">آخر إرسال</th>
                                <th class="d-none d-xl-table-cell">التاريخ</th>
                                <th style="width: 90px;" class="text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="contactsBody">
                            @include('contacts.partials.rows')
                        </tbody>
                    </table>
                </div>

                <!-- No Results Message -->
                <div class="text-center py-4 d-none" id="noResults">
                    <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">لا توجد نتائج مطابقة</p>
                </div>

                <!-- Pagination -->
                {{-- Pagination Container - Always Rendered to prevent JS Error --}}
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center p-3 border-top gap-3"
                    id="pagination">
                    @if ($contacts->hasPages())
                        <div class="text-muted small d-none d-sm-block">
                            عرض {{ $contacts->firstItem() }}-{{ $contacts->lastItem() }} من {{ $contacts->total() }} جهة
                            اتصال
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $contacts->links() }}
                        </nav>
                    @endif
                </div>
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

                            <!-- Custom File Input -->
                            <div class="file-upload-wrapper">
                                <input type="file" class="file-upload-input" name="file" id="fileInput"
                                    accept=".xlsx,.xls,.csv" required onchange="updateFileName(this)">
                                <div class="file-upload-box">
                                    <div class="text-center p-4">
                                        <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.5rem;"></i>
                                        <h5 class="mt-3 mb-1 fw-bold text-dark-emphasis">اضغط لاختيار الملف</h5>
                                        <p class="text-muted small mb-0" id="fileNameDisplay">أو اسحب الملف وأفلته هنا</p>
                                        <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                            الحد الأقصى: 10MB | الصيغ: xlsx, xls, csv
                                        </div>
                                    </div>
                                </div>
                            </div>
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

@push('styles')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Flatpickr Dark Mode Styles */
        [data-bs-theme="dark"] .flatpickr-input {
            background-color: #2b3035 !important;
            color: #e9ecef !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .flatpickr-input::placeholder {
            color: #6c757d !important;
        }

        [data-bs-theme="dark"] .flatpickr-calendar {
            background: #1a1d21 !important;
            border-color: #495057 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5) !important;
        }

        [data-bs-theme="dark"] .flatpickr-months .flatpickr-month {
            background: #212529 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months,
        [data-bs-theme="dark"] .flatpickr-current-month input.cur-year {
            background: #212529 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .flatpickr-weekdays {
            background: #212529 !important;
        }

        [data-bs-theme="dark"] span.flatpickr-weekday {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .flatpickr-day {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .flatpickr-day:hover {
            background: #343a40 !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.inRange,
        [data-bs-theme="dark"] .flatpickr-day.prevMonthDay.inRange,
        [data-bs-theme="dark"] .flatpickr-day.nextMonthDay.inRange {
            background: rgba(37, 211, 102, 0.2) !important;
            border-color: rgba(37, 211, 102, 0.2) !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.selected,
        [data-bs-theme="dark"] .flatpickr-day.startRange,
        [data-bs-theme="dark"] .flatpickr-day.endRange {
            background: #25d366 !important;
            border-color: #25d366 !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.today {
            border-color: #25d366 !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.disabled,
        [data-bs-theme="dark"] .flatpickr-day.prevMonthDay,
        [data-bs-theme="dark"] .flatpickr-day.nextMonthDay {
            color: #6c757d !important;
        }

        [data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month,
        [data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month {
            fill: #e9ecef !important;
        }

        [data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month:hover,
        [data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month:hover {
            fill: #25d366 !important;
        }

        /* Last Sent Badge Styles */
        .badge.bg-success-subtle {
            background-color: rgba(37, 211, 102, 0.15) !important;
        }

        .badge.bg-secondary-subtle {
            background-color: rgba(108, 117, 125, 0.15) !important;
        }

        [data-bs-theme="dark"] .badge.bg-secondary-subtle {
            background-color: rgba(108, 117, 125, 0.25) !important;
        }

        /* File Upload Styles */
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            margin-top: 0.5rem;
        }

        .file-upload-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .file-upload-box {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-upload-wrapper:hover .file-upload-box {
            background-color: #e9ecef;
            border-color: #adb5bd;
        }

        .file-upload-input:focus+.file-upload-box {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Dark Mode for File Upload */
        [data-bs-theme="dark"] .file-upload-box {
            background-color: #212529;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .file-upload-wrapper:hover .file-upload-box {
            background-color: #2b3035;
            border-color: #6c757d;
        }

        [data-bs-theme="dark"] .text-dark-emphasis {
            color: #f8f9fa !important;
        }
    </style>
@endpush

@push('scripts')
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
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
            restoreCheckboxStates();
            setupRowClickHandlers();
            updateBulkActions();
        })();

        function restoreCheckboxStates() {
            document.querySelectorAll('.contact-checkbox').forEach(cb => {
                if (SelectionManager.has(cb.value)) {
                    cb.checked = true;
                }
            });
        }

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

        // Toggle Featured Logic
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('star-btn')) {
                e.preventDefault();
                e.stopPropagation();
                toggleFeatured(e.target);
            }
        });

        function toggleFeatured(btn) {
            const id = btn.dataset.id;
            const isFeatured = btn.classList.contains('bi-star-fill');

            // Optimistic UI update
            if (isFeatured) {
                btn.classList.replace('bi-star-fill', 'bi-star');
                btn.classList.remove('text-warning');
                btn.classList.add('text-muted');
                btn.title = 'إضافة للمميزة';
            } else {
                btn.classList.replace('bi-star', 'bi-star-fill');
                btn.classList.remove('text-muted');
                btn.classList.add('text-warning');
                btn.title = 'إزالة من المميزة';
            }

            // Animate
            btn.style.transform = 'scale(1.2)';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);

            fetch(`/contacts/${id}/toggle-featured`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        // Revert on error
                        if (isFeatured) {
                            btn.classList.replace('bi-star', 'bi-star-fill');
                            btn.classList.add('text-warning');
                            btn.classList.remove('text-muted');
                        } else {
                            btn.classList.replace('bi-star-fill', 'bi-star');
                            btn.classList.add('text-muted');
                            btn.classList.remove('text-warning');
                        }
                        // alert(data.message || 'Error updating status');
                    }
                })
                .catch(err => {
                    console.error(err);
                    // Revert on error
                    if (isFeatured) {
                        btn.classList.replace('bi-star', 'bi-star-fill');
                        btn.classList.add('text-warning');
                        btn.classList.remove('text-muted');
                    } else {
                        btn.classList.replace('bi-star-fill', 'bi-star');
                        btn.classList.add('text-muted');
                        btn.classList.remove('text-warning');
                    }
                });
        }

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
                '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-success me-2"></div>جاري البحث...</td></tr>';
            noResults.classList.add('d-none');

            // Build URL with search query and filters
            const url = new URL(window.location.href);
            url.searchParams.set('q', query);
            url.searchParams.delete('page'); // Reset to page 1 on search

            // Add contact filter parameters
            const contactFilter = document.getElementById('contactFilter')?.value || '';
            if (contactFilter) {
                url.searchParams.set('contact_filter', contactFilter);
                if (contactFilter === 'range' && window.flatpickrInstance?.selectedDates?.length >= 1) {
                    const dates = window.flatpickrInstance.selectedDates;
                    // IMPORTANT: Use local date format, NOT toISOString() which converts to UTC
                    const formatLocalDate = (date) => {
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                    };
                    const dateFrom = formatLocalDate(dates[0]);
                    const dateTo = dates.length >= 2 ? formatLocalDate(dates[1]) : dateFrom;
                    url.searchParams.set('date_from', dateFrom);
                    url.searchParams.set('date_to', dateTo);
                }
            } else {
                url.searchParams.delete('contact_filter');
                url.searchParams.delete('date_from');
                url.searchParams.delete('date_to');
            }

            fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    // Update URL
                    history.pushState(null, '', url.toString());

                    if (data.html) {
                        contactsBody.innerHTML = data.html;
                        noResults.classList.add('d-none');
                        // RESTORE CHECKBOX STATES AFTER RENDER
                        restoreCheckboxStates();
                    } else {
                        contactsBody.innerHTML = '';
                        noResults.classList.remove('d-none');
                    }

                    if (pagination) {
                        if (data.pagination) {
                            pagination.innerHTML = data.pagination;
                        } else {
                            pagination.innerHTML = '';
                        }
                    }

                    updateBulkActions();
                    attachContactEventListeners();

                    // Update total badge
                    if (data.total !== undefined) {
                        document.getElementById('totalBadge').textContent = data.total;
                    }
                })
                .catch(err => {
                    console.error('Search error:', err);
                    contactsBody.innerHTML =
                        `<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>حدث خطأ أثناء البحث: ${err.message}</td></tr>`;
                });
        }

        // File Upload Name Update
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const fileBox = input.nextElementSibling;

            if (input.files && input.files[0]) {
                const name = input.files[0].name;
                // Clear and rebuild safely
                fileNameDisplay.innerHTML = '';
                const span = document.createElement('span');
                span.className = 'text-success fw-bold';
                const icon = document.createElement('i');
                icon.className = 'bi bi-check-circle me-1';
                span.appendChild(icon);
                span.appendChild(document.createTextNode(name));
                fileNameDisplay.appendChild(span);
                fileBox.style.borderColor = '#198754';
                fileBox.style.backgroundColor = 'rgba(25, 135, 84, 0.05)';
            } else {
                fileNameDisplay.textContent = 'أو اسحب الملف وأفلته هنا';
                fileBox.style.borderColor = ''; // reset
                fileBox.style.backgroundColor = ''; // reset
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Removed manual renderContacts and renderPagination - using server-side HTML now
        // Removed formatDate - handled server-side

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

            // Update UI - Use LOCAL count for "Selected" to allow per-page selection, OR GLOBAL?
            // User requested: "When I move to another page... number returns to 0".
            // Fix: Show GLOBAL count in main badge.
            selectedCountEl.textContent = totalSelected; // CHANGED FROM currentPageChecked

            // Note: crossPageIndicator is now redundant if we show totalSelected in main badge. 
            // We can hide it or keep it for emphasis. I will hide it to reduce clutter.
            crossPageIndicator.classList.add('d-none'); // ALWAYS HIDDEN

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

        // ========== FLATPICKR & CONTACT FILTER ==========
        // Initialize Flatpickr date range picker
        const dateRangePicker = document.getElementById('dateRangePicker');
        const dateRangeContainer = document.getElementById('dateRangeContainer');
        const contactFilter = document.getElementById('contactFilter');

        if (dateRangePicker && typeof flatpickr !== 'undefined') {
            const defaultDate = dateRangePicker.dataset.defaultFrom && dateRangePicker.dataset.defaultTo ? [dateRangePicker
                .dataset.defaultFrom, dateRangePicker.dataset.defaultTo
            ] : null;

            window.flatpickrInstance = flatpickr(dateRangePicker, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                locale: 'ar',
                maxDate: 'today',
                defaultDate: defaultDate,
                onChange: function(selectedDates, dateStr) {
                    if (selectedDates.length === 2) {
                        // Both dates selected - trigger search
                        performSearch(searchInput.value.trim());
                    }
                },
                onReady: function(selectedDates, dateStr, instance) {
                    // Apply RTL styling fix
                    instance.calendarContainer.classList.add('flatpickr-rtl');
                }
            });
        }

        // Contact filter change handler
        contactFilter?.addEventListener('change', function() {
            const value = this.value;

            if (value === 'range') {
                // Show date range picker
                dateRangeContainer.classList.remove('d-none');
                // Don't search until dates are selected
            } else {
                // Hide date range picker
                dateRangeContainer.classList.add('d-none');
                // Clear date selection
                if (window.flatpickrInstance) {
                    window.flatpickrInstance.clear();
                }
                // Trigger search for 'never' or 'all'
                performSearch(searchInput.value.trim());
            }
        });
    </script>
@endpush
