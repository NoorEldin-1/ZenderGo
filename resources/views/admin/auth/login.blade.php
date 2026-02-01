@extends('admin.layouts.app')

@section('title', 'تسجيل دخول المسؤول')

@push('styles')
    <style>
        .admin-login-wrapper {
            min-height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }

        .admin-login-card {
            width: 100%;
            max-width: 420px;
        }

        .admin-login-card .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
        }

        .admin-login-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }

        .admin-login-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .admin-login-card .form-control {
            padding: 0.85rem 1rem;
            font-size: 1rem;
            border-radius: 10px;
        }

        .admin-login-card .btn-whatsapp {
            padding: 0.85rem;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: 10px;
        }

        .back-link {
            color: var(--bs-secondary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--whatsapp-green);
        }

        [data-bs-theme="dark"] .back-link {
            color: #adb5bd;
        }

        [data-bs-theme="dark"] .back-link:hover {
            color: var(--whatsapp-green);
        }
    </style>
@endpush

@section('content')
    <div class="admin-login-wrapper">
        <div class="admin-login-card">
            <div class="card shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="admin-login-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4 class="mb-1 fw-bold">لوحة الإدارة</h4>
                        <p class="text-muted mb-0">تسجيل دخول المسؤولين</p>
                    </div>

                    <form method="POST" action="{{ route('admin.login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label fw-medium">اسم المستخدم</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                id="username" name="username" value="{{ old('username') }}" placeholder="admin" required
                                autofocus>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium">كلمة المرور</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
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

                        <button type="submit" class="btn btn-whatsapp w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل الدخول
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="back-link">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة للموقع الرئيسي
                </a>
            </div>
        </div>
    </div>
@endsection
