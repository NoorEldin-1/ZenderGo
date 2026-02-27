@extends('layouts.app')

@section('title', 'إضافة جهة اتصال')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/css/intlTelInput.css">
    <style>
        .iti {
            width: 100%;
        }

        .iti__flag {
            background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/img/flags.png");
        }

        @media (min-resolution: 2x) {
            .iti__flag {
                background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/img/flags@2x.png");
            }
        }

        .iti__country-list {
            text-align: left;
            direction: ltr;
            z-index: 9999 !important;
        }

        /* Fix for RTL dropdown positioning when appended to body */
        .iti--container .iti__dropdown-content {
            left: 0 !important;
            right: auto !important;
            transform: none !important;
        }

        /* Dark mode support for intl-tel-input */
        [data-bs-theme="dark"] .iti__country-list {
            background-color: #212529 !important;
            color: #e9ecef !important;
            border-color: #495057 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important;
        }

        [data-bs-theme="dark"] .iti__country {
            padding: 8px 10px;
        }

        [data-bs-theme="dark"] .iti__country:hover,
        [data-bs-theme="dark"] .iti__country.iti__highlight {
            background-color: #343a40 !important;
        }

        [data-bs-theme="dark"] .iti__search-input {
            background-color: #1a1d21 !important;
            color: #e9ecef !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .iti__divider {
            border-bottom-color: #495057 !important;
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-right me-1"></i>العودة للقائمة
        </a>
        <h2 class="fw-bold mb-1">إضافة جهة اتصال</h2>
        <p class="text-muted mb-0">أضف جهة اتصال جديدة إلى قائمتك</p>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    @if (!$canAddContact)
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>لا يمكن إضافة جهات اتصال جديدة!</strong>
                            <p class="mb-0 mt-1 small">لقد وصلت للحد الأقصى ({{ number_format($contactLimit) }} جهة اتصال).
                                يرجى حذف بعض جهات الاتصال أولاً.</p>
                        </div>
                    @elseif($remainingSlots <= 10)
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <strong>تنبيه:</strong> متبقي {{ $remainingSlots }} جهة اتصال فقط من الحد الأقصى
                            ({{ number_format($contactLimit) }}).
                        </div>
                    @endif

                    @error('limit')
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            {{ $message }}
                        </div>
                    @enderror

                    <form action="{{ route('contacts.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">
                                الاسم <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" placeholder="اسم جهة الاتصال"
                                    required autofocus {{ !$canAddContact ? 'disabled' : '' }}>
                            </div>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="form-label fw-semibold">
                                رقم الهاتف <span class="text-danger">*</span>
                            </label>
                            <div dir="ltr">
                                <input type="tel"
                                    class="form-control form-control-lg @error('phone') is-invalid @enderror" id="phone"
                                    name="phone" value="{{ old('phone') }}" inputmode="tel" autocomplete="tel" required
                                    style="width:100%" {{ !$canAddContact ? 'disabled' : '' }}>
                            </div>
                            <div class="form-text mt-2 text-end">أدخل رقم الهاتف متضمناً رمز الدولة</div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-primary flex-fill"
                                {{ !$canAddContact ? 'disabled' : '' }}>
                                <i class="bi bi-plus-lg me-1"></i>إضافة جهة الاتصال
                            </button>
                            <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">
                                إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="col-lg-6 mt-4 mt-lg-0">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-lightbulb text-warning me-2"></i>نصائح
                    </h6>
                    <ul class="mb-0 pe-3">
                        <li class="mb-2">أدخل رقم الهاتف متضمناً رمز الدولة</li>
                        <li class="mb-2">تأكد من صحة الرقم قبل الإضافة</li>
                        <li class="mb-2">يمكنك استيراد جهات اتصال متعددة من ملف Excel</li>
                        <li>لا يمكن إضافة رقم موجود مسبقاً</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.querySelector("#phone");
            if (phoneInput && !phoneInput.disabled) {
                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: "eg",
                    preferredCountries: ["eg", "sa", "ae", "kw", "qa", "bh", "om", "jo", "lb", "sy", "iq",
                        "sd", "ye", "dz", "ma", "tn", "ly"
                    ],
                    separateDialCode: true,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js",
                    nationalMode: false,
                    dropdownContainer: document.body
                });

                // On form submit, update input value with the full number
                const form = phoneInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (phoneInput.value.trim()) {
                            const fullNumber = iti.getNumber();
                            if (fullNumber) {
                                phoneInput.value = fullNumber;
                            }
                        }
                    });
                }
            }
        });
    </script>
@endpush
