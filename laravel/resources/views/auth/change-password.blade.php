@extends('layouts.app')

@section('title', 'تغيير كلمة المرور')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-key me-2 text-success"></i>تغيير كلمة المرور
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST" id="changePasswordForm">
                        @csrf

                        <!-- Current Password -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold">كلمة المرور الحالية</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" placeholder="أدخل كلمة المرور الحالية"
                                    required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">كلمة المرور الجديدة</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock-fill text-success"></i>
                                </span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="أدخل كلمة المرور الجديدة (6 أحرف على الأقل)"
                                    minlength="6" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">تأكيد كلمة المرور
                                الجديدة</label>
                            <div class="input-group">
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
                            <div id="confirmError" class="text-danger small mt-1" style="display: none;">كلمة المرور غير
                                متطابقة</div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-whatsapp btn-lg" id="submitBtn">
                                <i class="bi bi-check-lg me-2"></i>تغيير كلمة المرور
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>العودة للوحة التحكم
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
            const form = document.getElementById('changePasswordForm');
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
