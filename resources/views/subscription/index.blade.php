@extends('layouts.app')

@section('title', 'اشتراكي')

@push('styles')
    <style>
        .subscription-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .subscription-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .subscription-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #25D366 0%, #128C7E 100%);
        }

        .subscription-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .subscription-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
        }

        .subscription-icon.trial {
            background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
            color: white;
        }

        .subscription-icon.paid {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
        }

        .subscription-icon.expired {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .subscription-type {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .subscription-type.trial {
            color: #17a2b8;
        }

        .subscription-type.paid {
            color: #25D366;
        }

        .subscription-type.expired {
            color: #dc3545;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: rgba(37, 211, 102, 0.15);
            color: #128C7E;
        }

        .status-badge.expired {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            background: #f0f5f1;
            transform: translateY(-2px);
        }

        .info-card .icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .info-card .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
        }

        .info-card .label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Progress Bar */
        .progress-section {
            margin: 1.5rem 0;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .progress-bar-custom {
            height: 12px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-fill.trial {
            background: linear-gradient(90deg, #17a2b8 0%, #0dcaf0 100%);
        }

        .progress-fill.paid {
            background: linear-gradient(90deg, #25D366 0%, #128C7E 100%);
        }

        .progress-fill.low {
            background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%);
        }

        .progress-fill.critical {
            background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
        }

        /* Payment Section */
        .payment-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .payment-section h6 {
            color: #212529;
            margin-bottom: 1rem;
        }

        .vodafone-number {
            background: linear-gradient(135deg, #e60000 0%, #cc0000 100%);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .vodafone-number .number {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 1px;
            direction: ltr;
        }

        .vodafone-number .copy-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .vodafone-number .copy-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
        }

        .upload-area:hover {
            border-color: #25D366;
            background: rgba(37, 211, 102, 0.05);
        }

        .upload-area.dragover {
            border-color: #25D366;
            background: rgba(37, 211, 102, 0.1);
        }

        .upload-area i {
            font-size: 2.5rem;
            color: #6c757d;
            margin-bottom: 0.75rem;
        }

        .upload-area p {
            margin-bottom: 0;
            color: #6c757d;
        }

        .upload-preview {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Pending Request Alert */
        .pending-alert {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 14px;
            padding: 1.5rem;
            text-align: center;
        }

        .pending-alert i {
            font-size: 2.5rem;
            color: #856404;
            margin-bottom: 0.75rem;
        }

        .pending-alert h6 {
            color: #856404;
            margin-bottom: 0.5rem;
        }

        /* Rejected Alert */
        .rejected-alert {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid #dc3545;
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .rejected-alert i {
            font-size: 1.5rem;
            color: #721c24;
        }

        .rejected-alert h6 {
            color: #721c24;
            margin-bottom: 0.5rem;
        }

        /* Expired Alert */
        .expired-alert {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
            border: 2px solid #dc3545;
            border-radius: 14px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .expired-alert i {
            font-size: 2.5rem;
            color: #dc3545;
            margin-bottom: 0.75rem;
        }

        .expired-alert h5 {
            color: #dc3545;
            margin-bottom: 0.5rem;
        }

        /* Payment History */
        .history-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .history-table table {
            margin-bottom: 0;
        }

        /* Trial Notice Styles */
        .trial-notice {
            background: linear-gradient(135deg, #e3f6f5 0%, #d1ecf1 100%);
            border: 2px solid #17a2b8;
            border-radius: 16px;
            padding: 1rem;
        }

        .trial-notice-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.35);
        }

        .trial-notice h5 {
            color: #0c5460;
            font-weight: 700;
        }

        .trial-countdown {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
    </style>
@endpush

@section('content')
    <div class="subscription-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="bi bi-gem text-success ms-2"></i>اشتراكي
            </h4>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right ms-1"></i>الرئيسية
            </a>
        </div>

        {{-- Current Subscription Status Card --}}
        <div class="subscription-card">
            @if ($subscription && $subscription->isActive())
                {{-- Active Subscription --}}
                <div class="subscription-header">
                    <div class="subscription-icon {{ $subscription->type }}">
                        @if ($subscription->isTrial())
                            <i class="bi bi-gift"></i>
                        @else
                            <i class="bi bi-patch-check-fill"></i>
                        @endif
                    </div>
                    <div class="subscription-type {{ $subscription->type }}">
                        @if ($subscription->isTrial())
                            فترة تجريبية
                        @else
                            اشتراك مفعّل
                        @endif
                    </div>
                    <span class="status-badge active">
                        <i class="bi bi-check-circle-fill ms-1"></i>نشط
                    </span>
                </div>

                <div class="info-cards">
                    <div class="info-card">
                        <div class="icon text-primary">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div class="value">{{ $subscription->daysRemaining() }}</div>
                        <div class="label">يوم متبقي</div>
                    </div>
                    <div class="info-card">
                        <div class="icon text-success">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="value">{{ $subscription->ends_at->format('m/d') }}</div>
                        <div class="label">ينتهي في</div>
                    </div>
                </div>

                @php
                    $percentage = $subscription->percentageRemaining();
                    $progressClass = $subscription->type;
                    if ($percentage <= 20) {
                        $progressClass = 'critical';
                    } elseif ($percentage <= 40) {
                        $progressClass = 'low';
                    }
                @endphp

                <div class="progress-section">
                    <div class="progress-label">
                        <span>المدة المتبقية</span>
                        <span>{{ $percentage }}%</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill {{ $progressClass }}" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            @else
                {{-- Expired or No Subscription --}}
                <div class="expired-alert">
                    <i class="bi bi-exclamation-triangle-fill d-block"></i>
                    <h5>انتهى اشتراكك</h5>
                    <p class="text-muted mb-0">قم بتجديد اشتراكك للاستمرار في استخدام الخدمة</p>
                </div>

                <div class="subscription-header">
                    <div class="subscription-icon expired">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="subscription-type expired">غير مشترك</div>
                    <span class="status-badge expired">
                        <i class="bi bi-x-circle-fill ms-1"></i>منتهي
                    </span>
                </div>
            @endif

            {{-- Payment Section - Only show if NOT in active trial period --}}
            @if ($subscription && $subscription->isActive() && $subscription->isTrial())
                {{-- Trial Active - Block Subscription --}}
                <div class="payment-section">
                    <div class="trial-notice">
                        <div class="text-center py-4">
                            <div class="trial-notice-icon">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <h5 class="mt-3 mb-2">أنت في الفترة التجريبية</h5>
                            <p class="text-muted mb-3">
                                استمتع بالفترة التجريبية المجانية!
                                <br>
                                ستتمكن من الاشتراك المدفوع بعد انتهاء الفترة التجريبية.
                            </p>
                            <div class="trial-countdown mb-3">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <i class="bi bi-calendar-event text-info"></i>
                                    <span>متبقي <strong class="text-info">{{ $subscription->daysRemaining() }}</strong>
                                        يوم</span>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    تنتهي بتاريخ: {{ $subscription->ends_at->format('Y/m/d') }}
                                </small>
                            </div>

                            {{-- Subscription Info Preview --}}
                            <div class="subscription-preview mt-3">
                                <div class="alert alert-light border mb-0 text-start">
                                    <h6 class="mb-2"><i class="bi bi-info-circle text-success me-1"></i>معلومات الاشتراك:
                                    </h6>
                                    <ul class="mb-0 pe-3 small">
                                        <li><strong>سعر الاشتراك:</strong> {{ number_format($subscriptionPrice) }} جنيه /
                                            شهر</li>
                                        <li><strong>طريقة الدفع:</strong> فودافون كاش</li>
                                        <li><strong>التفعيل:</strong> يتم مراجعة طلبك وتفعيل الاشتراك خلال ساعات</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Show Payment Section - Trial expired or no subscription --}}
                <div class="payment-section">
                    <h6><i class="bi bi-wallet2 me-2"></i>الاشتراك أو التجديد</h6>

                    {{-- Show last rejected request warning --}}
                    @if ($lastRejectedRequest)
                        <div class="rejected-alert d-flex align-items-start gap-3">
                            <i class="bi bi-x-circle-fill"></i>
                            <div>
                                <h6 class="mb-1">تم رفض طلبك السابق</h6>
                                @if ($lastRejectedRequest->admin_notes)
                                    <p class="mb-0 small"><strong>ملاحظة:</strong> {{ $lastRejectedRequest->admin_notes }}
                                    </p>
                                @else
                                    <p class="mb-0 small">يمكنك إرسال طلب جديد بصورة واضحة</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Instructions --}}
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-info-circle fs-5"></i>
                            <strong>خطوات الاشتراك:</strong>
                        </div>
                        <ol class="mb-0 pe-4">
                            <li>حوّل المبلغ <strong>({{ number_format($subscriptionPrice) }} جنيه)</strong> على رقم فودافون
                                كاش
                                أدناه</li>
                            <li>بعد التحويل، ارفع صورة الوصل من هنا</li>
                            <li>انتظر مراجعة الطلب وتفعيل اشتراكك</li>
                        </ol>
                    </div>

                    {{-- Vodafone Cash Number --}}
                    <div class="vodafone-number">
                        <div>
                            <small class="d-block opacity-75">رقم فودافون كاش</small>
                            <span class="number" id="vodafoneNumber">{{ $vodafoneCashNumber }}</span>
                        </div>
                        <button type="button" class="copy-btn" onclick="copyNumber()">
                            <i class="bi bi-clipboard me-1"></i>نسخ
                        </button>
                    </div>

                    @if ($pendingRequest)
                        {{-- Pending Request Display --}}
                        <div class="pending-alert">
                            <i class="bi bi-hourglass-split d-block"></i>
                            <h6>طلبك قيد المراجعة</h6>
                            <p class="text-muted small mb-2">
                                تم إرسال طلبك بتاريخ {{ $pendingRequest->created_at->format('Y/m/d - H:i') }}
                            </p>
                            <p class="mb-0 small">
                                <i class="bi bi-clock me-1"></i>سيتم مراجعة طلبك وتفعيل اشتراكك في أقرب وقت
                            </p>
                        </div>
                    @else
                        {{-- Upload Form --}}
                        <form action="{{ route('subscription.payment') }}" method="POST" enctype="multipart/form-data"
                            id="paymentForm">
                            @csrf

                            <div class="upload-area" id="uploadArea"
                                onclick="document.getElementById('receiptInput').click()">
                                <i class="bi bi-cloud-upload"></i>
                                <p><strong>اضغط لرفع صورة الوصل</strong></p>
                                <p class="small text-muted">JPG, PNG, WebP - حد أقصى 5MB</p>
                                <img id="previewImage" class="upload-preview d-none" alt="معاينة">
                            </div>

                            <input type="file" id="receiptInput" name="receipt" accept="image/jpeg,image/png,image/webp"
                                class="d-none" required>

                            @error('receipt')
                                <div class="text-danger small mt-2">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror

                            <button type="submit" class="btn btn-success w-100 mt-3 py-2" id="submitBtn" disabled>
                                <i class="bi bi-send me-1"></i>إرسال طلب الاشتراك
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        {{-- Payment History --}}
        @if ($paymentHistory->count() > 0)
            <div class="subscription-card">
                <h6 class="mb-3"><i class="bi bi-clock-history me-2"></i>سجل طلباتك</h6>
                <div class="history-table table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>التاريخ</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>الملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($paymentHistory as $request)
                                <tr>
                                    <td>{{ $request->created_at->format('m/d') }}</td>
                                    <td>{{ number_format($request->amount) }} ج</td>
                                    <td>
                                        <span class="badge {{ $request->status_badge_class }}">
                                            @if ($request->isApproved())
                                                <i class="bi bi-check-circle me-1"></i>
                                            @else
                                                <i class="bi bi-x-circle me-1"></i>
                                            @endif
                                            {{ $request->status_text }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $request->admin_notes ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Copy Vodafone number
        function copyNumber() {
            const number = document.getElementById('vodafoneNumber').textContent;
            navigator.clipboard.writeText(number).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.innerHTML = '<i class="bi bi-check me-1"></i>تم النسخ';
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>نسخ';
                }, 2000);
            });
        }

        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const receiptInput = document.getElementById('receiptInput');
        const previewImage = document.getElementById('previewImage');
        const submitBtn = document.getElementById('submitBtn');

        if (receiptInput) {
            receiptInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);

                    // Enable submit
                    submitBtn.disabled = false;
                }
            });

            // Drag and drop
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    receiptInput.files = files;
                    receiptInput.dispatchEvent(new Event('change'));
                }
            });
        }

        // Form submission with loading
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الإرسال...';
            });
        }
    </script>
@endpush
