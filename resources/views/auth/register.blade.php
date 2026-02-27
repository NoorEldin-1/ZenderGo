@extends('layouts.app')

@section('title', 'إنشاء حساب جديد')

@section('content')
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">إنشاء حساب جديد</h4>
                    <p class="text-muted" id="stepDescription">أدخل كلمة المرور الخاصة بك</p>
                </div>

                <!-- Info Messages -->
                @if (session('info'))
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                    </div>
                @endif

                <!-- Step 1: Password Form -->
                <div id="passwordSection">
                    <div class="alert alert-light border mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-shield-lock me-2"></i>أمان الحساب</h6>
                        <p class="mb-0 small text-muted">أنشئ كلمة مرور قوية لحماية حسابك. ستحتاجها لتسجيل الدخول.</p>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock text-success"></i>
                            </span>
                            <input type="password" class="form-control" id="password"
                                placeholder="أدخل كلمة المرور (6 أحرف على الأقل)" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="passwordError" class="text-danger small mt-1" style="display: none;"></div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">تأكيد كلمة المرور</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock-fill text-success"></i>
                            </span>
                            <input type="password" class="form-control" id="password_confirmation"
                                placeholder="أعد إدخال كلمة المرور" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="confirmError" class="text-danger small mt-1" style="display: none;"></div>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-whatsapp btn-lg" id="proceedToQrBtn">
                            <i class="bi bi-arrow-left me-2"></i>متابعة لربط WhatsApp
                        </button>
                    </div>
                </div>

                <!-- Step 2: Phone Number Input Section (hidden initially) -->
                <div id="instructionsSection" style="display: none;">
                    <!-- Pairing Code Instructions -->
                    <div id="codeInstructions" class="alert alert-light border mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-phone me-2"></i>الربط برقم الهاتف:</h6>
                        <p class="mb-2 small">أدخل رقم WhatsApp الخاص بك وسنرسل لك كود للربط.</p>
                        <div class="mb-3">
                            <label for="phoneNumber" class="form-label small fw-semibold">رقم الهاتف</label>
                            <div dir="ltr">
                                <input type="tel" class="form-control form-control-lg" id="phoneNumber" inputmode="tel"
                                    autocomplete="tel" style="width:100%">
                            </div>
                            <div class="form-text mt-2 text-end small">أدخل الرقم كاملاً متضمناً رمز الدولة</div>
                        </div>
                        <ol class="mb-0 small text-muted">
                            <li>أدخل رقمك واضغط "الحصول على الكود"</li>
                            <li>افتح WhatsApp على هاتفك</li>
                            <li>اذهب إلى: الإعدادات ← الأجهزة المرتبطة ← ربط جهاز</li>
                            <li>اختر "الربط برقم الهاتف" بدلاً من مسح QR</li>
                            <li>أدخل الكود المعروض</li>
                        </ol>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-whatsapp btn-lg" id="startBtn">
                            <i class="bi bi-key me-2"></i>الحصول على الكود
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="backToPasswordBtn">
                            <i class="bi bi-arrow-right me-2"></i>العودة لتغيير كلمة المرور
                        </button>
                    </div>
                </div>



                <!-- Pairing Code Display Container -->
                <div id="pairingCodeContainer" class="text-center mb-4" style="display: none;">
                    <div class="p-4 bg-light rounded-3 d-inline-block">
                        <p class="text-muted mb-2 small">أدخل هذا الكود في WhatsApp:</p>
                        <div id="pairingCodeDisplay" class="fw-bold text-success"
                            style="font-size: 2.5rem; letter-spacing: 0.3rem; font-family: monospace;">
                            --------
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 text-start small">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>الخطوات:</strong>
                        <ol class="mb-0 mt-2">
                            <li>افتح WhatsApp على هاتفك</li>
                            <li>اذهب إلى: الإعدادات ← الأجهزة المرتبطة ← ربط جهاز</li>
                            <li>اضغط على "الربط برقم الهاتف بدلاً من ذلك"</li>
                            <li>أدخل الكود أعلاه</li>
                        </ol>
                    </div>
                    <div class="spinner-border spinner-border-sm text-success mt-3" role="status">
                        <span class="visually-hidden">جاري الانتظار...</span>
                    </div>
                    <p class="text-muted small mt-2">جاري انتظار إدخال الكود...</p>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="text-center mb-4" style="display: none;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0" id="loadingText">جاري بدء الجلسة...</p>
                </div>

                <!-- Success State -->
                <div id="successState" class="text-center mb-4" style="display: none;">
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                        <span id="successMessage">تم التسجيل بنجاح!</span>
                    </div>
                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                    <p class="text-muted small">جاري تحويلك...</p>
                </div>

                <!-- Error Container -->
                <div id="errorContainer" class="alert alert-danger mt-3" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="errorMessage"></span>
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-block" id="retryBtn">
                        <i class="bi bi-arrow-clockwise me-1"></i>إعادة المحاولة
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-muted small mt-4">
            لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-success">تسجيل الدخول</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const passwordSection = document.getElementById('passwordSection');
            const instructionsSection = document.getElementById('instructionsSection');
            const proceedToQrBtn = document.getElementById('proceedToQrBtn');
            const backToPasswordBtn = document.getElementById('backToPasswordBtn');
            const startBtn = document.getElementById('startBtn');
            const retryBtn = document.getElementById('retryBtn');
            const loadingState = document.getElementById('loadingState');
            const loadingText = document.getElementById('loadingText');
            const successState = document.getElementById('successState');
            const successMessage = document.getElementById('successMessage');
            const errorContainer = document.getElementById('errorContainer');
            const errorMessage = document.getElementById('errorMessage');
            const stepDescription = document.getElementById('stepDescription');

            // Password fields
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const passwordError = document.getElementById('passwordError');
            const confirmError = document.getElementById('confirmError');
            const togglePassword = document.getElementById('togglePassword');
            const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');

            // Pairing code elements
            const pairingCodeContainer = document.getElementById('pairingCodeContainer');
            const pairingCodeDisplay = document.getElementById('pairingCodeDisplay');
            const phoneNumberInput = document.getElementById('phoneNumber');
            const codeInstructions = document.getElementById('codeInstructions');

            // Initialize intl-tel-input
            let iti = null;
            if (phoneNumberInput) {
                iti = window.intlTelInput(phoneNumberInput, {
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

            let statusCheckInterval = null;
            let registrationPassword = '';
            const selectedMethod = 'code'; // Always use pairing code method

            // Toggle password visibility
            function setupPasswordToggle(button, input) {
                button.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('bi-eye');
                    this.querySelector('i').classList.toggle('bi-eye-slash');
                });
            }

            setupPasswordToggle(togglePassword, passwordInput);
            setupPasswordToggle(togglePasswordConfirm, passwordConfirmInput);

            // Validate passwords
            function validatePasswords() {
                let valid = true;
                passwordError.style.display = 'none';
                confirmError.style.display = 'none';

                if (passwordInput.value.length < 6) {
                    passwordError.textContent = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                    passwordError.style.display = 'block';
                    valid = false;
                }

                if (passwordInput.value !== passwordConfirmInput.value) {
                    confirmError.textContent = 'كلمة المرور غير متطابقة';
                    confirmError.style.display = 'block';
                    valid = false;
                }

                return valid;
            }

            // Step 1 -> Step 2: Validate password and proceed
            proceedToQrBtn.addEventListener('click', function() {
                if (validatePasswords()) {
                    registrationPassword = passwordInput.value;
                    passwordSection.style.display = 'none';
                    instructionsSection.style.display = 'block';
                    stepDescription.textContent = 'اربط حساب WhatsApp الخاص بك';
                }
            });

            // Back to password step
            backToPasswordBtn.addEventListener('click', function() {
                instructionsSection.style.display = 'none';
                passwordSection.style.display = 'block';
                stepDescription.textContent = 'أدخل كلمة المرور الخاصة بك';
            });

            function showError(message) {
                errorMessage.textContent = message;
                errorContainer.style.display = 'block';
                loadingState.style.display = 'none';
                pairingCodeContainer.style.display = 'none';
            }

            function hideError() {
                errorContainer.style.display = 'none';
            }

            function showLoading(text) {
                loadingText.textContent = text;
                loadingState.style.display = 'block';
                instructionsSection.style.display = 'none';
                passwordSection.style.display = 'none';
                pairingCodeContainer.style.display = 'none';
                hideError();
            }

            function showPairingCode(code) {
                // Format code with dash in middle for readability (e.g., "1234-5678")
                const formattedCode = code.length === 8 ?
                    code.slice(0, 4) + '-' + code.slice(4) :
                    code;
                pairingCodeDisplay.textContent = formattedCode;
                pairingCodeContainer.style.display = 'block';
                loadingState.style.display = 'none';
                instructionsSection.style.display = 'none';
                passwordSection.style.display = 'none';
                startStatusCheck();
            }

            function showSuccess(message) {
                successMessage.textContent = message;
                successState.style.display = 'block';
                pairingCodeContainer.style.display = 'none';
                loadingState.style.display = 'none';
                passwordSection.style.display = 'none';
                instructionsSection.style.display = 'none';
                if (statusCheckInterval) clearInterval(statusCheckInterval);
            }

            async function startRegistration() {
                // Validate phone number
                if (!phoneNumberInput.value.trim()) {
                    showError('يرجى إدخال رقم الهاتف');
                    return;
                }
                const fullPhoneNumber = iti ? iti.getNumber() : phoneNumberInput.value.trim();

                showLoading('جاري الحصول على كود الربط...');

                try {
                    const requestBody = {
                        password: registrationPassword,
                        password_confirmation: registrationPassword,
                        method: 'code',
                        phone: fullPhoneNumber
                    };

                    const response = await fetch('{{ route('register.start') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(requestBody)
                    });

                    const data = await response.json();
                    console.log('Start registration response:', data);

                    // Handle pairing code response
                    if (data.pairingCode) {
                        showPairingCode(data.pairingCode);
                    } else if (data.status === 'CONNECTED') {
                        showSuccess('تم الربط بنجاح!');
                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 1500);
                        }
                    } else if (data.message) {
                        showError(data.message);
                    } else {
                        showError('حدث خطأ. يرجى المحاولة مرة أخرى.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showError('حدث خطأ في الاتصال. تأكد من تشغيل خادم WhatsApp.');
                }
            }

            function startStatusCheck() {
                if (statusCheckInterval) clearInterval(statusCheckInterval);

                statusCheckInterval = setInterval(async () => {
                    try {
                        const response = await fetch('{{ route('register.check') }}', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        console.log('Registration check:', data);

                        if (data.connected) {
                            clearInterval(statusCheckInterval);
                            showSuccess(data.message || 'تم التسجيل بنجاح!');
                            if (data.redirect) {
                                setTimeout(() => window.location.href = data.redirect, 1500);
                            }
                        }
                    } catch (error) {
                        console.error('Status check error:', error);
                    }
                }, 3000);
            }

            // Event Listeners
            startBtn.addEventListener('click', startRegistration);
            retryBtn.addEventListener('click', function() {
                instructionsSection.style.display = 'block';
                hideError();
            });

            // Cleanup
            window.addEventListener('beforeunload', function() {
                if (statusCheckInterval) clearInterval(statusCheckInterval);
            });
        });
    </script>
@endpush

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

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        #passwordSection,
        #instructionsSection {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
@endpush
