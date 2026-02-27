@extends('layouts.app')

@section('title', 'استعادة كلمة المرور')

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
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">استعادة كلمة المرور</h4>
                    <p class="text-muted">أدخل رقم هاتفك المسجل لاستعادة كلمة المرور</p>
                </div>

                <!-- Messages -->
                @if (session('error'))
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                <!-- Step 1: Phone Input -->
                <div id="phoneStep">
                    <div class="mb-4">
                        <label for="phone" class="form-label fw-semibold">رقم الهاتف</label>
                        <div dir="ltr">
                            <input type="tel" class="form-control form-control-lg" id="phone" name="phone"
                                inputmode="tel" autocomplete="tel" required autofocus style="width:100%">
                        </div>
                        <div class="form-text mt-2 text-end">أدخل رقم الهاتف متضمناً رمز الدولة</div>
                    </div>

                    <!-- Instructions -->
                    <div class="alert alert-info mb-4" style="font-size: 0.9rem;">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>كيف تعمل هذه الخدمة؟</strong>
                        <ul class="mb-0 mt-2" style="padding-right: 1.2rem;">
                            <li>سيتم إرسال كود مكون من <strong>6 أرقام</strong> إلى رقم الواتساب الخاص بك.</li>
                            <li>الكود سيصلك من رقم الدعم الفني: <strong dir="ltr">{{ $supportPhone }}</strong></li>
                            <li>الكود صالح لمدة <strong>15 دقيقة</strong> فقط.</li>
                        </ul>
                    </div>

                    <!-- Error Message Area -->
                    <div id="sendError" class="alert alert-danger mb-3" style="display: none;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <span id="sendErrorText"></span>
                                <div id="contactSupportBlock" class="mt-3" style="display: none;">
                                    <hr class="my-2 opacity-25">
                                    <p class="mb-2 small opacity-75">يمكنك التواصل مع الدعم الفني مباشرة:</p>
                                    <a id="contactSupportBtn" href="#" target="_blank" rel="noopener noreferrer"
                                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-headset"></i>
                                        تواصل مع الدعم
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-whatsapp btn-lg" id="sendOtpBtn">
                            <i class="bi bi-send me-2"></i>إرسال كود التحقق
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loadingState" style="display: none;" class="text-center py-4">
                    <div class="spinner-border text-success mb-3" role="status">
                        <span class="visually-hidden">جاري الإرسال...</span>
                    </div>
                    <p class="text-muted">جاري إرسال كود التحقق إلى الواتساب...</p>
                </div>
            </div>
        </div>

        <p class="text-center text-muted small mt-4">
            تذكرت كلمة المرور؟ <a href="{{ route('login') }}" class="text-success fw-semibold">تسجيل الدخول</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');

            // Initialize intl-tel-input
            let iti = null;
            if (phoneInput) {
                iti = window.intlTelInput(phoneInput, {
                    initialCountry: "eg",
                    preferredCountries: ["eg", "sa", "ae", "kw", "qa", "bh", "om", "jo", "lb", "sy", "iq",
                        "sd", "ye", "dz", "ma", "tn", "ly"
                    ],
                    separateDialCode: true,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js",
                    nationalMode: false,
                    dropdownContainer: document.body
                });
            }
            const sendOtpBtn = document.getElementById('sendOtpBtn');
            const sendError = document.getElementById('sendError');
            const sendErrorText = document.getElementById('sendErrorText');
            const contactSupportBlock = document.getElementById('contactSupportBlock');
            const contactSupportBtn = document.getElementById('contactSupportBtn');
            const supportPhone = '{{ $supportPhone }}';

            function showError(message, showContactSupport) {
                sendError.style.display = 'block';
                sendErrorText.textContent = message;

                if (showContactSupport) {
                    const whatsappUrl = 'https://wa.me/2' + supportPhone + '?text=' +
                        encodeURIComponent('مرحباً، أحتاج مساعدة في استعادة كلمة المرور.');
                    contactSupportBtn.href = whatsappUrl;
                    contactSupportBlock.style.display = 'block';
                } else {
                    contactSupportBlock.style.display = 'none';
                }

                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = '<i class="bi bi-send me-2"></i>إرسال كود التحقق';
            }

            sendOtpBtn.addEventListener('click', function() {
                const phone = iti ? iti.getNumber() : phoneInput.value.trim();

                if (!phone || phone.length < 8) {
                    showError('يرجى إدخال رقم هاتف صحيح متضمناً رمز الدولة.', false);
                    return;
                }

                // Show loading
                sendError.style.display = 'none';
                sendOtpBtn.disabled = true;
                sendOtpBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>جاري الإرسال...';

                fetch('{{ route('password.send-otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            phone: phone
                        }),
                    })
                    .then(response => response.json().then(data => ({
                        status: response.status,
                        data
                    })))
                    .then(({
                        status,
                        data
                    }) => {
                        if (data.success) {
                            window.location.href = '{{ route('password.verify') }}';
                        } else {
                            // Show contact support for service unavailability (503) or server errors (500)
                            const isServiceError = (status === 503 || status === 500);
                            showError(data.message || 'حدث خطأ. حاول مرة أخرى.', isServiceError);
                        }
                    })
                    .catch(error => {
                        showError('حدث خطأ في الاتصال. حاول مرة أخرى.', true);
                    });
            });

            // Allow Enter key to submit
            phoneInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendOtpBtn.click();
                }
            });
        });
    </script>
@endpush
