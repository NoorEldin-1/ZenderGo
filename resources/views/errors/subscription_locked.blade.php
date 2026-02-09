@extends('layouts.app')

@section('title', 'تجديد الاشتراك')

@push('styles')
    <style>
        .locked-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .locked-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Top Decoration Line */
        .locked-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%);
        }

        /* Icon Animation */
        .icon-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .icon-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .main-icon {
            position: relative;
            z-index: 2;
            font-size: 3.5rem;
            color: #fd7e14;
            line-height: 100px;
            animation: shake 4s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.2;
            }

            100% {
                transform: scale(1);
                opacity: 0.5;
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            5% {
                transform: rotate(-5deg);
            }

            10% {
                transform: rotate(5deg);
            }

            15% {
                transform: rotate(-5deg);
            }

            20% {
                transform: rotate(5deg);
            }

            25% {
                transform: rotate(0deg);
            }
        }

        h2 {
            font-weight: 800;
            color: #2b3445;
            margin-bottom: 1rem;
        }

        p {
            color: #7d879c;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 1.05rem;
        }

        .action-btn {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            color: white;
        }

        .back-link {
            display: block;
            margin-top: 1.5rem;
            color: #6c757d;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #495057;
        }
    </style>
@endpush

@section('content')
    @php
        $supportPhone = \App\Models\SystemSetting::getSupportPhoneNumber();
    @endphp
    <div class="locked-container">
        <div class="locked-card">
            <div class="icon-container">
                <div class="icon-bg"></div>
                <div class="main-icon">
                    <i class="bi bi-lock-fill"></i>
                </div>
            </div>

            <h2>الاشتراك مطلوب</h2>
            <p>
                عفواً، لا يمكنك الوصول إلى هذه الصفحة لأن اشتراكك الحالي غير مفعل.
                <br>
                يرجى تجديد اشتراكك للاستمتاع بجميع مميزات المنصة والوصول إلى جهات الاتصال والحملات.
            </p>

            <a href="{{ route('subscription.index') }}" class="action-btn">
                <i class="bi bi-rocket-takeoff"></i>
                تجديد الاشتراك الآن
            </a>

            <div class="mt-4 pt-3 border-top">
                <p class="text-muted small mb-2">هل تحتاج مساعدة؟</p>
                <a href="https://wa.me/2{{ $supportPhone }}" target="_blank" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-whatsapp me-1"></i>تواصل مع الدعم: {{ $supportPhone }}
                </a>
            </div>

            <a href="{{ route('contacts.index') }}" class="back-link">
                العودة للرئيسية
            </a>
        </div>
    </div>
@endsection
