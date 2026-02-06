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

        /* InstaPay Styles */
        .instapay-number {
            background: linear-gradient(135deg, #0066b2 0%, #004d86 100%);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .instapay-number .number {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 1px;
            direction: ltr;
        }

        .instapay-number .copy-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .instapay-number .copy-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Payment Method Tabs */
        .payment-methods-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .payment-tab {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .payment-tab:hover {
            background: #f8f9fa;
        }

        .payment-tab.active.vodafone {
            border-color: #e60000;
            background: rgba(230, 0, 0, 0.1);
        }

        .payment-tab.active.instapay {
            border-color: #0066b2;
            background: rgba(0, 102, 178, 0.1);
        }


        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
            overflow: hidden;
            /* Prevent any children from overflowing */
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
            max-width: 100%;
            /* Never exceed container */
            width: auto;
            height: auto;
            max-height: 250px;
            /* Limit height for better UX */
            object-fit: contain;
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

        /* ===== DARK MODE OVERRIDES ===== */
        [data-bs-theme="dark"] .subscription-card {
            background: var(--bs-dark);
        }

        [data-bs-theme="dark"] .info-card {
            background: #1a1d21;
        }

        [data-bs-theme="dark"] .info-card:hover {
            background: #22262b;
        }

        [data-bs-theme="dark"] .info-card .icon {
            background: #2c3035;
        }

        [data-bs-theme="dark"] .info-card .value {
            color: #e9ecef;
        }

        [data-bs-theme="dark"] .payment-section {
            background: linear-gradient(135deg, #1a1d21 0%, #22262b 100%);
        }

        [data-bs-theme="dark"] .payment-section h6 {
            color: #e9ecef;
        }

        [data-bs-theme="dark"] .payment-tab {
            background: #1a1d21;
            border-color: #495057;
            color: #e9ecef;
        }

        [data-bs-theme="dark"] .payment-tab:hover {
            background: #22262b;
        }

        [data-bs-theme="dark"] .upload-area {
            background: #1a1d21;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .upload-area:hover {
            border-color: #25D366;
            background: rgba(37, 211, 102, 0.1);
        }

        /* Dark Mode: Rejected Alert - CRITICAL FIX */
        [data-bs-theme="dark"] .rejected-alert {
            background: linear-gradient(135deg, #3d1f23 0%, #4a252a 100%);
            border-color: #dc3545;
        }

        [data-bs-theme="dark"] .rejected-alert i {
            color: #ff6b7a;
        }

        [data-bs-theme="dark"] .rejected-alert h6 {
            color: #ff8a96;
        }

        [data-bs-theme="dark"] .rejected-alert p {
            color: #e0a6ab;
        }

        /* Dark Mode: Pending Alert */
        [data-bs-theme="dark"] .pending-alert {
            background: linear-gradient(135deg, #3d3520 0%, #4a4025 100%);
            border-color: #ffc107;
        }

        [data-bs-theme="dark"] .pending-alert i {
            color: #ffd54f;
        }

        [data-bs-theme="dark"] .pending-alert h6 {
            color: #ffeb8a;
        }

        [data-bs-theme="dark"] .pending-alert p {
            color: #e0d5a0;
        }

        /* Dark Mode: Expired Alert */
        [data-bs-theme="dark"] .expired-alert {
            background: linear-gradient(135deg, #3d1f23 0%, #4a252a 100%);
            border-color: #dc3545;
        }

        [data-bs-theme="dark"] .expired-alert i,
        [data-bs-theme="dark"] .expired-alert h5 {
            color: #ff8a96;
        }

        /* Dark Mode: Trial Notice */
        [data-bs-theme="dark"] .trial-notice {
            background: linear-gradient(135deg, #1a3a3a 0%, #1e4040 100%);
            border-color: #17a2b8;
        }

        [data-bs-theme="dark"] .trial-notice h5 {
            color: #5dd0e6;
        }

        [data-bs-theme="dark"] .trial-countdown {
            background: #1a1d21;
        }

        /* ===== RESPONSIVE FIXES ===== */
        @media (max-width: 576px) {

            .vodafone-number,
            .instapay-number {
                flex-wrap: wrap;
                justify-content: center;
                text-align: center;
                gap: 0.75rem;
            }

            .vodafone-number>div,
            .instapay-number>div {
                width: 100%;
            }

            .vodafone-number .copy-btn,
            .instapay-number .copy-btn {
                width: 100%;
            }

            .info-cards {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            .payment-methods-tabs {
                flex-direction: column;
            }
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

        @php
            $supportPhone = \App\Models\SystemSetting::getSupportPhoneNumber();
        @endphp

        {{-- Current Subscription Status Card --}}
        <div class="subscription-card">
            {{-- Check if user is suspended for SECURITY reasons - these cannot do anything --}}
            @if (auth()->user()->is_suspended && auth()->user()->suspension_reason === 'security')
                {{-- Account Suspended for Security --}}
                <div class="expired-alert"
                    style="background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%); border: 2px solid #dc3545;">
                    <i class="bi bi-shield-exclamation d-block" style="font-size: 2.5rem; color: #dc3545;"></i>
                    <h5 style="color: #dc3545;">حسابك معطل</h5>
                    <p class="text-muted mb-2">تم تعطيل حسابك لأسباب أمنية.</p>
                    <a href="https://wa.me/2{{ $supportPhone }}" target="_blank" class="btn btn-sm btn-success">
                        <i class="bi bi-whatsapp me-1"></i>تواصل مع الدعم: {{ $supportPhone }}
                    </a>
                </div>

                <div class="subscription-header">
                    <div class="subscription-icon expired">
                        <i class="bi bi-shield-x"></i>
                    </div>
                    <div class="subscription-type expired">حساب موقوف</div>
                    <span class="status-badge expired">
                        <i class="bi bi-shield-exclamation ms-1"></i>معطل
                    </span>
                </div>
            @elseif (auth()->user()->is_suspended && auth()->user()->suspension_reason === 'subscription')
                {{-- Account Suspended for Subscription - needs to pay --}}
                <div class="expired-alert"
                    style="background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border: 2px solid #ffc107;">
                    <i class="bi bi-exclamation-triangle-fill d-block" style="font-size: 2.5rem; color: #856404;"></i>
                    <h5 style="color: #856404;">يرجى تجديد الاشتراك</h5>
                    <p class="text-muted mb-0">تم إيقاف حسابك بسبب الاشتراك. قم بتجديد اشتراكك للاستمرار.</p>
                </div>

                <div class="subscription-header">
                    <div class="subscription-icon expired">
                        <i class="bi bi-credit-card-2-front"></i>
                    </div>
                    <div class="subscription-type expired">بحاجة للتجديد</div>
                    <span class="status-badge expired">
                        <i class="bi bi-exclamation-circle-fill ms-1"></i>مطلوب الدفع
                    </span>
                </div>
            @elseif ($subscription && $subscription->isActive())
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

                @php
                    $timeRemaining = $subscription->detailedTimeRemaining();
                @endphp
                <div id="paid-timer-container" data-ends-at="{{ $subscription->ends_at->toIso8601String() }}"
                    data-starts-at="{{ $subscription->starts_at->toIso8601String() }}" class="info-cards"
                    style="grid-template-columns: repeat(3, 1fr);">
                    <div class="info-card">
                        <div class="icon text-primary">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                        <div class="value" id="paid-timer-days">{{ $timeRemaining['days'] }}</div>
                        <div class="label">يوم</div>
                    </div>
                    <div class="info-card">
                        <div class="icon text-info">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="value" id="paid-timer-hours">{{ $timeRemaining['hours'] }}</div>
                        <div class="label">ساعة</div>
                    </div>
                    <div class="info-card">
                        <div class="icon text-warning">
                            <i class="bi bi-stopwatch"></i>
                        </div>
                        <div class="value" id="paid-timer-minutes">{{ $timeRemaining['minutes'] }}</div>
                        <div class="label">دقيقة</div>
                    </div>
                    <div class="info-card">
                        <div class="icon text-danger">
                            <i class="bi bi-stopwatch-fill"></i>
                        </div>
                        <div class="value" id="paid-timer-seconds">{{ $timeRemaining['seconds'] }}</div>
                        <div class="label">ثانية</div>
                    </div>
                </div>
                <div class="text-center mt-2 mb-3">
                    <small class="text-muted">
                        <i class="bi bi-calendar-check me-1"></i>ينتهي في:
                        {{ $subscription->ends_at->format('Y/m/d - H:i') }}
                    </small>
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
                        <span id="paid-progress-text">{{ $percentage }}%</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div id="paid-progress-fill" class="progress-fill {{ $progressClass }}"
                            style="width: {{ $percentage }}%"></div>
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

            {{-- Payment Section - Handle different states --}}
            @if (auth()->user()->is_suspended && auth()->user()->suspension_reason === 'security')
                {{-- Suspended for SECURITY - cannot subscribe at all --}}
                <div class="payment-section">
                    <div class="alert alert-danger mb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-shield-exclamation fs-5"></i>
                            <strong>حسابك موقوف</strong>
                        </div>
                        <p class="mb-2">تم إيقاف حسابك لأسباب أمنية. تواصل مع الدعم لمعرفة التفاصيل.</p>
                        <a href="https://wa.me/2{{ $supportPhone }}" target="_blank" class="btn btn-sm btn-success">
                            <i class="bi bi-whatsapp me-1"></i>تواصل مع الدعم: {{ $supportPhone }}
                        </a>
                    </div>
                </div>
            @elseif (auth()->user()->is_suspended && auth()->user()->suspension_reason === 'subscription')
                {{-- Suspended for SUBSCRIPTION - show payment form to renew --}}
                <div class="payment-section">
                    <h6><i class="bi bi-wallet2 me-2"></i>تجديد الاشتراك</h6>

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
                    @php
                        $hasVodafone = !empty($vodafoneCashNumber) && $vodafoneCashNumber !== '01XXXXXXXXX';
                        $hasInstapay = !empty($instapayNumber);
                    @endphp
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-triangle fs-5"></i>
                            <strong>مطلوب تجديد الاشتراك:</strong>
                        </div>
                        <ol class="mb-0 pe-4">
                            <li>حوّل المبلغ <strong>({{ number_format($subscriptionPrice) }} جنيه)</strong> على أحد أرقام
                                الدفع أدناه</li>
                            <li>بعد التحويل، ارفع صورة الوصل من هنا</li>
                            <li>انتظر مراجعة الطلب وإعادة تفعيل حسابك</li>
                        </ol>
                    </div>

                    {{-- Payment Method Tabs --}}
                    @if ($hasVodafone || $hasInstapay)
                        @if ($hasVodafone && $hasInstapay)
                            <div class="payment-methods-tabs">
                                <div class="payment-tab vodafone active" onclick="selectPaymentMethod('vodafone')">
                                    <i class="bi bi-phone text-danger"></i>
                                    <span>فودافون كاش</span>
                                </div>
                                <div class="payment-tab instapay" onclick="selectPaymentMethod('instapay')">
                                    <i class="bi bi-bank text-primary"></i>
                                    <span>إنستاباي</span>
                                </div>
                            </div>
                        @endif

                        {{-- Vodafone Cash Details --}}
                        @if ($hasVodafone)
                            <div id="vodafone-details" class="{{ $hasVodafone ? '' : 'd-none' }}">
                                <div class="vodafone-number">
                                    <div>
                                        <small class="d-block opacity-75">رقم فودافون كاش</small>
                                        <span class="number" id="vodafoneNumber">{{ $vodafoneCashNumber }}</span>
                                    </div>
                                    <button type="button" class="copy-btn" onclick="copyNumber('vodafoneNumber')">
                                        <i class="bi bi-clipboard me-1"></i>نسخ
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- InstaPay Details --}}
                        @if ($hasInstapay)
                            <div id="instapay-details" class="{{ !$hasVodafone ? '' : 'd-none' }}">
                                <div class="instapay-number">
                                    <div>
                                        <small class="d-block opacity-75">رقم إنستاباي</small>
                                        <span class="number" id="instapayNumber">{{ $instapayNumber }}</span>
                                    </div>
                                    <button type="button" class="copy-btn" onclick="copyNumber('instapayNumber')">
                                        <i class="bi bi-clipboard me-1"></i>نسخ
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if ($pendingRequest)
                        {{-- Pending Request Display --}}
                        <div class="pending-alert">
                            <i class="bi bi-hourglass-split d-block"></i>
                            <h6>طلبك قيد المراجعة</h6>
                            <p class="text-muted small mb-2">
                                تم إرسال طلبك بتاريخ {{ $pendingRequest->created_at->format('Y/m/d - H:i') }}
                            </p>
                            <p class="mb-2 small">
                                <i class="bi bi-clock me-1"></i>سيتم مراجعة طلبك وتفعيل اشتراكك في أقرب وقت
                            </p>
                            <a href="https://wa.me/2{{ $supportPhone }}" target="_blank"
                                class="btn btn-sm btn-outline-success">
                                <i class="bi bi-whatsapp me-1"></i>للاستفسار: {{ $supportPhone }}
                            </a>
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

                            <input type="file" id="receiptInput" name="receipt"
                                accept="image/jpeg,image/png,image/webp" class="d-none" required>

                            @error('receipt')
                                <div class="text-danger small mt-2">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror

                            <button type="submit" class="btn btn-success w-100 mt-3 py-2" id="submitBtn" disabled>
                                <i class="bi bi-send me-1"></i>إرسال طلب التجديد
                            </button>
                        </form>
                    @endif
                </div>
            @elseif ($subscription && $subscription->isActive() && $subscription->isTrial())
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
                            @php
                                $trialTimeRemaining = $subscription->detailedTimeRemaining();
                            @endphp
                            <div class="trial-countdown mb-3" id="trial-timer-container"
                                data-ends-at="{{ $subscription->ends_at->toIso8601String() }}">
                                <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="bi bi-calendar-day me-1"></i><span
                                            id="trial-timer-days">{{ $trialTimeRemaining['days'] }}</span> يوم
                                    </span>
                                    <span class="badge bg-primary px-3 py-2">
                                        <i class="bi bi-clock me-1"></i><span
                                            id="trial-timer-hours">{{ $trialTimeRemaining['hours'] }}</span> ساعة
                                    </span>
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="bi bi-stopwatch me-1"></i><span
                                            id="trial-timer-minutes">{{ $trialTimeRemaining['minutes'] }}</span> دقيقة
                                    </span>
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-stopwatch-fill me-1"></i><span
                                            id="trial-timer-seconds">{{ $trialTimeRemaining['seconds'] ?? 0 }}</span>
                                        ثانية
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    تنتهي بتاريخ: {{ $subscription->ends_at->format('Y/m/d - H:i') }}
                                </small>
                            </div>

                            {{-- Subscription Info Preview --}}
                            <div class="subscription-preview mt-3">
                                <div class="alert alert-light border mb-0 text-start">
                                    <h6 class="mb-2"><i class="bi bi-info-circle text-success me-1"></i>معلومات
                                        الاشتراك:
                                    </h6>
                                    <ul class="mb-0 pe-3 small">
                                        <li><strong>سعر الاشتراك:</strong> {{ number_format($subscriptionPrice) }} جنيه /
                                            شهر</li>
                                        <li><strong>طريقة الدفع:</strong> فودافون كاش أو إنستاباي</li>
                                        <li><strong>التفعيل:</strong> يتم مراجعة طلبك وتفعيل الاشتراك خلال ساعات</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($subscription && $subscription->isActive() && $subscription->isPaid())
                {{-- Active Paid Subscription - Show Thank You Message --}}
                <div class="payment-section">
                    <div class="trial-notice"
                        style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-color: #28a745;">
                        <div class="text-center py-4">
                            <div class="trial-notice-icon"
                                style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <h5 class="mt-3 mb-2" style="color: #155724;">اشتراكك نشط</h5>
                            <p class="text-muted mb-3">
                                شكراً لاشتراكك معنا! استمتع بجميع مميزات النظام.
                                <br>
                                سيتم إشعارك قبل انتهاء الاشتراك لتجديده.
                            </p>
                            @php
                                $paidTimeRemaining = $subscription->detailedTimeRemaining();
                            @endphp
                            <div class="trial-countdown mb-3">
                                <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-calendar-day me-1"></i>{{ $paidTimeRemaining['days'] }} يوم
                                    </span>
                                    <span class="badge bg-primary px-3 py-2">
                                        <i class="bi bi-clock me-1"></i>{{ $paidTimeRemaining['hours'] }} ساعة
                                    </span>
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="bi bi-stopwatch me-1"></i>{{ $paidTimeRemaining['minutes'] }} دقيقة
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    ينتهي بتاريخ: {{ $subscription->ends_at->format('Y/m/d - H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Show Payment Section - Trial/Paid expired or no subscription --}}
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
                    @php
                        $hasVodafone2 = !empty($vodafoneCashNumber) && $vodafoneCashNumber !== '01XXXXXXXXX';
                        $hasInstapay2 = !empty($instapayNumber);
                    @endphp
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-info-circle fs-5"></i>
                            <strong>خطوات الاشتراك:</strong>
                        </div>
                        <ol class="mb-0 pe-4">
                            <li>حوّل المبلغ <strong>({{ number_format($subscriptionPrice) }} جنيه)</strong> على أحد أرقام
                                الدفع أدناه</li>
                            <li>بعد التحويل، ارفع صورة الوصل من هنا</li>
                            <li>انتظر مراجعة الطلب وتفعيل اشتراكك</li>
                        </ol>
                    </div>

                    {{-- Payment Method Tabs --}}
                    @if ($hasVodafone2 || $hasInstapay2)
                        @if ($hasVodafone2 && $hasInstapay2)
                            <div class="payment-methods-tabs">
                                <div class="payment-tab vodafone active" onclick="selectPaymentMethod('vodafone')">
                                    <i class="bi bi-phone text-danger"></i>
                                    <span>فودافون كاش</span>
                                </div>
                                <div class="payment-tab instapay" onclick="selectPaymentMethod('instapay')">
                                    <i class="bi bi-bank text-primary"></i>
                                    <span>إنستاباي</span>
                                </div>
                            </div>
                        @endif

                        {{-- Vodafone Cash Details --}}
                        @if ($hasVodafone2)
                            <div id="vodafone-details" class="{{ $hasVodafone2 ? '' : 'd-none' }}">
                                <div class="vodafone-number">
                                    <div>
                                        <small class="d-block opacity-75">رقم فودافون كاش</small>
                                        <span class="number" id="vodafoneNumber">{{ $vodafoneCashNumber }}</span>
                                    </div>
                                    <button type="button" class="copy-btn" onclick="copyNumber('vodafoneNumber')">
                                        <i class="bi bi-clipboard me-1"></i>نسخ
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- InstaPay Details --}}
                        @if ($hasInstapay2)
                            <div id="instapay-details" class="{{ !$hasVodafone2 ? '' : 'd-none' }}">
                                <div class="instapay-number">
                                    <div>
                                        <small class="d-block opacity-75">رقم إنستاباي</small>
                                        <span class="number" id="instapayNumber">{{ $instapayNumber }}</span>
                                    </div>
                                    <button type="button" class="copy-btn" onclick="copyNumber('instapayNumber')">
                                        <i class="bi bi-clipboard me-1"></i>نسخ
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if ($pendingRequest)
                        {{-- Pending Request Display --}}
                        <div class="pending-alert">
                            <i class="bi bi-hourglass-split d-block"></i>
                            <h6>طلبك قيد المراجعة</h6>
                            <p class="text-muted small mb-2">
                                تم إرسال طلبك بتاريخ {{ $pendingRequest->created_at->format('Y/m/d - H:i') }}
                            </p>
                            <p class="mb-2 small">
                                <i class="bi bi-clock me-1"></i>سيتم مراجعة طلبك وتفعيل اشتراكك في أقرب وقت
                            </p>
                            <a href="https://wa.me/2{{ $supportPhone }}" target="_blank"
                                class="btn btn-sm btn-outline-success">
                                <i class="bi bi-whatsapp me-1"></i>للاستفسار: {{ $supportPhone }}
                            </a>
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

                            <input type="file" id="receiptInput" name="receipt"
                                accept="image/jpeg,image/png,image/webp" class="d-none" required>

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
        document.addEventListener('DOMContentLoaded', function() {
            /**
             * Update Timer Logic
             */
            function updateTimer(containerId, daysId, hoursId, minutesId, secondsId) {
                const container = document.getElementById(containerId);
                if (!container) return;

                const endsAtStr = container.getAttribute('data-ends-at');
                if (!endsAtStr) return;

                const endsAt = new Date(endsAtStr).getTime();
                const now = new Date().getTime();
                const distance = endsAt - now;

                if (distance < 0) {
                    // Expired - set all to 0
                    const ids = [daysId, hoursId, minutesId, secondsId];
                    ids.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = "0";
                    });
                    return;
                }

                // Calculate time components
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update DOM
                const daysEl = document.getElementById(daysId);
                const hoursEl = document.getElementById(hoursId);
                const minutesEl = document.getElementById(minutesId);
                const secondsEl = document.getElementById(secondsId);

                if (daysEl) daysEl.textContent = days;
                if (hoursEl) hoursEl.textContent = hours;
                if (minutesEl) minutesEl.textContent = minutes;
                if (secondsEl) secondsEl.textContent = seconds;
            }

            /**
             * Update Progress Bar
             */
            function updateProgressBar() {
                const container = document.getElementById('paid-timer-container');
                if (!container) return; // Only for paid subscription

                const startsAtStr = container.getAttribute('data-starts-at');
                const endsAtStr = container.getAttribute('data-ends-at');

                if (!startsAtStr || !endsAtStr) return;

                const startsAt = new Date(startsAtStr).getTime();
                const endsAt = new Date(endsAtStr).getTime();
                const now = new Date().getTime();

                const totalDuration = endsAt - startsAt;
                const timeRemaining = endsAt - now;

                if (totalDuration <= 0) return;

                let percentage = Math.round((timeRemaining / totalDuration) * 100);

                // Cap percentage between 0 and 100
                if (percentage < 0) percentage = 0;
                if (percentage > 100) percentage = 100;

                const textEl = document.getElementById('paid-progress-text');
                const fillEl = document.getElementById('paid-progress-fill');

                if (textEl) textEl.textContent = percentage + '%';
                if (fillEl) {
                    fillEl.style.width = percentage + '%';

                    // Update color class dynamically
                    fillEl.classList.remove('critical', 'low', 'paid');
                    if (percentage <= 20) {
                        fillEl.classList.add('critical');
                    } else if (percentage <= 40) {
                        fillEl.classList.add('low');
                    } else {
                        fillEl.classList.add('paid');
                    }
                }
            }

            // Initial Run
            updateTimer('paid-timer-container', 'paid-timer-days', 'paid-timer-hours', 'paid-timer-minutes',
                'paid-timer-seconds');
            updateProgressBar();
            updateTimer('trial-timer-container', 'trial-timer-days', 'trial-timer-hours', 'trial-timer-minutes',
                'trial-timer-seconds');

            // Set Interval (every 1 second)
            setInterval(function() {
                updateTimer('paid-timer-container', 'paid-timer-days', 'paid-timer-hours',
                    'paid-timer-minutes', 'paid-timer-seconds');
                updateProgressBar();
                updateTimer('trial-timer-container', 'trial-timer-days', 'trial-timer-hours',
                    'trial-timer-minutes', 'trial-timer-seconds');
            }, 1000);
        });
    </script>
@endpush

@push('scripts')
    <script>
        // Copy number function - now accepts element ID
        function copyNumber(elementId) {
            const element = document.getElementById(elementId);
            if (!element) return;

            const number = element.textContent;
            navigator.clipboard.writeText(number).then(() => {
                const btn = element.closest('.vodafone-number, .instapay-number').querySelector('.copy-btn');
                btn.innerHTML = '<i class="bi bi-check me-1"></i>تم النسخ';
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>نسخ';
                }, 2000);
            });
        }

        // Payment method tab switching
        function selectPaymentMethod(method) {
            // Update tabs - remove active from all, add to selected
            document.querySelectorAll('.payment-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.payment-tab.' + method).forEach(tab => tab.classList.add('active'));

            // Hide all payment details
            document.querySelectorAll('#vodafone-details').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('#instapay-details').forEach(el => el.classList.add('d-none'));

            // Show selected payment method details
            document.querySelectorAll('#' + method + '-details').forEach(el => el.classList.remove('d-none'));
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
