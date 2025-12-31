@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="d-flex align-items-center mb-4">
                <h2 class="h4 mb-0">
                    <i class="bi bi-gear text-muted me-2"></i>الإعدادات
                </h2>
            </div>

            <!-- WhatsApp Settings Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-whatsapp text-success me-2"></i>ربط WhatsApp
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Connection Status -->
                    <div id="connectionStatus">
                        @if ($isConnected)
                            <div class="alert alert-success mb-4" id="statusConnected">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                    <div>
                                        <strong>حساب WhatsApp مربوط!</strong>
                                        <br>
                                        <small class="text-muted">يمكنك الآن إرسال الحملات من رقمك.</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning mb-4" id="statusDisconnected">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                    <div>
                                        <strong>لم يتم ربط حساب WhatsApp</strong>
                                        <br>
                                        <small>اضغط على "ربط WhatsApp" ثم امسح QR Code من تطبيق WhatsApp.</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- QR Code Container -->
                    <div id="qrCodeContainer" class="text-center mb-4" style="display: none;">
                        <div class="p-4 bg-light rounded-3 d-inline-block">
                            <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-width: 280px;">
                        </div>
                        <p class="text-muted mt-3 mb-0">
                            <i class="bi bi-phone me-1"></i>
                            افتح WhatsApp على هاتفك ← الإعدادات ← الأجهزة المرتبطة ← ربط جهاز
                        </p>
                    </div>

                    <!-- Loading State -->
                    <div id="loadingState" class="text-center mb-4" style="display: none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0" id="loadingText">جاري بدء الجلسة...</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        @if ($isConnected)
                            <button type="button" class="btn btn-outline-danger btn-lg" id="disconnectBtn">
                                <i class="bi bi-x-circle me-2"></i>قطع الاتصال
                            </button>
                        @else
                            <button type="button" class="btn btn-whatsapp btn-lg" id="connectBtn">
                                <i class="bi bi-qr-code me-2"></i>ربط WhatsApp
                            </button>
                        @endif
                    </div>

                    <!-- Error Message Container -->
                    <div id="errorContainer" class="alert alert-danger mt-3" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="errorMessage"></span>
                    </div>

                    <!-- Session Info (Debug) -->
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted d-block">
                            <i class="bi bi-info-circle me-1"></i>
                            معرف الجلسة: <code dir="ltr">{{ $user->whatsapp_session }}</code>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Account Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>معلومات الحساب
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <label class="form-label text-muted small">رقم الهاتف</label>
                            <p class="mb-0 fw-semibold" dir="ltr">{{ $user->phone }}</p>
                        </div>
                        @if ($user->email)
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">البريد الإلكتروني</label>
                                <p class="mb-0 fw-semibold" dir="ltr">{{ $user->email }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const connectBtn = document.getElementById('connectBtn');
            const disconnectBtn = document.getElementById('disconnectBtn');
            const qrCodeContainer = document.getElementById('qrCodeContainer');
            const qrCodeImage = document.getElementById('qrCodeImage');
            const loadingState = document.getElementById('loadingState');
            const loadingText = document.getElementById('loadingText');
            const connectionStatus = document.getElementById('connectionStatus');
            const errorContainer = document.getElementById('errorContainer');
            const errorMessage = document.getElementById('errorMessage');

            let statusCheckInterval = null;

            // Helper functions for error display
            function showError(message) {
                if (errorContainer && errorMessage) {
                    errorMessage.textContent = message;
                    errorContainer.style.display = 'block';
                }
            }

            function hideError() {
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                }
            }

            // Connect Button Click
            if (connectBtn) {
                connectBtn.addEventListener('click', async function() {
                    connectBtn.disabled = true;
                    connectBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>جاري الاتصال...';

                    loadingState.style.display = 'block';
                    loadingText.textContent = 'جاري بدء الجلسة...';
                    qrCodeContainer.style.display = 'none';
                    hideError();

                    try {
                        const response = await fetch('{{ route('settings.whatsapp.start') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        console.log('Start session response:', data);
                        loadingState.style.display = 'none';

                        if (data.qrcode) {
                            // Show QR Code
                            qrCodeImage.src = data.qrcode;
                            qrCodeContainer.style.display = 'block';
                            connectBtn.innerHTML =
                                '<i class="bi bi-arrow-clockwise me-2"></i>تحديث QR Code';
                            connectBtn.disabled = false;

                            // Start checking connection status
                            startStatusCheck();
                        } else if (data.status === 'CONNECTED' || data.status === 'isLogged') {
                            // Already connected
                            location.reload();
                        } else if (data.message) {
                            // Show error message from API
                            showError(data.message);
                            connectBtn.innerHTML = '<i class="bi bi-qr-code me-2"></i>ربط WhatsApp';
                            connectBtn.disabled = false;
                        } else {
                            // Try to get QR code separately
                            loadingText.textContent = 'جاري جلب QR Code...';
                            loadingState.style.display = 'block';
                            await fetchQrCode();
                            loadingState.style.display = 'none';
                            connectBtn.innerHTML = '<i class="bi bi-qr-code me-2"></i>ربط WhatsApp';
                            connectBtn.disabled = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        loadingState.style.display = 'none';
                        connectBtn.innerHTML = '<i class="bi bi-qr-code me-2"></i>ربط WhatsApp';
                        connectBtn.disabled = false;
                        showError(
                            'حدث خطأ في الاتصال. تأكد من تشغيل خادم WhatsApp على localhost:21465');
                    }
                });
            }

            // Disconnect Button Click
            if (disconnectBtn) {
                disconnectBtn.addEventListener('click', async function() {
                    if (!confirm('هل أنت متأكد من قطع اتصال WhatsApp؟')) return;

                    disconnectBtn.disabled = true;
                    disconnectBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>جاري قطع الاتصال...';

                    try {
                        await fetch('{{ route('settings.whatsapp.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        disconnectBtn.disabled = false;
                        disconnectBtn.innerHTML = '<i class="bi bi-x-circle me-2"></i>قطع الاتصال';
                    }
                });
            }

            // Fetch QR Code
            async function fetchQrCode() {
                try {
                    const response = await fetch('{{ route('settings.whatsapp.qrcode') }}', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    if (data.qrcode) {
                        qrCodeImage.src = data.qrcode;
                        qrCodeContainer.style.display = 'block';
                        startStatusCheck();
                    }
                } catch (error) {
                    console.error('Error fetching QR:', error);
                }
            }

            // Check connection status periodically
            function startStatusCheck() {
                if (statusCheckInterval) clearInterval(statusCheckInterval);

                statusCheckInterval = setInterval(async () => {
                    try {
                        const response = await fetch('{{ route('settings.whatsapp.status') }}', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.connected) {
                            clearInterval(statusCheckInterval);
                            // Success! Reload page to show connected state
                            location.reload();
                        }
                    } catch (error) {
                        console.error('Status check error:', error);
                    }
                }, 3000); // Check every 3 seconds
            }

            // Clean up on page leave
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
