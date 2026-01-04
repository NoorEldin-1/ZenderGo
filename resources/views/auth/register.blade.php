@extends('layouts.app')

@section('title', 'إنشاء حساب جديد')

@section('content')
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">إنشاء حساب جديد</h4>
                    <p class="text-muted">اربط حساب WhatsApp الخاص بك للتسجيل</p>
                </div>

                <!-- Info Messages -->
                @if (session('info'))
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                    </div>
                @endif

                <!-- Instructions -->
                <div id="instructionsSection">
                    <div class="alert alert-light border mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-list-ol me-2"></i>خطوات التسجيل:</h6>
                        <ol class="mb-0 small">
                            <li>اضغط على زر "بدء التسجيل" أدناه</li>
                            <li>سيظهر لك رمز QR Code</li>
                            <li>افتح WhatsApp على هاتفك</li>
                            <li>اذهب إلى: الإعدادات ← الأجهزة المرتبطة ← ربط جهاز</li>
                            <li>امسح رمز QR Code</li>
                            <li>سيتم إنشاء حسابك تلقائياً!</li>
                        </ol>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-whatsapp btn-lg" id="startBtn">
                            <i class="bi bi-qr-code me-2"></i>بدء التسجيل
                        </button>
                    </div>
                </div>

                <!-- QR Code Container -->
                <div id="qrCodeContainer" class="text-center mb-4" style="display: none;">
                    <div class="p-4 bg-light rounded-3 d-inline-block">
                        <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-width: 280px;">
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        <i class="bi bi-phone me-1"></i>
                        امسح الرمز من تطبيق WhatsApp على هاتفك
                    </p>
                    <div class="spinner-border spinner-border-sm text-success mt-3" role="status">
                        <span class="visually-hidden">جاري الانتظار...</span>
                    </div>
                    <p class="text-muted small mt-2">جاري انتظار مسح الرمز...</p>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startBtn = document.getElementById('startBtn');
            const retryBtn = document.getElementById('retryBtn');
            const instructionsSection = document.getElementById('instructionsSection');
            const qrCodeContainer = document.getElementById('qrCodeContainer');
            const qrCodeImage = document.getElementById('qrCodeImage');
            const loadingState = document.getElementById('loadingState');
            const loadingText = document.getElementById('loadingText');
            const successState = document.getElementById('successState');
            const successMessage = document.getElementById('successMessage');
            const errorContainer = document.getElementById('errorContainer');
            const errorMessage = document.getElementById('errorMessage');

            let statusCheckInterval = null;

            function showError(message) {
                errorMessage.textContent = message;
                errorContainer.style.display = 'block';
                loadingState.style.display = 'none';
                qrCodeContainer.style.display = 'none';
            }

            function hideError() {
                errorContainer.style.display = 'none';
            }

            function showLoading(text) {
                loadingText.textContent = text;
                loadingState.style.display = 'block';
                instructionsSection.style.display = 'none';
                qrCodeContainer.style.display = 'none';
                hideError();
            }

            function showQrCode(qrcode) {
                // Add data URI prefix if missing (WPPConnect returns raw Base64)
                if (qrcode && !qrcode.startsWith('data:')) {
                    qrcode = 'data:image/png;base64,' + qrcode;
                }
                qrCodeImage.src = qrcode;
                qrCodeContainer.style.display = 'block';
                loadingState.style.display = 'none';
                instructionsSection.style.display = 'none';
                startStatusCheck();
            }

            function showSuccess(message) {
                successMessage.textContent = message;
                successState.style.display = 'block';
                qrCodeContainer.style.display = 'none';
                loadingState.style.display = 'none';
                if (statusCheckInterval) clearInterval(statusCheckInterval);
            }

            async function startRegistration() {
                showLoading('جاري بدء جلسة التسجيل...');

                try {
                    const response = await fetch('{{ route('register.start') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    console.log('Start registration response:', data);

                    if (data.qrcode) {
                        showQrCode(data.qrcode);
                    } else if (data.status === 'CONNECTED') {
                        showSuccess('تم الربط بنجاح!');
                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 1500);
                        }
                    } else if (data.message) {
                        showError(data.message);
                    } else {
                        showError('فشل الحصول على QR Code. يرجى المحاولة مرة أخرى.');
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
    <style>
        #qrCodeContainer {
            animation: fadeIn 0.3s ease-in-out;
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

        #qrCodeImage {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush
