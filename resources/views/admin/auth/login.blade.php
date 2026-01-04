@extends('layouts.app')

@section('title', 'تسجيل دخول المسؤول')

@section('content')
    <div class="auth-card">
        <div class="card shadow-lg">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%);">
                        <i class="bi bi-shield-lock fs-1 text-white"></i>
                    </div>
                    <h4 class="mb-1">لوحة الإدارة</h4>
                    <p class="text-muted mb-0">تسجيل دخول المسؤولين</p>
                </div>

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control form-control-lg @error('username') is-invalid @enderror"
                            id="username" name="username" value="{{ old('username') }}" placeholder="admin" required
                            autofocus>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="••••••••" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                تذكرني
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-whatsapp btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل الدخول
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-right-short"></i>
                العودة للموقع الرئيسي
            </a>
        </div>
    </div>
@endsection
