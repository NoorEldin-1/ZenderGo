@extends('layouts.app')

@section('title', 'تأكيد رمز التحقق')

@section('content')
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">أدخل رمز التحقق</h4>
                    <p class="text-muted">
                        تم إرسال رمز مكون من 4 أرقام إلى
                        <br>
                        <strong dir="ltr" class="text-dark">{{ session('phone') }}</strong>
                    </p>
                </div>

                <form action="{{ route('verify') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="otp" class="form-label fw-semibold">رمز التحقق</label>
                        <input type="text"
                            class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                            id="otp" name="otp" maxlength="4" inputmode="numeric" pattern="[0-9]*"
                            placeholder="● ● ● ●" style="letter-spacing: 1rem; font-size: 1.5rem;"
                            autocomplete="one-time-code" required autofocus>
                        @error('otp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-whatsapp btn-lg">
                            <i class="bi bi-check-lg me-2"></i>تأكيد الدخول
                        </button>

                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة لتغيير الرقم
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-muted small mb-2">
                <i class="bi bi-clock me-1"></i>
                الرمز صالح لمدة 5 دقائق
            </p>
            <form action="{{ route('login') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="phone" value="{{ session('phone') }}">
                <button type="submit" class="btn btn-link btn-sm text-success p-0">
                    <i class="bi bi-arrow-repeat me-1"></i>إعادة إرسال الرمز
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-focus and auto-submit on 4 digits
        const otpInput = document.getElementById('otp');
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 4) {
                this.form.submit();
            }
        });
    </script>
@endpush
