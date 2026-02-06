@extends('layouts.app')

@section('title', 'لوحة التحكم')

@push('styles')
    <style>
        .dashboard-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card .card-body {
            padding: 1.5rem;
        }

        .dashboard-card .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .dashboard-card .icon-wrapper i {
            font-size: 1.75rem;
        }

        .dashboard-card .icon-wrapper.primary {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.15), rgba(13, 110, 253, 0.05));
        }

        .dashboard-card .icon-wrapper.primary i {
            color: #0d6efd;
        }

        .dashboard-card .icon-wrapper.success {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.2), rgba(37, 211, 102, 0.05));
        }

        .dashboard-card .icon-wrapper.success i {
            color: var(--whatsapp-green);
        }

        .dashboard-card .icon-wrapper.info {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.15), rgba(13, 202, 240, 0.05));
        }

        .dashboard-card .icon-wrapper.info i {
            color: #0dcaf0;
        }

        .dashboard-card .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #1a1d21;
        }

        .dashboard-card .card-stat {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1d21;
            margin-bottom: 0.25rem;
        }

        .dashboard-card .card-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .dashboard-card .card-btn {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .dashboard-card .card-btn.btn-primary-soft {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border: none;
        }

        .dashboard-card .card-btn.btn-primary-soft:hover {
            background: #0d6efd;
            color: white;
        }

        .dashboard-card .card-btn.btn-success-soft {
            background: rgba(37, 211, 102, 0.15);
            color: var(--whatsapp-dark);
            border: none;
        }

        .dashboard-card .card-btn.btn-success-soft:hover {
            background: var(--whatsapp-green);
            color: white;
        }

        .dashboard-card .card-btn.btn-info-soft {
            background: rgba(13, 202, 240, 0.1);
            color: #0aa2c0;
            border: none;
        }

        .dashboard-card .card-btn.btn-info-soft:hover {
            background: #0dcaf0;
            color: white;
        }

        /* Campaign card highlight */
        .dashboard-card.highlight {
            background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
            border: 2px solid rgba(37, 211, 102, 0.2);
        }

        .dashboard-card.highlight:hover {
            border-color: var(--whatsapp-green);
        }

        /* ========== Guide Banner ========== */
        .guide-banner {
            position: relative;
            background: linear-gradient(135deg, #1a1d21 0%, #2d3748 50%, #1a1d21 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            overflow: hidden;
            min-height: 200px;
        }

        .guide-banner::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle at 30% 50%, rgba(37, 211, 102, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 70% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 40%);
            animation: aurora 15s ease-in-out infinite;
        }

        @keyframes aurora {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(5%, -5%) rotate(5deg);
            }

            66% {
                transform: translate(-5%, 5%) rotate(-5deg);
            }
        }

        .guide-banner-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .guide-banner-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 10px 40px rgba(37, 211, 102, 0.4);
            animation: float-icon 4s ease-in-out infinite;
        }

        @keyframes float-icon {

            0%,
            100% {
                transform: translateY(0) rotate(-3deg);
            }

            50% {
                transform: translateY(-8px) rotate(3deg);
            }
        }

        .guide-banner-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .guide-banner-text {
            flex-grow: 1;
        }

        .guide-banner-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .guide-banner-title .badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            font-size: 0.65rem;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-weight: 600;
            animation: pulse-badge 2s ease-in-out infinite;
        }

        @keyframes pulse-badge {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .guide-banner-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .guide-banner-steps {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .guide-step-mini {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
        }

        .guide-step-mini .step-num {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .guide-banner-cta {
            flex-shrink: 0;
        }

        .guide-banner-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.75rem;
            background: white;
            color: #1a1d21;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .guide-banner-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.3);
            background: var(--whatsapp-green);
            color: white;
        }

        .guide-banner-btn i {
            font-size: 1.25rem;
            transition: transform 0.3s;
        }

        .guide-banner-btn:hover i {
            transform: translateX(-5px);
        }

        /* Floating particles */
        .guide-particles {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(37, 211, 102, 0.4);
            border-radius: 50%;
            animation: float-particle 8s ease-in-out infinite;
        }

        .particle:nth-child(1) {
            left: 10%;
            top: 20%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            left: 20%;
            top: 80%;
            animation-delay: 1s;
        }

        .particle:nth-child(3) {
            left: 60%;
            top: 30%;
            animation-delay: 2s;
        }

        .particle:nth-child(4) {
            left: 80%;
            top: 70%;
            animation-delay: 3s;
        }

        .particle:nth-child(5) {
            left: 90%;
            top: 10%;
            animation-delay: 4s;
        }

        @keyframes float-particle {

            0%,
            100% {
                transform: translateY(0) scale(1);
                opacity: 0.4;
            }

            50% {
                transform: translateY(-20px) scale(1.5);
                opacity: 0.8;
            }
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .guide-banner-content {
                flex-direction: column;
                text-align: center;
            }

            .guide-banner-steps {
                justify-content: center;
            }

            .guide-banner-cta {
                width: 100%;
            }

            .guide-banner-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 575.98px) {
            .guide-banner {
                padding: 1.5rem;
            }

            .guide-banner-icon {
                width: 70px;
                height: 70px;
            }

            .guide-banner-icon i {
                font-size: 1.75rem;
            }

            .guide-banner-title {
                font-size: 1.25rem;
            }

            .guide-banner-steps {
                gap: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <h2 class="mb-1 fw-bold">مرحباً! 👋</h2>
        <p class="text-muted mb-0">إليك ملخص لحسابك</p>
    </div>

    <div class="row g-4">
        <!-- Contacts Card -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="dashboard-card card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="icon-wrapper primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="card-stat">{{ Auth::user()->contacts()->count() }}</div>
                    <p class="card-text">جهات الاتصال المحفوظة</p>
                    <a href="{{ route('contacts.index') }}" class="card-btn btn-primary-soft mt-auto">
                        <i class="bi bi-arrow-left"></i>
                        إدارة جهات الاتصال
                    </a>
                </div>
            </div>
        </div>

        <!-- Campaign Card -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="dashboard-card card h-100 highlight">
                <div class="card-body d-flex flex-column">
                    <div class="icon-wrapper success">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="card-title">حملة جديدة</div>
                    <p class="card-text">أرسل رسائل جماعية عبر واتساب</p>
                    <a href="{{ route('campaigns.create') }}" class="card-btn btn-success-soft mt-auto">
                        <i class="bi bi-plus-lg"></i>
                        إنشاء حملة
                    </a>
                </div>
            </div>
        </div>

        <!-- Import Card -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="dashboard-card card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="icon-wrapper info">
                        <i class="bi bi-file-earmark-excel"></i>
                    </div>
                    <div class="card-title">استيراد جهات</div>
                    <p class="card-text">من ملف Excel أو CSV</p>
                    <a href="{{ route('contacts.index') }}" class="card-btn btn-info-soft mt-auto">
                        <i class="bi bi-upload"></i>
                        استيراد الآن
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide Banner -->
    <div class="guide-banner">
        <!-- Floating Particles -->
        <div class="guide-particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <div class="guide-banner-content">
            <div class="guide-banner-icon">
                <i class="bi bi-book"></i>
            </div>

            <div class="guide-banner-text">
                <div class="guide-banner-title">
                    أول مرة تستخدم زندر؟
                    <span class="badge">دليل شامل</span>
                </div>
                <p class="guide-banner-subtitle">تعلم كيف ترسل حملتك الأولى في 3 خطوات بسيطة</p>

                <div class="guide-banner-steps">
                    <div class="guide-step-mini">
                        <span class="step-num">1</span>
                        <span>أضف جهات الاتصال</span>
                    </div>
                    <div class="guide-step-mini">
                        <span class="step-num">2</span>
                        <span>أنشئ حملة</span>
                    </div>
                    <div class="guide-step-mini">
                        <span class="step-num">3</span>
                        <span>أرسل عبر واتساب</span>
                    </div>
                </div>
            </div>

            <div class="guide-banner-cta">
                <a href="{{ route('guide') }}" class="guide-banner-btn">
                    <span>ابدأ الدليل</span>
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
@endsection
