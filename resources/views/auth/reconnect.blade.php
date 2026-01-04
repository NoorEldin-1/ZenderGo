@extends('layouts.app')

@section('title', 'إعادة ربط WhatsApp')

@section('content')
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-link-45deg text-warning" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">إعادة ربط WhatsApp</h4>
                    <p class="text-muted">جلسة WhatsApp منتهية. يرجى إعادة الربط للمتابعة.</p>
                </div>

                <!-- Warning Messages -->
                @if (session('warning'))
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                    </div>
                @endif

                <!-- Phone Display -->
                <div class="alert alert-light border mb-4 text-center">
                    <small class="text-muted d-block">رقم الهاتف المسجل</small>
                    <strong dir="ltr" class="fs-5">{{ $phone ?? session('login_phone') }}</strong>
                </div>

                <!-- Connect Button -->
                <div id="connectSection">
                    <div class="d-grid">
                        <button type="button" class="btn btn-whatsapp btn-lg" id="connectBtn">
                            <i class="bi bi-qr-code me-2"></i>عرض QR Code
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
                        امسح الرمز من تطبيق WhatsApp
                    </p>
                    <div class="spinner-border spinner-border-sm text-success mt-3" role="status"></div>
                    <p class="text-muted small mt-2">جاري الانتظار...</p>
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
                        <span>تم الربط بنجاح!</span>
                    </div>
                </div>

                <!-- Error Container -->
                <div id="errorContainer" class="alert alert-danger mt-3" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="errorMessage"></span>
                </div>

                <!-- Back to Login -->
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-muted small">
                        <i class="bi bi-arrow-right me-1"></i>العودة لتسجيل الدخول
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const connectBtn = document.getElementById('connectBtn');
            const connectSection = document.getElementById('connectSection');
            const qrCodeContainer = document.getElementById('qrCodeContainer');
            const qrCodeImage = document.getElementById('qrCodeImage');
            const loadingState = document.getElementById('loadingState');
            const loadingText = document.getElementById('loadingText');
            const successState = document.getElementById('successState');
            const errorContainer = document.getElementById('errorContainer');
            const errorMessage = document.getElementById('errorMessage');

            let statusCheckInterval = null;

            function showError(message) {
                errorMessage.textContent = message;
                errorContainer.style.display = 'block';
                loadingState.style.display = 'none';
            }

            function hideError() {
                errorContainer.style.display = 'none';
            }

            async function startReconnect() {
                connectBtn.disabled = true;
                connectBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>جاري الاتصال...';
                loadingState.style.display = 'block';
                loadingText.textContent = 'جاري بدء الجلسة...';
                hideError();

                try {
                    const response = await fetch('{{ route('login.reconnect.start') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    console.log('Reconnect response:', data);
                    loadingState.style.display = 'none';

                    if (data.redirect) {
                        // Already connected
                        successState.style.display = 'block';
                        connectSection.style.display = 'none';
                        setTimeout(() => window.location.href = data.redirect, 1000);
                    } else if (data.qrcode) {
                        // Add data URI prefix if missing (WPPConnect returns raw Base64)
                        let qrcode = data.qrcode;
                        if (!qrcode.startsWith('data:')) {
                            qrcode = 'data:image/png;base64,' + qrcode;
                        }
                        qrCodeImage.src = qrcode;
                        qrCodeContainer.style.display = 'block';
                        connectSection.style.display = 'none';
                        startStatusCheck();
                    } else if (data.message) {
                        showError(data.message);
                        connectBtn.disabled = false;
                        connectBtn.innerHTML = '<i class="bi bi-qr-code me-2"></i>عرض QR Code';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    loadingState.style.display = 'none';
                    showError('حدث خطأ في الاتصال');
                    connectBtn.disabled = false;
                    connectBtn.innerHTML = '<i class="bi bi-qr-code me-2"></i>عرض QR Code';
                }
            }

            function startStatusCheck() {
                if (statusCheckInterval) clearInterval(statusCheckInterval);

                statusCheckInterval = setInterval(async () => {
                    try {
                        const response = await fetch('{{ route('login.reconnect.check') }}', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.connected && data.redirect) {
                            clearInterval(statusCheckInterval);
                            successState.style.display = 'block';
                            qrCodeContainer.style.display = 'none';
                            setTimeout(() => window.location.href = data.redirect, 1000);
                        }
                    } catch (error) {
                        console.error('Status check error:', error);
                    }
                }, 3000);
            }

            connectBtn.addEventListener('click', startReconnect);

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
