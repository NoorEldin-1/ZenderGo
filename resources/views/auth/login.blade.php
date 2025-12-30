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

                    <div class="d-grid">
                        <button type="submit" class="btn btn-whatsapp btn-lg">
                            <i class="bi bi-send me-2"></i>إرسال رمز التحقق
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center text-muted small mt-4">
            <i class="bi bi-shield-check me-1"></i>
            سيتم إرسال رمز التحقق عبر واتساب
        </p>
    </div>
@endsection
