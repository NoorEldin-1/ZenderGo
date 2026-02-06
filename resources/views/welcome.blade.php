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
            overflow-x: hidden;
        }

        .navbar-landing {
            background: rgba(10, 15, 26, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
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

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 100%;
            height: 150%;
            background: radial-gradient(circle, rgba(37, 211, 102, 0.15) 0%, transparent 60%);
            pointer-events: none;
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

        .footer-section {
            background: rgba(0, 0, 0, 0.3);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2rem 0;
        }

        .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
        }

        .footer-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--whatsapp-green);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-year {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        @media (max-width: 991.98px) {
            .hero-section {
                text-align: center;
            }

            .hero-description {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-visual {
                margin-top: 3rem;
            }
        }

        @media (max-width: 575.98px) {
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
        }
    </style>
</head>

<body>
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

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <div class="hero-badge">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span>منصة التسويق #1 عبر واتساب</span>
                    </div>
                    <h1 class="hero-title">
                        ضاعف <span class="highlight">مبيعاتك</span><br>
                        عبر واتساب مع زندر
                    </h1>
                    <p class="hero-description">
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
                <div class="col-lg-6 hero-visual">
                    <div class="hero-mockup">
                        <div class="mockup-card">
                            <div class="mockup-header">
                                <div class="mockup-avatar">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="mockup-info">
                                    <h5>جهات الاتصال</h5>
                                    <small>إدارة العملاء</small>
                                </div>
                            </div>
                            <div class="mockup-contacts">
                                <div class="contact-item">
                                    <div class="contact-avatar">أ</div>
                                    <div class="contact-info">
                                        <p class="name">أحمد محمد</p>
                                        <span class="status">عميل نشط</span>
                                    </div>
                                    <span class="contact-badge">VIP</span>
                                </div>
                                <div class="contact-item">
                                    <div class="contact-avatar">س</div>
                                    <div class="contact-info">
                                        <p class="name">سارة علي</p>
                                        <span class="status">عميل جديد</span>
                                    </div>
                                </div>
                                <div class="contact-item">
                                    <div class="contact-avatar">م</div>
                                    <div class="contact-info">
                                        <p class="name">محمود حسن</p>
                                        <span class="status">مهتم</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <h2 class="section-title">كل ما تحتاجه في منصة واحدة</h2>
            <p class="section-subtitle">أدوات قوية تساعدك على تنمية أعمالك وزيادة مبيعاتك عبر واتساب</p>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon green"><i class="bi bi-send-fill"></i></div>
                        <h4>رسائل جماعية</h4>
                        <p>أرسل حملات تسويقية لآلاف العملاء دفعة واحدة مع تخصيص الرسائل لكل عميل. جدولة الرسائل وتتبع
                            حالة التسليم.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon blue"><i class="bi bi-people-fill"></i></div>
                        <h4>إدارة العملاء</h4>
                        <p>نظام CRM متكامل لتنظيم جهات الاتصال وتصنيف العملاء حسب اهتماماتهم. استيراد وتصدير البيانات
                            بسهولة.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-section">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <i class="bi bi-whatsapp"></i>
                    Zender
                </div>
                <span class="footer-year">© 2026</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
