@extends('layouts.app')

@section('title', 'تأكيد كود التحقق')

@section('content')
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-patch-check text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">أدخل كود التحقق</h4>
                    <p class="text-muted">تم إرسال كود مكون من 6 أرقام إلى واتساب الخاص بك</p>
                </div>

                <!-- Instructions -->
                <div class="alert alert-success mb-4" style="font-size: 0.9rem;">
                    <i class="bi bi-whatsapp me-2"></i>
                    تم إرسال كود التحقق من الرقم <strong dir="ltr">{{ $supportPhone }}</strong>
                    إلى رقمك <strong dir="ltr">{{ $phone }}</strong>.
                    <br>
                    <small class="text-muted mt-1 d-block">تحقق من رسائل الواتساب الخاصة بك.</small>
                </div>

                <!-- OTP Input -->
                <div class="mb-4">
                    <label for="otp" class="form-label fw-semibold">كود التحقق</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-key text-success"></i>
                        </span>
                        <input type="text" class="form-control text-center fw-bold" id="otp" name="otp"
                            placeholder="------" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                            autocomplete="one-time-code" required autofocus
                            style="letter-spacing: 0.5rem; font-size: 1.5rem;">
                    </div>
                    <div class="form-text">أدخل الكود المكون من 6 أرقام الذي وصلك على الواتساب</div>
                </div>

                <!-- Error Message Area -->
                <div id="verifyError" class="alert alert-danger mb-3" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="verifyErrorText"></span>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-whatsapp btn-lg" id="verifyOtpBtn">
                        <i class="bi bi-check-circle me-2"></i>تأكيد الكود
                    </button>
                    <a href="{{ route('password.request') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-2"></i>إعادة إرسال الكود
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center text-muted small mt-4">
            تذكرت كلمة المرور؟ <a href="{{ route('login') }}" class="text-success fw-semibold">تسجيل الدخول</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            const verifyBtn = document.getElementById('verifyOtpBtn');
            const verifyError = document.getElementById('verifyError');
            const verifyErrorText = document.getElementById('verifyErrorText');

            // Only allow numeric input
            otpInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            verifyBtn.addEventListener('click', function() {
                const otp = otpInput.value.trim();

                if (!otp || otp.length !== 6) {
                    verifyError.style.display = 'block';
                    verifyErrorText.textContent = 'يرجى إدخال كود مكون من 6 أرقام.';
                    return;
                }

                // Show loading
                verifyError.style.display = 'none';
                verifyBtn.disabled = true;
                verifyBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>جاري التحقق...';

                fetch('{{ route('password.verify-otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            otp: otp
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
                            // Redirect to reset password page
                            window.location.href = '{{ route('password.reset-form') }}';
                        } else {
                            verifyError.style.display = 'block';
                            verifyErrorText.textContent = data.message || 'الكود غير صحيح.';
                            verifyBtn.disabled = false;
                            verifyBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الكود';
                        }
                    })
                    .catch(error => {
                        verifyError.style.display = 'block';
                        verifyErrorText.textContent = 'حدث خطأ في الاتصال. حاول مرة أخرى.';
                        verifyBtn.disabled = false;
                        verifyBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الكود';
                    });
            });

            // Allow Enter key to submit
            otpInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    verifyBtn.click();
                }
            });

            // Auto-submit when 6 digits entered
            otpInput.addEventListener('input', function() {
                if (this.value.length === 6) {
                    verifyBtn.click();
                }
            });
        });
    </script>
@endpush
