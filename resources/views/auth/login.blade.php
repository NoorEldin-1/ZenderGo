@extends('layouts.app')

@section('title', 'تسجيل الدخول')

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
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-phone text-success"></i>
                            </span>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ old('phone') }}" placeholder="01012345678" inputmode="numeric"
                                pattern="[0-9]*" autocomplete="tel" required autofocus>
                        </div>
                        <div class="form-text">أدخل رقم الهاتف المصري (مثال: 01012345678)</div>
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
        });
    </script>
@endpush
