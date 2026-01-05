@extends('layouts.app')

@section('title', 'إعدادات النظام')

@push('styles')
    <style>
        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .settings-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .settings-header-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .setting-group {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .setting-group:hover {
            background: #f0f5f1;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.1);
        }

        .setting-group .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .setting-group .form-control {
            font-size: 1.1rem;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .setting-group .form-control:focus {
            border-color: #25D366;
            box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.1);
        }

        .setting-label {
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.5rem;
        }

        .setting-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .input-group-text {
            background: #25D366;
            border-color: #25D366;
            color: white;
            font-weight: 600;
        }

        .btn-save {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            border: none;
            padding: 0.9rem 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.35);
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="mb-0">
            <i class="bi bi-gear text-success ms-2"></i>إعدادات النظام
        </h4>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right ms-1"></i>العودة للوحة التحكم
            </a>
            <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                    <i class="bi bi-box-arrow-right ms-1"></i>خروج
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle ms-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="settings-card">
        <div class="settings-header">
            <div class="settings-header-icon">
                <i class="bi bi-sliders"></i>
            </div>
            <div>
                <h5 class="mb-1">إعدادات الاشتراكات</h5>
                <p class="text-muted mb-0 small">تحكم في الفترة التجريبية وأسعار الاشتراك</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="setting-group">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="setting-label">أيام الفترة التجريبية</div>
                        <div class="setting-description">
                            عدد الأيام التي يحصل عليها المستخدم الجديد مجاناً عند التسجيل
                        </div>
                        <div class="input-group" style="max-width: 250px;">
                            <input type="number" name="trial_days"
                                class="form-control @error('trial_days') is-invalid @enderror"
                                value="{{ old('trial_days', $settings['trial_days']) }}" min="1" max="365"
                                required>
                            <span class="input-group-text">يوم</span>
                        </div>
                        @error('trial_days')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="setting-label">سعر الاشتراك الشهري</div>
                        <div class="setting-description">
                            قيمة الاشتراك الشهري الذي يدفعه المستخدم بعد انتهاء الفترة التجريبية
                        </div>
                        <div class="input-group" style="max-width: 250px;">
                            <input type="number" name="subscription_price"
                                class="form-control @error('subscription_price') is-invalid @enderror"
                                value="{{ old('subscription_price', $settings['subscription_price']) }}" min="0"
                                step="0.01" required>
                            <span class="input-group-text">جنيه</span>
                        </div>
                        @error('subscription_price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="setting-label">رقم فودافون كاش</div>
                        <div class="setting-description">
                            الرقم الذي سيظهر للمستخدمين لتحويل قيمة الاشتراك عليه
                        </div>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text" style="background: #e60000; border-color: #e60000;">
                                <i class="bi bi-phone text-white"></i>
                            </span>
                            <input type="text" name="vodafone_cash_number"
                                class="form-control @error('vodafone_cash_number') is-invalid @enderror"
                                value="{{ old('vodafone_cash_number', $settings['vodafone_cash_number']) }}"
                                placeholder="01XXXXXXXXX" dir="ltr" required>
                        </div>
                        @error('vodafone_cash_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="setting-label">رقم الدعم الفني</div>
                        <div class="setting-description">
                            رقم WhatsApp للدعم الفني الذي سيظهر للمستخدمين في رسائل الأخطاء وزر الدعم العائم
                        </div>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text">
                                <i class="bi bi-whatsapp text-white"></i>
                            </span>
                            <input type="text" name="support_phone_number"
                                class="form-control @error('support_phone_number') is-invalid @enderror"
                                value="{{ old('support_phone_number', $settings['support_phone_number']) }}"
                                placeholder="01XXXXXXXXX" dir="ltr" required>
                        </div>
                        @error('support_phone_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end pt-3">
                <button type="submit" class="btn btn-success btn-save">
                    <i class="bi bi-check2-circle ms-2"></i>حفظ الإعدادات
                </button>
            </div>
        </form>
    </div>
@endsection
