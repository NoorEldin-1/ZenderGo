<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#25D366">
    <title>زندر | منصة التسويق عبر واتساب وإدارة العملاء</title>
    <meta name="description"
        content="زندر - منصة متكاملة لأتمتة رسائل واتساب وإدارة علاقات العملاء (CRM). أرسل حملات تسويقية ذكية وتواصل مع عملائك بسهولة.">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet"
        integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --whatsapp-green: #25D366;
            --whatsapp-dark: #128C7E;
            --body-bg: #0a0f1a;
            --text-muted: rgba(255, 255, 255, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--body-bg);
            color: #fff;
            min-height: 100vh;
            position: relative;
        }

        #particles-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--body-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(37, 211, 102, 0.4);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--whatsapp-green);
        }

        .navbar-landing {
            background: rgba(10, 15, 26, 0.6);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 1200px;
            z-index: 1000;
            transition: transform 0.4s ease, top 0.4s ease, opacity 0.4s ease;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .navbar-landing.nav-hidden {
            transform: translate(-50%, -150%);
            opacity: 0;
        }

        .navbar-brand {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--whatsapp-green) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .navbar-brand i {
            font-size: 2rem;
        }

        .btn-login {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.4);
            color: white;
        }

        .btn-register {
            background: var(--whatsapp-green);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-register:hover {
            background: var(--whatsapp-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 100px;
            overflow: hidden;
        }



        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(37, 211, 102, 0.15);
            border: 1px solid rgba(37, 211, 102, 0.3);
            color: var(--whatsapp-green);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-title .highlight {
            background: linear-gradient(135deg, var(--whatsapp-green), #67e89a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            border: none;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(37, 211, 102, 0.4);
            color: white;
        }

        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 1rem 2rem;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .hero-visual {
            position: relative;
            z-index: 2;
        }

        .mockup-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
        }

        .mockup-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mockup-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mockup-avatar i {
            font-size: 1.5rem;
            color: white;
        }

        .mockup-info h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1rem;
        }

        .mockup-info small {
            color: var(--text-muted);
        }

        .mockup-contacts {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.75rem 1rem;
            border-radius: 12px;
        }

        .contact-avatar {
            width: 40px;
            height: 40px;
            background: rgba(37, 211, 102, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--whatsapp-green);
        }

        .contact-info {
            flex: 1;
        }

        .contact-info .name {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
        }

        .contact-info .status {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .contact-badge {
            background: var(--whatsapp-green);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .features-section {
            padding: 6rem 0;
            position: relative;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 3rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.4s;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(37, 211, 102, 0.3);
            transform: translateY(-8px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon.green {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.2), rgba(37, 211, 102, 0.05));
        }

        .feature-icon.blue {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(102, 126, 234, 0.05));
        }

        .feature-icon i {
            font-size: 2rem;
        }

        .feature-icon.green i {
            color: var(--whatsapp-green);
        }

        .feature-icon.blue i {
            color: #667eea;
        }

        .feature-card h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* ========== Sticky Steps Section ========== */
        .steps-section {
            padding: 5rem 0 5rem 0;
            position: relative;
            z-index: 2;
        }

        .steps-sticky-wrapper {
            position: sticky;
            top: 15vh;
            padding-right: 2rem;
        }

        .steps-title {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff, var(--whatsapp-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .steps-subtitle {
            color: var(--text-muted);
            font-size: 1.15rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .step-card {
            background: #0a0f1a;
            /* Opaque to hide previous cards */
            border: 1px solid rgba(255, 255, 255, 0.05);
            /* Softer border */
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            /* Stronger highlight on top edge to separate stacked cards */
            border-radius: 24px;
            padding: 2.5rem;
            position: sticky;
            transition: all 0.4s ease;
            box-shadow: 0 -25px 50px rgba(0, 0, 0, 0.6);
            /* Enhanced shadow above the card for a clear 3D overlap effect */
        }

        .step-card:nth-of-type(1) {
            top: 15vh;
            margin-bottom: 40vh;
        }

        .step-card:nth-of-type(2) {
            top: calc(15vh + 80px);
            /* 80px offset ensures badge doesn't overlap text above */
            margin-bottom: 40vh;
        }

        .step-card:nth-of-type(3) {
            top: calc(15vh + 160px);
            margin-bottom: 0vh;
            /* No huge sticky margin on the last box to prevent blank scroll */
        }

        .step-card:hover {
            border-color: rgba(37, 211, 102, 0.4);
            background: #0d1424;
            /* Solid slightly lighter dark background instead of transparent */
            transform: translateX(-10px);
        }

        .step-number {
            position: absolute;
            top: -20px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            border: 4px solid var(--body-bg);
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
        }

        .step-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.15), rgba(37, 211, 102, 0.05));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .step-icon i {
            font-size: 1.8rem;
            color: var(--whatsapp-green);
        }

        .step-icon.blue {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(102, 126, 234, 0.05));
        }

        .step-icon.blue i {
            color: #667eea;
        }

        .step-icon.gold {
            background: linear-gradient(135deg, rgba(247, 201, 75, 0.15), rgba(247, 201, 75, 0.05));
        }

        .step-icon.gold i {
            color: #f7c94b;
        }

        .step-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: white;
        }

        .step-card p {
            color: var(--text-muted);
            font-size: 1.05rem;
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .step-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .step-features span {
            background: rgba(37, 211, 102, 0.1);
            border: 1px solid rgba(37, 211, 102, 0.2);
            color: var(--whatsapp-green);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .step-features span i {
            font-size: 1rem;
        }

        .steps-cta-wrapper {
            margin-top: 2rem;
        }

        .btn-steps-cta {
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }

        .btn-steps-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(37, 211, 102, 0.4);
            color: white;
        }

        @media (max-width: 991.98px) {
            .steps-sticky-wrapper {
                position: relative;
                top: 0;
                padding-right: 0;
                margin-bottom: 4rem;
                text-align: center;
            }

            .step-card {
                position: relative;
                top: 0 !important;
                margin-bottom: 2rem !important;
                box-shadow: none;
            }

            .steps-cta-wrapper {
                text-align: center;
            }
        }

        .footer-section {
            padding: 3rem 0;
            background: transparent;
        }

        .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .footer-brand {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .footer-brand:hover {
            opacity: 0.8;
            color: #fff;
        }

        .footer-brand span {
            background: linear-gradient(135deg, var(--whatsapp-green), #67e89a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-brand i {
            color: var(--whatsapp-green);
            font-size: 2rem;
        }

        @media (max-width: 991.98px) {
            .hero-section {
                text-align: center;
            }

            .hero-description {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-visual {
                margin-top: 3rem;
            }
        }

        @media (max-width: 575.98px) {
            .navbar-brand {
                font-size: 1.25rem;
            }

            .navbar-brand i {
                font-size: 1.5rem;
            }

            .btn-login,
            .btn-register {
                padding: 0.4rem 1rem;
                font-size: 0.9rem;
            }

            .navbar-landing {
                padding: 0.5rem 1rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-hero-primary,
            .btn-hero-secondary {
                width: 100%;
                justify-content: center;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            /* Timeline Mobile */
            .timeline-title {
                font-size: 1.75rem;
            }

            .timeline-wrapper {
                padding-right: 40px;
            }

            .timeline-wrapper::before {
                right: 15px;
            }

            .timeline-indicator {
                right: -40px;
                width: 36px;
                height: 36px;
            }

            .timeline-indicator .step-num {
                font-size: 0.9rem;
            }

            .timeline-content {
                padding: 1.25rem;
            }

            .timeline-content h3 {
                font-size: 1.1rem;
            }

            .timeline-features {
                gap: 0.5rem;
            }

            .timeline-features span {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
            }

            .btn-timeline-cta {
                width: 100%;
                justify-content: center;
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Particles Background Canvas -->
    <canvas id="particles-canvas"></canvas>

    <nav class="navbar-landing">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/" class="navbar-brand">
                    <i class="bi bi-whatsapp"></i>
                    زندر
                </a>
                <div class="d-flex align-items-center gap-2">
                    <a href="/login" class="btn-login">دخول</a>
                    <a href="/register" class="btn-register">ابدأ مجاناً</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section text-center">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-10 hero-content">
                    <div class="hero-badge mx-auto">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span>منصة التسويق #1 عبر واتساب</span>
                    </div>
                    <h1 class="hero-title">
                        ضاعف <span class="highlight">مبيعاتك</span><br>
                        عبر واتساب مع زندر
                    </h1>
                    <p class="hero-description mx-auto">
                        منصة متكاملة لأتمتة الرسائل وإدارة علاقات العملاء (CRM).
                        أرسل حملات تسويقية ذكية وتواصل مع عملائك بسهولة.
                    </p>
                    <div class="hero-buttons">
                        <a href="/register" class="btn-hero-primary">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                            ابدأ تجربتك المجانية
                        </a>
                        <a href="/login" class="btn-hero-secondary">
                            <i class="bi bi-box-arrow-in-left"></i>
                            لديك حساب؟ سجل دخولك
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How It Works Sticky Section -->
    <section class="steps-section">
        <div class="container">
            <div class="row">

                <!-- Sticky Content (Title & CTA) -->
                <div class="col-lg-5">
                    <div class="steps-sticky-wrapper">
                        <h2 class="steps-title">كيف تبدأ مع زندر؟</h2>
                        <p class="steps-subtitle">٣ خطوات بسيطة لإطلاق حملتك الأولى بكل احترافية، ابدأ الآن ووفر وقتك.
                        </p>

                        <div class="steps-cta-wrapper d-none d-lg-block">
                            <a href="/register" class="btn-steps-cta">
                                <i class="bi bi-rocket-takeoff-fill"></i>
                                ابدأ الآن مجاناً
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Scrolling Cards -->
                <div class="col-lg-7">
                    <!-- Step 1 -->
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h3>أنشئ حسابك واربط واتساب</h3>
                        <p>سجّل حساباً جديداً ثم اربط رقم واتساب الخاص بك بسهولة وأمان عبر خيارات الربط المباشرة
                            المتوفرة لدينا.</p>
                        <div class="step-features">
                            <span><i class="bi bi-phone"></i> كود الربط</span>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon blue">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3>أضف جهات الاتصال</h3>
                        <p>أضف عملاءك بالطريقة التي تناسبك وفي ثوانٍ معدودة لتنظيم جهات اتصالك استعداداً لإطلاق حملتك.
                        </p>
                        <div class="step-features">
                            <span><i class="bi bi-pencil-square"></i> إضافة يدوية</span>
                            <span><i class="bi bi-file-earmark-excel"></i> استيراد من Excel</span>
                            <span><i class="bi bi-chat-dots"></i> سحب المحادثات</span>
                            <span><i class="bi bi-collection"></i> سحب الجروبات</span>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon gold">
                            <i class="bi bi-send-fill"></i>
                        </div>
                        <h3>أطلق حملتك الأولى</h3>
                        <p>أرسل رسائل مخصصة لكل عميل تلقائياً بضغطة زر واحدة وتتبع حالة إرسال حملتك مباشرة.</p>
                        <div class="step-features">
                            <span><i class="bi bi-image"></i> صور ومرفقات</span>
                            <span><i class="bi bi-person-badge"></i> اسم المستلم تلقائياً</span>
                        </div>
                    </div>

                    <!-- Mobile CTA (Shows only on mobile) -->
                    <div class="steps-cta-wrapper d-block d-lg-none mt-5">
                        <a href="/register" class="btn-steps-cta w-100 justify-content-center">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                            ابدأ الآن مجاناً
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>



    <footer class="footer-section">
        <div class="container">
            <div class="footer-content">
                <a href="/" class="footer-brand">
                    <i class="bi bi-whatsapp"></i>
                    <span>ZenderGo</span>
                </a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let lastScrollTop = 0;
            const navbar = document.querySelector('.navbar-landing');

            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Scroll Down
                    navbar.classList.add('nav-hidden');
                } else {
                    // Scroll Up
                    navbar.classList.remove('nav-hidden');
                }
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling
            });

            // Particles Animation
            const canvas = document.getElementById('particles-canvas');
            const ctx = canvas.getContext('2d');
            let width, height, particles;
            let mouse = {
                x: null,
                y: null,
                radius: 150
            }; // Interaction radius
            let isDesktop = window.matchMedia("(min-width: 992px)").matches;

            function initCanvas() {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
                particles = [];
                isDesktop = window.matchMedia("(min-width: 992px)").matches;

                // Creates particles based on screen size (responsive count)
                let particleCount = Math.floor((width * height) / 18000);
                if (particleCount > 100) particleCount = 100; // Cap particles for performance

                for (let i = 0; i < particleCount; i++) {
                    particles.push(new Particle());
                }
            }

            class Particle {
                constructor() {
                    this.x = Math.random() * width;
                    this.y = Math.random() * height;
                    this.vx = (Math.random() - 0.5) * 1.5;
                    this.vy = (Math.random() - 0.5) * 1.5;
                    this.radius = Math.random() * 2 + 1;
                }

                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    // Bounce off walls
                    if (this.x < 0 || this.x > width) this.vx = -this.vx;
                    if (this.y < 0 || this.y > height) this.vy = -this.vy;
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(37, 211, 102, 0.6)';
                    ctx.fill();
                }
            }

            function animate() {
                ctx.clearRect(0, 0, width, height);

                for (let i = 0; i < particles.length; i++) {
                    particles[i].update();
                    particles[i].draw();

                    // Draw connecting lines between particles
                    for (let j = i + 1; j < particles.length; j++) {
                        let dx = particles[i].x - particles[j].x;
                        let dy = particles[i].y - particles[j].y;
                        let distance = Math.sqrt(dx * dx + dy * dy);

                        if (distance < 120) {
                            ctx.beginPath();
                            ctx.strokeStyle =
                                `rgba(37, 211, 102, ${0.4 - distance / 300})`; // Fade depending on distance
                            ctx.lineWidth = 0.8;
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.stroke();
                        }
                    }

                    // Mouse Interaction (Desktop Only)
                    if (isDesktop && mouse.x !== null && mouse.y !== null) {
                        let dxMouse = particles[i].x - mouse.x;
                        let dyMouse = particles[i].y - mouse.y;
                        let distanceMouse = Math.sqrt(dxMouse * dxMouse + dyMouse * dyMouse);

                        if (distanceMouse < mouse.radius) {
                            ctx.beginPath();
                            ctx.strokeStyle =
                                `rgba(37, 211, 102, ${0.6 - distanceMouse / (mouse.radius * 1.5)})`; // Stronger connection to mouse
                            ctx.lineWidth = 1;
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(mouse.x, mouse.y);
                            ctx.stroke();

                            // Optional: slight magnetic pull effect towards the cursor (uncomment to activate)
                            // particles[i].x -= dxMouse / 100;
                            // particles[i].y -= dyMouse / 100;
                        }
                    }
                }
                requestAnimationFrame(animate);
            }

            initCanvas();
            animate();

            // Event Listeners for Mouse
            window.addEventListener('mousemove', (event) => {
                if (isDesktop) {
                    mouse.x = event.x;
                    mouse.y = event.y;
                }
            });

            window.addEventListener('mouseout', () => {
                mouse.x = null;
                mouse.y = null;
            });

            window.addEventListener('resize', () => {
                initCanvas();
            });
        });
    </script>
</body>

</html>
