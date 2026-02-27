@extends('layouts.app')

@section('title', 'تسجيل الدخول')

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
                    <i class="bi bi-whatsapp text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">مرحباً بك في زندر</h4>
                    <p class="text-muted">أدخل رقم هاتفك للمتابعة</p>
                </div>

                <!-- Info/Error/Success Messages -->
                @if (session('info'))
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if (session('suspension_error'))
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-shield-exclamation me-2"></i>{{ session('suspension_error') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="phone" class="form-label fw-semibold">رقم الهاتف</label>
                        <div dir="ltr">
                            <input type="tel" class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ old('phone') }}" inputmode="tel" autocomplete="tel"
                                required autofocus style="width:100%">
                        </div>
                        <div class="form-text mt-2 text-end">أدخل رقم الهاتف متضمناً رمز الدولة</div>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock text-success"></i>
                            </span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" placeholder="أدخل كلمة المرور" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-whatsapp btn-lg">
                            <i class="bi bi-box-arrow-in-left me-2"></i>تسجيل الدخول
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}" class="text-muted small">
                        <i class="bi bi-question-circle me-1"></i>نسيت كلمة المرور؟
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center text-muted small mt-4">
            ليس لديك حساب؟ <a href="{{ route('register') }}" class="text-success fw-semibold">إنشاء حساب جديد</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('bi-eye');
                    this.querySelector('i').classList.toggle('bi-eye-slash');
                });
            }

            const phoneInput = document.querySelector("#phone");
            if (phoneInput) {
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

                // On form submit, get the full international number and replace the input's value
                const form = phoneInput.closest('form');
                form.addEventListener('submit', function(e) {
                    if (phoneInput.value.trim()) {
                        // Update input value with the full number (including dial code)
                        const fullNumber = iti.getNumber();
                        if (fullNumber) {
                            phoneInput.value = fullNumber;
                        }
                    }
                });
            }
        });
    </script>
@endpush
