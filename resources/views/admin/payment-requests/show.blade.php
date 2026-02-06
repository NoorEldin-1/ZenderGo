@extends('admin.layouts.app')

@section('title', 'تفاصيل طلب الدفع')

@push('styles')
    <style>
        .status-badge-large {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            border: 2px solid #ffc107;
            color: #856404;
        }

        .status-approved {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            color: #155724;
        }

        .status-rejected {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid #dc3545;
            color: #721c24;
        }

        .receipt-image {
            width: 100%;
            max-height: 450px;
            object-fit: contain;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .receipt-image:hover {
            transform: scale(1.02);
        }

        /* Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            cursor: pointer;
        }

        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 8px;
        }

        .image-modal .close-btn {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="mb-0">
            <i class="bi bi-credit-card text-success ms-2"></i>تفاصيل طلب الدفع #{{ $paymentRequest->id }}
        </h4>
        <a href="{{ route('admin.payment-requests.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right ms-1"></i>رجوع للقائمة
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle ms-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Receipt Image --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>صورة الوصل</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    @if ($paymentRequest->isPending())
                        <img src="{{ asset('storage/' . $paymentRequest->receipt_image) }}" alt="صورة الوصل"
                            class="receipt-image" onclick="openImageModal(this.src)">
                        <p class="text-muted small mt-2 mb-0 text-center">
                            <i class="bi bi-zoom-in me-1"></i>اضغط على الصورة للتكبير
                        </p>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-trash fs-1 d-block mb-2"></i>
                            <p class="mb-0">تم حذف الصورة بعد معالجة الطلب</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Request & User Details --}}
        <div class="col-lg-6">
            {{-- Current Status --}}
            <div class="card text-center mb-4">
                <div class="card-body py-3">
                    <div class="status-badge-large status-{{ $paymentRequest->status }}">
                        @if ($paymentRequest->isPending())
                            <i class="bi bi-hourglass-split"></i>قيد المراجعة
                        @elseif($paymentRequest->isApproved())
                            <i class="bi bi-check-circle-fill"></i>تم القبول
                        @else
                            <i class="bi bi-x-circle-fill"></i>تم الرفض
                        @endif
                    </div>
                </div>
            </div>

            {{-- User Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>بيانات المستخدم</h6>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">الاسم</span>
                        <span class="fw-semibold">{{ $paymentRequest->user->name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">رقم الهاتف</span>
                        <span class="fw-semibold" dir="ltr">{{ $paymentRequest->user->phone }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">حالة الاشتراك</span>
                        <span>
                            @if ($paymentRequest->user->hasActiveSubscription())
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-danger">منتهي</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">حالة الحساب</span>
                        <span>
                            @if ($paymentRequest->user->is_suspended)
                                <span class="badge bg-danger">معطل</span>
                            @else
                                <span class="badge bg-success">نشط</span>
                            @endif
                        </span>
                    </li>
                </ul>
            </div>

            {{-- Request Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>تفاصيل الطلب</h6>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">المبلغ</span>
                        <span class="fw-semibold text-success">{{ number_format($paymentRequest->amount) }} جنيه</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">تاريخ الإرسال</span>
                        <span class="fw-semibold">{{ $paymentRequest->created_at->format('Y/m/d - H:i') }}</span>
                    </li>
                    @if ($paymentRequest->reviewed_at)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">تاريخ المراجعة</span>
                            <span class="fw-semibold">{{ $paymentRequest->reviewed_at->format('Y/m/d - H:i') }}</span>
                        </li>
                    @endif
                    @if ($paymentRequest->admin_notes)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">الملاحظات</span>
                            <span class="fw-semibold">{{ $paymentRequest->admin_notes }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Actions (only for pending) --}}
            @if ($paymentRequest->isPending())
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>اتخاذ إجراء</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">ملاحظات (اختياري للقبول، إجباري للرفض)</label>
                            <textarea id="adminNotes" class="form-control" rows="2" placeholder="اكتب ملاحظة..."></textarea>
                            <div id="notesError" class="text-danger small mt-1 d-none">
                                <i class="bi bi-exclamation-circle me-1"></i>يجب كتابة سبب الرفض
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success flex-fill" data-bs-toggle="modal"
                                data-bs-target="#approveModal">
                                <i class="bi bi-check-circle me-1"></i>قبول وتفعيل الاشتراك
                            </button>
                            <button type="button" class="btn btn-danger flex-fill" id="rejectBtn">
                                <i class="bi bi-x-circle me-1"></i>رفض الطلب
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Approve Modal --}}
                <div class="modal fade" id="approveModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-check-circle me-2"></i>تأكيد القبول
                                </h5>
                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <i class="bi bi-patch-check-fill text-success" style="font-size: 4rem;"></i>
                                <p class="mt-3 mb-0 fs-5">هل أنت متأكد من قبول هذا الطلب وتفعيل اشتراك المستخدم؟</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <form action="{{ route('admin.payment-requests.approve', $paymentRequest) }}"
                                    method="POST" id="approveForm">
                                    @csrf
                                    <input type="hidden" name="notes" id="approveNotes">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i>نعم، قبول وتفعيل
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reject Modal --}}
                <div class="modal fade" id="rejectModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-x-circle me-2"></i>تأكيد الرفض
                                </h5>
                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                                <p class="mt-3 mb-0 fs-5">هل أنت متأكد من رفض هذا الطلب؟</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <form action="{{ route('admin.payment-requests.reject', $paymentRequest) }}"
                                    method="POST" id="rejectForm">
                                    @csrf
                                    <input type="hidden" name="notes" id="rejectNotes">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-circle me-1"></i>نعم، رفض الطلب
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Copy notes to approve form when modal opens
                    document.getElementById('approveModal').addEventListener('show.bs.modal', function() {
                        document.getElementById('approveNotes').value = document.getElementById('adminNotes').value;
                    });

                    // Handle reject button - validate notes first
                    document.getElementById('rejectBtn').addEventListener('click', function() {
                        var notes = document.getElementById('adminNotes').value.trim();
                        var errorDiv = document.getElementById('notesError');

                        if (!notes) {
                            errorDiv.classList.remove('d-none');
                            document.getElementById('adminNotes').focus();
                            document.getElementById('adminNotes').classList.add('is-invalid');
                            return;
                        }

                        errorDiv.classList.add('d-none');
                        document.getElementById('adminNotes').classList.remove('is-invalid');
                        document.getElementById('rejectNotes').value = notes;

                        var rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
                        rejectModal.show();
                    });

                    // Clear error when typing
                    document.getElementById('adminNotes').addEventListener('input', function() {
                        document.getElementById('notesError').classList.add('d-none');
                        this.classList.remove('is-invalid');
                    });
                </script>
            @endif
        </div>
    </div>

    {{-- Image Modal --}}
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="close-btn">&times;</span>
        <img id="modalImage" src="" alt="صورة مكبرة">
    </div>
@endsection

@push('scripts')
    <script>
        function openImageModal(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
@endpush
