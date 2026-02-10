@extends('layouts.app')

@section('title', 'تعيين كلمة مرور جديدة')

@section('content')
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-key-fill text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">تعيين كلمة مرور جديدة</h4>
                    <p class="text-muted">أدخل كلمة المرور الجديدة لحسابك</p>
                </div>

                <!-- Error Messages -->
                @if (session('error'))
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('password.update-forgot') }}" method="POST" id="resetPasswordForm">
                    @csrf

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">كلمة المرور الجديدة</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock-fill text-success"></i>
                            </span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" placeholder="أدخل كلمة المرور الجديدة (6 أحرف على الأقل)"
                                minlength="6" required autofocus>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">تأكيد كلمة المرور</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock-fill text-success"></i>
                            </span>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" placeholder="أعد إدخال كلمة المرور الجديدة" minlength="6"
                                required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="password_confirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="confirmError" class="text-danger small mt-1" style="display: none;">
                            كلمة المرور غير متطابقة
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-whatsapp btn-lg" id="submitBtn">
                            <i class="bi bi-check-lg me-2"></i>تغيير كلمة المرور
                        </button>
                    </div>
                </form>
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
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(function(button) {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            });

            // Validate password confirmation
            const form = document.getElementById('resetPasswordForm');
            const password = document.getElementById('password');
            const confirmation = document.getElementById('password_confirmation');
            const confirmError = document.getElementById('confirmError');

            form.addEventListener('submit', function(e) {
                if (password.value !== confirmation.value) {
                    e.preventDefault();
                    confirmError.style.display = 'block';
                    confirmation.focus();
                } else {
                    confirmError.style.display = 'none';
                }
            });

            // Hide error on typing
            confirmation.addEventListener('input', function() {
                if (password.value === confirmation.value) {
                    confirmError.style.display = 'none';
                }
            });
        });
    </script>
@endpush
