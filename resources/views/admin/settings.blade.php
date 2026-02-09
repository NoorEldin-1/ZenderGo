@extends('admin.layouts.app')

@section('title', 'إعدادات النظام')

@push('styles')
    <style>
        .settings-header-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .setting-group {
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .setting-group:hover {
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
            flex-shrink: 0;
        }

        .setting-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .setting-description {
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%);
            border: none;
            padding: 0.9rem 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 12px;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.35);
            color: white;
        }

        /* Custom Input Group Text for branding (Vodafone Red, InstaPay Blue) */
        .input-group-vodafone {
            background: #e60000 !important;
            border-color: #e60000 !important;
            color: white !important;
        }

        .input-group-instapay {
            background: #0066b2 !important;
            border-color: #0066b2 !important;
            color: white !important;
        }

        .input-group-whatsapp {
            background: var(--whatsapp-green) !important;
            border-color: var(--whatsapp-green) !important;
            color: white !important;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="mb-0">
            <i class="bi bi-gear text-success ms-2"></i>إعدادات النظام
        </h4>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="settings-header-icon">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h5 class="mb-1">إعدادات الاشتراكات</h5>
                    <p class="text-muted mb-0 small">تحكم في الفترة التجريبية وأسعار الاشتراك</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Trial Duration --}}
                <div class="setting-group bg-body-tertiary border">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-box bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="setting-label">مدة الفترة التجريبية</div>
                            <div class="setting-description text-muted">
                                المدة التي يحصل عليها المستخدم الجديد مجاناً عند التسجيل
                            </div>
                            <div class="d-flex gap-2 align-items-center flex-wrap" style="max-width: 350px;">
                                <input type="number" name="trial_duration"
                                    class="form-control @error('trial_duration') is-invalid @enderror"
                                    value="{{ old('trial_duration', $settings['trial_duration']) }}" min="1"
                                    max="9999" required style="width: 120px;">
                                <select name="trial_duration_unit"
                                    class="form-select @error('trial_duration_unit') is-invalid @enderror"
                                    style="width: 130px;">
                                    <option value="minutes"
                                        {{ old('trial_duration_unit', $settings['trial_duration_unit']) == 'minutes' ? 'selected' : '' }}>
                                        دقيقة</option>
                                    <option value="hours"
                                        {{ old('trial_duration_unit', $settings['trial_duration_unit']) == 'hours' ? 'selected' : '' }}>
                                        ساعة</option>
                                    <option value="days"
                                        {{ old('trial_duration_unit', $settings['trial_duration_unit']) == 'days' ? 'selected' : '' }}>
                                        يوم</option>
                                </select>
                            </div>
                            @error('trial_duration')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('trial_duration_unit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Subscription Price --}}
                <div class="setting-group bg-body-tertiary border">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="setting-label">سعر الاشتراك الشهري</div>
                            <div class="setting-description text-muted">
                                قيمة الاشتراك الشهري الذي يدفعه المستخدم بعد انتهاء الفترة التجريبية
                            </div>
                            <div class="input-group" style="max-width: 250px;">
                                <input type="number" name="subscription_price"
                                    class="form-control @error('subscription_price') is-invalid @enderror"
                                    value="{{ old('subscription_price', $settings['subscription_price']) }}" min="0"
                                    step="0.01" required>
                                <span class="input-group-text input-group-whatsapp">جنيه</span>
                            </div>
                            @error('subscription_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Vodafone Cash --}}
                <div class="setting-group bg-body-tertiary border">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="setting-label">رقم فودافون كاش</div>
                            <div class="setting-description text-muted">
                                الرقم الذي سيظهر للمستخدمين لتحويل قيمة الاشتراك عليه
                            </div>
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text input-group-vodafone">
                                    <i class="bi bi-phone"></i>
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

                {{-- InstaPay --}}
                <div class="setting-group bg-body-tertiary border">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="setting-label">رقم إنستاباي (InstaPay) <span
                                    class="badge bg-secondary">اختياري</span>
                            </div>
                            <div class="setting-description text-muted">
                                رقم الهاتف أو IPA Handle الخاص بإنستاباي (اتركه فارغاً لإخفائه)
                            </div>
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text input-group-instapay">
                                    <i class="bi bi-bank"></i>
                                </span>
                                <input type="text" name="instapay_number"
                                    class="form-control @error('instapay_number') is-invalid @enderror"
                                    value="{{ old('instapay_number', $settings['instapay_number']) }}"
                                    placeholder="@username أو 01XXXXXXXXX" dir="ltr">
                            </div>
                            @error('instapay_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Support Phone --}}
                <div class="setting-group bg-body-tertiary border">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="setting-label">رقم الدعم الفني</div>
                            <div class="setting-description text-muted">
                                رقم WhatsApp للدعم الفني الذي سيظهر للمستخدمين
                            </div>
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text input-group-whatsapp">
                                    <i class="bi bi-whatsapp"></i>
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

                {{-- Contact Limit --}}
                <div class="setting-group bg-body-tertiary border">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="setting-label">حد جهات الاتصال لكل مستخدم</div>
                            <div class="setting-description text-muted">
                                الحد الأقصى لعدد جهات الاتصال التي يمكن لكل مستخدم إضافتها
                            </div>
                            <div class="input-group" style="max-width: 250px;">
                                <input type="number" name="contact_limit"
                                    class="form-control @error('contact_limit') is-invalid @enderror"
                                    value="{{ old('contact_limit', $settings['contact_limit']) }}" min="10"
                                    max="100000" required>
                                <span class="input-group-text input-group-whatsapp">جهة اتصال</span>
                            </div>
                            @error('contact_limit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Campaign Limit --}}
                <div class="setting-group bg-body-tertiary border">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="bi bi-send"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="setting-label">حد الإرسال للحملة</div>
                            <div class="setting-description text-muted">
                                الحد الأقصى لعدد المستلمين في كل دفعة إرسال (يؤثر أيضاً على عدد جهات الاتصال في كل صفحة)
                            </div>
                            <div class="input-group" style="max-width: 250px;">
                                <input type="number" name="campaign_limit"
                                    class="form-control @error('campaign_limit') is-invalid @enderror"
                                    value="{{ old('campaign_limit', $settings['campaign_limit']) }}" min="1"
                                    max="1000" required>
                                <span class="input-group-text input-group-whatsapp">مستلم</span>
                            </div>
                            @error('campaign_limit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3">
                    <button type="submit" class="btn btn-save">
                        <i class="bi bi-check2-circle ms-2"></i>حفظ الإعدادات
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
