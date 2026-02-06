@extends('layouts.app')

@section('title', 'دليل الاستخدام')

@push('styles')
    <style>
        /* ========== Hero Section ========== */
        /* Guide Page Wrapper - Overflow Protection */
        .guide-page-wrapper {
            overflow-x: clip;
            max-width: 100%;
            position: relative;
            padding-left: 0;
        }

        .guide-hero {
            background: linear-gradient(135deg, #1a1d21 0%, #2d3748 50%, #1a1d21 100%);
            padding: 3rem 1.5rem;
            margin: -1.5rem -1.5rem 2rem -1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            max-width: 100vw;
            box-sizing: border-box;
        }

        .guide-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(37, 211, 102, 0.1) 0%, transparent 50%);
            animation: pulse-bg 8s ease-in-out infinite;
        }

        @keyframes pulse-bg {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .guide-hero h1 {
            color: white;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .guide-hero .emoji-rocket {
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .guide-hero p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        /* ========== Progress Bar ========== */
        .progress-container {
            position: sticky;
            top: 60px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem 1rem;
            padding-bottom: 2.5rem;
            margin: 0 -1.5rem 2rem -1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 100vw;
            box-sizing: border-box;
            overflow: visible;
        }

        .progress-steps {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            gap: 0;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50px;
            right: 50px;
            height: 4px;
            background: #e9ecef;
            transform: translateY(-50%);
            z-index: 0;
            border-radius: 2px;
        }

        .progress-line {
            position: absolute;
            top: 50%;
            left: auto;
            right: 50px;
            height: 4px;
            background: linear-gradient(90deg, var(--whatsapp-dark), var(--whatsapp-green));
            transform: translateY(-50%);
            z-index: 1;
            transition: width 0.5s ease;
            width: 0%;
            border-radius: 2px;
        }

        .progress-step {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            color: #6c757d;
            position: relative;
            z-index: 2;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            flex-shrink: 0;
            margin: 0 18px;
        }

        .progress-step:first-child {
            margin-left: 0;
        }

        .progress-step:last-child {
            margin-right: 0;
        }

        .progress-step:hover {
            transform: scale(1.1);
            border-color: var(--whatsapp-green);
        }

        .progress-step.active {
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            border-color: var(--whatsapp-green);
            color: white;
            transform: scale(1.15);
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.5);
        }

        .progress-step.completed {
            background: var(--whatsapp-green);
            border-color: var(--whatsapp-green);
            color: white;
        }

        .progress-step.completed::after {
            content: '✓';
            font-size: 1.2rem;
        }

        .progress-step.completed span:first-child {
            display: none;
        }

        .progress-step-label {
            position: absolute;
            bottom: -28px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.75rem;
            white-space: nowrap;
            color: #6c757d;
            font-weight: 600;
            background: white;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .progress-step.active .progress-step-label {
            color: var(--whatsapp-dark);
        }

        /* ========== Timeline ========== */
        .timeline {
            position: relative;
            padding-right: 60px;
            overflow: visible;
        }

        .timeline::before {
            content: '';
            position: absolute;
            right: 15px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, var(--whatsapp-green), var(--whatsapp-dark), #667eea);
            border-radius: 4px;
        }

        /* ========== Step Cards ========== */
        .step-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            padding-top: 2.5rem;
            margin-bottom: 2.5rem;
            margin-top: 1rem;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(37, 211, 102, 0.1);
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: visible;
        }

        .step-card.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(37, 211, 102, 0.15);
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 2rem;
            right: -38px;
            width: 20px;
            height: 20px;
            background: var(--whatsapp-green);
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.2);
        }

        .step-number {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            transform: rotate(-5deg);
            z-index: 10;
        }

        .step-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: inline-block;
            animation: bounce-in 0.6s ease;
        }

        @keyframes bounce-in {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .step-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1d21;
            margin-bottom: 1rem;
        }

        .step-description {
            color: #4a5568;
            font-size: 1.05rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        /* ========== Benefits List ========== */
        .benefits-list {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.05), rgba(37, 211, 102, 0.1));
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .benefits-title {
            font-weight: 700;
            color: var(--whatsapp-dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px dashed rgba(37, 211, 102, 0.2);
        }

        .benefit-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .benefit-icon {
            width: 28px;
            height: 28px;
            background: var(--whatsapp-green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .benefit-text strong {
            color: #1a1d21;
            display: block;
            margin-bottom: 2px;
        }

        .benefit-text span {
            color: #718096;
            font-size: 0.9rem;
        }

        /* ========== Sub-steps Accordion ========== */
        .substeps-accordion {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
        }

        .substep-header {
            background: white;
            padding: 1rem 1.25rem;
            border: none;
            width: 100%;
            text-align: right;
            font-weight: 600;
            color: #1a1d21;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 1px solid #e9ecef;
        }

        .substep-header:hover {
            background: rgba(37, 211, 102, 0.05);
        }

        .substep-header .icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .substep-header.method-1 .icon {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .substep-header.method-2 .icon {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .substep-header .arrow {
            margin-right: auto;
            transition: transform 0.3s;
        }

        .substep-header[aria-expanded="true"] .arrow {
            transform: rotate(180deg);
        }

        .substep-body {
            padding: 1.25rem;
            background: white;
        }

        .substep-list {
            list-style: none;
            padding: 0;
            margin: 0;
            counter-reset: substep;
        }

        .substep-list li {
            padding: 0.75rem 0;
            padding-right: 2.5rem;
            position: relative;
            border-bottom: 1px dashed #e9ecef;
            color: #4a5568;
            line-height: 1.7;
        }

        .substep-list li:last-child {
            border-bottom: none;
        }

        .substep-list li::before {
            counter-increment: substep;
            content: counter(substep);
            position: absolute;
            right: 0;
            top: 0.75rem;
            width: 24px;
            height: 24px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #6c757d;
        }

        /* ========== CTA Section ========== */
        .cta-section {
            background: linear-gradient(135deg, #1a1d21, #2d3748);
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            margin-top: 3rem;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2325D366' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-section h3 {
            color: white;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s;
        }

        .cta-btn-primary {
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            color: white;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
        }

        .cta-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(37, 211, 102, 0.5);
            color: white;
        }

        .cta-btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .cta-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* ========== Tips Section ========== */
        .tips-section {
            background: linear-gradient(135deg, #fff9e6, #fff3cd);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .tips-title {
            color: #856404;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tip-item {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            color: #856404;
        }

        .tip-item:last-child {
            margin-bottom: 0;
        }

        /* ========== Responsive ========== */
        @media (max-width: 991.98px) {
            .guide-hero {
                margin: -1rem -1rem 1.5rem -1rem;
                padding: 2rem 1rem;
                width: calc(100% + 2rem);
                margin-left: -1rem;
                margin-right: -1rem;
            }

            .progress-container {
                margin: 0 -1rem 1.5rem -1rem;
                width: calc(100% + 2rem);
            }

            .timeline {
                padding-right: 25px;
            }

            .timeline::before {
                right: 10px;
            }

            .step-card::before {
                right: -33px;
                width: 16px;
                height: 16px;
            }
        }

        @media (max-width: 575.98px) {

            /* Hero section mobile fix */
            .guide-hero {
                margin: 0 0 1.5rem 0;
                padding: 2rem 1rem;
                width: 100%;
                border-radius: 0;
            }

            .guide-hero h1 {
                font-size: 1.5rem;
            }

            /* Progress container mobile fix */
            .progress-container {
                margin: 0 0 1.5rem 0;
                width: 100%;
                padding: 1rem 0.5rem;
                padding-bottom: 2rem;
            }

            .progress-steps {
                justify-content: center;
                gap: 0;
                max-width: 100%;
                flex-wrap: nowrap;
            }

            .progress-steps::before {
                left: 15%;
                right: 15%;
                top: 16px;
            }

            .progress-line {
                right: 15%;
                top: 16px;
            }

            .progress-step {
                width: 32px;
                height: 32px;
                font-size: 0.75rem;
                margin: 0 4px;
                flex-shrink: 0;
            }

            .progress-step-label {
                display: none !important;
            }

            /* Timeline mobile fix - hide decorative elements */
            .timeline {
                padding-right: 0;
                padding-left: 0;
                overflow: visible;
            }

            .timeline::before {
                display: none;
            }

            .step-card {
                padding: 1.5rem;
                padding-top: 4rem;
                margin-right: 0;
                margin-top: 0.5rem;
                position: relative;
            }

            .step-card::before {
                display: none;
            }

            .step-title {
                font-size: 1.25rem;
            }

            /* Step number - centered at top of card */
            .step-number {
                position: absolute;
                top: -20px;
                left: 50%;
                right: auto;
                transform: translateX(-50%) rotate(0deg);
                width: 44px;
                height: 44px;
                font-size: 1.1rem;
            }

            .step-icon {
                text-align: center;
                margin-top: 0.5rem;
            }

            /* CTA section mobile fix */
            .cta-section {
                margin: 2rem 0;
                border-radius: 15px;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .cta-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="guide-page-wrapper">
        <!-- Hero Section -->
        <div class="guide-hero">
            <h1><span class="emoji-rocket">🚀</span> رحلتك مع زندر</h1>
            <p>دليل شامل ومفصل لجميع مميزات المنصة - من إضافة جهات الاتصال حتى إرسال حملاتك</p>
        </div>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-steps">
                <div class="progress-line" id="progressLine"></div>
                <div class="progress-step active" data-step="1" onclick="scrollToStep(1)">
                    <span>1</span>
                    <span class="progress-step-label">جهات الاتصال</span>
                </div>
                <div class="progress-step" data-step="2" onclick="scrollToStep(2)">
                    <span>2</span>
                    <span class="progress-step-label">المشاركة</span>
                </div>
                <div class="progress-step" data-step="3" onclick="scrollToStep(3)">
                    <span>3</span>
                    <span class="progress-step-label">إنشاء حملة</span>
                </div>
                <div class="progress-step" data-step="4" onclick="scrollToStep(4)">
                    <span>4</span>
                    <span class="progress-step-label">القوالب</span>
                </div>
                <div class="progress-step" data-step="5" onclick="scrollToStep(5)">
                    <span>5</span>
                    <span class="progress-step-label">الإرسال</span>
                </div>
                <div class="progress-step" data-step="6" onclick="scrollToStep(6)">
                    <span>6</span>
                    <span class="progress-step-label">الاشتراك</span>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline">
            <!-- Step 1: Add Contacts -->
            <div class="step-card" id="step1">
                <div class="step-number">1</div>
                <div class="step-icon">👥</div>
                <h2 class="step-title">إضافة جهات الاتصال</h2>
                <p class="step-description">
                    الخطوة الأولى في رحلتك مع زندر هي إضافة جهات الاتصال - وهم العملاء أو الأشخاص الذين تريد إرسال الرسائل
                    إليهم.
                    يوفر لك زندر <strong>طريقتين مرنتين</strong> لإضافة جهات الاتصال حسب احتياجاتك.
                </p>

                <!-- Benefits -->
                <div class="benefits-list">
                    <div class="benefits-title">
                        <i class="bi bi-stars"></i>
                        لماذا هذه الخطوة مهمة؟
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-clock-history"></i></div>
                        <div class="benefit-text">
                            <strong>توفير الوقت والجهد</strong>
                            <span>بدلاً من إرسال رسائل فردية، أضف كل جهات اتصالك مرة واحدة وأرسل للجميع بضغطة زر</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-folder2-open"></i></div>
                        <div class="benefit-text">
                            <strong>تنظيم قاعدة العملاء</strong>
                            <span>احتفظ بقائمة منظمة ومحدثة لجميع عملائك في مكان واحد</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-arrow-repeat"></i></div>
                        <div class="benefit-text">
                            <strong>إعادة الاستخدام</strong>
                            <span>جهات الاتصال المضافة تبقى محفوظة ويمكنك استخدامها في أي حملة مستقبلية</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-steps Accordion -->
                <div class="substeps-accordion">
                    <button class="substep-header method-1" type="button" data-bs-toggle="collapse"
                        data-bs-target="#method1" aria-expanded="true">
                        <span class="icon"><i class="bi bi-person-plus"></i></span>
                        <span>الطريقة الأولى: الإضافة اليدوية (جهة واحدة)</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse show" id="method1">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>من القائمة الجانبية، اضغط على <strong>"جهات الاتصال"</strong></li>
                                <li>اضغط على زر <strong>"+ إضافة"</strong> الموجود أعلى الصفحة</li>
                                <li>أدخل <strong>اسم جهة الاتصال</strong> (مثال: أحمد محمد)</li>
                                <li>أدخل <strong>رقم الهاتف</strong> كاملاً مع كود المنطقة (مثال: 01012345678)</li>
                                <li>اضغط على <strong>"حفظ"</strong> لإضافة جهة الاتصال</li>
                            </ol>
                            <div class="alert alert-info py-2 mb-0 mt-3 small">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>نصيحة:</strong> هذه الطريقة مناسبة لإضافة عدد قليل من جهات الاتصال (1-10 جهات)
                            </div>
                        </div>
                    </div>

                    <button class="substep-header method-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#method2" aria-expanded="false">
                        <span class="icon"><i class="bi bi-file-earmark-excel"></i></span>
                        <span>الطريقة الثانية: الاستيراد من Excel (آلاف الجهات)</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse" id="method2">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>من صفحة <strong>"جهات الاتصال"</strong>، اضغط على زر <strong>"استيراد"</strong></li>
                                <li>سيظهر لك نافذة توضح <strong>تنسيق الملف المطلوب</strong>:
                                    <div class="mt-2 mb-2 p-2 bg-light rounded small">
                                        <code>Store_Name</code> (اختياري) | <code>Cust_FullName</code> (مطلوب) |
                                        <code>Cust_Mobile</code> (مطلوب)
                                    </div>
                                </li>
                                <li>اختر <strong>ملف Excel أو CSV</strong> من جهازك</li>
                                <li>ستظهر <strong>معاينة للبيانات</strong> قبل الاستيراد للتأكد من صحتها</li>
                                <li>يمكنك <strong>تصفية البيانات</strong> حسب رقم الهاتف أو اسم المتجر</li>
                                <li>اضغط <strong>"تأكيد الاستيراد"</strong> لإضافة جميع الجهات دفعة واحدة</li>
                            </ol>
                            <div class="alert alert-success py-2 mb-0 mt-3 small">
                                <i class="bi bi-lightning me-1"></i>
                                <strong>ميزة ذكية:</strong> النظام يضيف الصفر تلقائياً للأرقام الناقصة ويتعرف على أسماء
                                الأعمدة
                                البديلة!
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-section">
                    <div class="tips-title">
                        <i class="bi bi-lightbulb"></i>
                        نصائح مهمة
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>تأكد من صحة أرقام الهواتف - يجب أن تكون أرقام واتساب صالحة</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>يمكنك تعديل أو حذف أي جهة اتصال في أي وقت</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>استخدم خاصية <strong>"المشاركة"</strong> لإرسال جهات الاتصال لمستخدم آخر على المنصة</span>
                    </div>
                </div>
            </div>

            <!-- Step 2: Contact Sharing -->
            <div class="step-card" id="step2">
                <div class="step-number">2</div>
                <div class="step-icon">🔄</div>
                <h2 class="step-title">مشاركة جهات الاتصال</h2>
                <p class="step-description">
                    هل تريد مشاركة جهات الاتصال مع زميلك في العمل؟ زندر يتيح لك إرسال واستقبال جهات الاتصال
                    بين المستخدمين بسهولة تامة. هذه الميزة مفيدة للعمل الجماعي وتوزيع قوائم العملاء.
                </p>

                <!-- Benefits -->
                <div class="benefits-list">
                    <div class="benefits-title">
                        <i class="bi bi-stars"></i>
                        لماذا تستخدم المشاركة؟
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-people-fill"></i></div>
                        <div class="benefit-text">
                            <strong>العمل الجماعي</strong>
                            <span>شارك جهات الاتصال مع فريقك للعمل على نفس قاعدة العملاء</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-arrow-left-right"></i></div>
                        <div class="benefit-text">
                            <strong>تبادل البيانات</strong>
                            <span>استقبل جهات اتصال من زملائك وأضفها لقائمتك مباشرة</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-shield-check"></i></div>
                        <div class="benefit-text">
                            <strong>تحكم كامل</strong>
                            <span>قبول أو رفض أي طلب مشاركة - أنت المتحكم</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-steps Accordion -->
                <div class="substeps-accordion">
                    <button class="substep-header method-1" type="button" data-bs-toggle="collapse"
                        data-bs-target="#shareMethod1" aria-expanded="true">
                        <span class="icon"><i class="bi bi-send"></i></span>
                        <span>إرسال جهات اتصال لمستخدم آخر</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse show" id="shareMethod1">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>من صفحة <strong>"جهات الاتصال"</strong>، حدد جهات الاتصال التي تريد مشاركتها</li>
                                <li>اضغط على زر <strong>"مشاركة"</strong> الذي يظهر في أعلى الصفحة</li>
                                <li>أدخل <strong>رقم هاتف المستخدم</strong> الذي تريد المشاركة معه</li>
                                <li>اختيارياً: أضف <strong>رسالة</strong> توضيحية مع طلب المشاركة</li>
                                <li>اضغط <strong>"إرسال"</strong> لإرسال طلب المشاركة</li>
                            </ol>
                        </div>
                    </div>

                    <button class="substep-header method-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#shareMethod2" aria-expanded="false">
                        <span class="icon"><i class="bi bi-inbox"></i></span>
                        <span>استقبال طلبات المشاركة</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse" id="shareMethod2">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>عندما يشاركك أحد المستخدمين جهات اتصال، ستظهر في <strong>"طلبات المشاركة"</strong></li>
                                <li>اضغط على <strong>"عرض جهات الاتصال"</strong> لمعاينة البيانات قبل القبول</li>
                                <li>اضغط <strong>"قبول"</strong> لإضافة جهات الاتصال لقائمتك</li>
                                <li>أو اضغط <strong>"رفض"</strong> إذا لم ترغب في استلامها</li>
                            </ol>
                            <div class="alert alert-info py-2 mb-0 mt-3 small">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>ملاحظة:</strong> جهات الاتصال المقبولة تُضاف لقائمتك ويمكنك التعديل عليها بحرية
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Create Campaign -->
            <div class="step-card" id="step3">
                <div class="step-number">3</div>
                <div class="step-icon">📝</div>
                <h2 class="step-title">إنشاء حملة جديدة</h2>
                <p class="step-description">
                    بعد إضافة جهات الاتصال، حان وقت إنشاء حملتك! الحملة هي الرسالة التي سترسلها لمجموعة من العملاء.
                    زندر يوفر لك أدوات احترافية لكتابة رسائل جذابة ومؤثرة.
                </p>

                <!-- Benefits -->
                <div class="benefits-list">
                    <div class="benefits-title">
                        <i class="bi bi-stars"></i>
                        ما الذي يميز إنشاء الحملات في زندر؟
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-emoji-smile"></i></div>
                        <div class="benefit-text">
                            <strong>إيموجي ورموز تعبيرية</strong>
                            <span>أضف الإيموجي المناسب لجعل رسالتك أكثر جاذبية وتفاعلية 😊🔥⭐</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-type-bold"></i></div>
                        <div class="benefit-text">
                            <strong>تنسيق النص</strong>
                            <span>اجعل النص <b>عريض</b> أو <i>مائل</i> أو <s>مشطوب</s> لإبراز المعلومات المهمة</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-lightning"></i></div>
                        <div class="benefit-text">
                            <strong>قوالب جاهزة</strong>
                            <span>استخدم قوالب جاهزة أو احفظ رسائلك المميزة لإعادة استخدامها لاحقاً</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-images"></i></div>
                        <div class="benefit-text">
                            <strong>إضافة صور متعددة 🆕</strong>
                            <span>أرفق حتى <strong>5 صور</strong> مع رسالتك لجذب انتباه العميل وتوضيح العرض بشكل
                                أفضل!</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-steps -->
                <div class="substeps-accordion">
                    <button class="substep-header" type="button" data-bs-toggle="collapse"
                        data-bs-target="#createSteps" aria-expanded="true">
                        <span class="icon"><i class="bi bi-list-ol"></i></span>
                        <span>خطوات إنشاء الحملة بالتفصيل</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse show" id="createSteps">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>من القائمة، اضغط على <strong>"الحملات"</strong> أو من لوحة التحكم اضغط <strong>"+
                                        إنشاء"</strong></li>
                                <li><strong>اختر المستلمين:</strong> حدد جهات الاتصال التي تريد إرسال الرسالة إليها (الحد
                                    الأقصى
                                    50 مستلم لكل حملة)</li>
                                <li>استخدم <strong>البحث</strong> للعثور على جهات اتصال معينة بسرعة</li>
                                <li><strong>اكتب رسالتك:</strong> في مربع الرسالة، اكتب المحتوى الذي تريد إرساله</li>
                                <li>استخدم <strong>أزرار التنسيق</strong> (إيموجي، عريض، مائل، مشطوب) لتحسين رسالتك</li>
                                <li><strong>اختياري:</strong> أضف صور (حتى 5 صور) بالضغط على زر "صور" واختيار الملفات من
                                    جهازك -
                                    الحد الأقصى 5MB لكل صورة</li>
                                <li><strong>اختياري:</strong> استخدم "القوالب" لتحميل رسالة جاهزة أو حفظ رسالتك الحالية</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-section">
                    <div class="tips-title">
                        <i class="bi bi-lightbulb"></i>
                        نصائح لرسائل أكثر فعالية
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>ابدأ رسالتك بتحية ودية مثل "مرحباً 👋" لجذب الانتباه</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>اجعل رسالتك قصيرة ومباشرة - الرسائل الطويلة قد لا تُقرأ كاملة</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>استخدم <strong>Call To Action</strong> واضح مثل "اتصل الآن" أو "احجز مكانك"</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>احفظ الرسائل الناجحة كقوالب لإعادة استخدامها</span>
                    </div>
                </div>
            </div>

            <!-- Step 4: Templates -->
            <div class="step-card" id="step4">
                <div class="step-number">4</div>
                <div class="step-icon">📋</div>
                <h2 class="step-title">إدارة القوالب</h2>
                <p class="step-description">
                    هل تكتب نفس الرسالة مراراً وتكراراً؟ القوالب توفر عليك الوقت! احفظ رسائلك المميزة
                    واستخدمها في أي وقت بضغطة زر واحدة. كما يمكنك مشاركتها مع زملائك.
                </p>

                <!-- Benefits -->
                <div class="benefits-list">
                    <div class="benefits-title">
                        <i class="bi bi-stars"></i>
                        ماذا تستفيد من القوالب؟
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-clock-history"></i></div>
                        <div class="benefit-text">
                            <strong>توفير الوقت</strong>
                            <span>بدلاً من كتابة الرسالة كل مرة، استخدم قالب جاهز بضغطة واحدة</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-check2-all"></i></div>
                        <div class="benefit-text">
                            <strong>ثبات الجودة</strong>
                            <span>احتفظ بأفضل صيغ رسائلك واستخدمها دائماً بنفس الجودة</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-share"></i></div>
                        <div class="benefit-text">
                            <strong>مشاركة مع الفريق</strong>
                            <span>شارك قوالبك مع زملائك ليستخدموا نفس الرسائل الناجحة</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-steps Accordion -->
                <div class="substeps-accordion">
                    <button class="substep-header method-1" type="button" data-bs-toggle="collapse"
                        data-bs-target="#templatesSave" aria-expanded="true">
                        <span class="icon"><i class="bi bi-bookmark-plus"></i></span>
                        <span>حفظ رسالة كقالب</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse show" id="templatesSave">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>من صفحة <strong>"الحملات"</strong>، اكتب الرسالة التي تريد حفظها</li>
                                <li>اضغط على زر <strong>"القوالب"</strong> في شريط الأدوات</li>
                                <li>اضغط على <strong>"+ حفظ كقالب جديد"</strong></li>
                                <li>أدخل <strong>اسم القالب</strong> (مثال: عرض شهر رمضان)</li>
                                <li>اضغط <strong>"حفظ"</strong> - سيُحفظ القالب في قائمتك</li>
                            </ol>
                        </div>
                    </div>

                    <button class="substep-header method-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#templatesLoad" aria-expanded="false">
                        <span class="icon"><i class="bi bi-file-earmark-text"></i></span>
                        <span>استخدام قالب محفوظ</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse" id="templatesLoad">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>من صفحة <strong>"الحملات"</strong>، اضغط على زر <strong>"القوالب"</strong></li>
                                <li>ستظهر قائمة بجميع قوالبك المحفوظة</li>
                                <li>اضغط على <strong>اسم القالب</strong> الذي تريد استخدامه</li>
                                <li>سيتم <strong>تحميل النص تلقائياً</strong> في مربع الرسالة</li>
                            </ol>
                            <div class="alert alert-success py-2 mb-0 mt-3 small">
                                <i class="bi bi-lightbulb me-1"></i>
                                <strong>نصيحة:</strong> يمكنك تعديل النص بعد تحميله لتخصيصه حسب الحاجة
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-section">
                    <div class="tips-title">
                        <i class="bi bi-lightbulb"></i>
                        نصائح للقوالب
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>أنشئ قوالب لكل نوع من رسائلك (عروض، تذكيرات، تهنئة...)</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>استخدم أسماء واضحة للقوالب ليسهل العثور عليها</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>يمكنك مشاركة القوالب مع زملائك من نفس نافذة القوالب</span>
                    </div>
                </div>
            </div>

            <!-- Step 5: Send Campaign -->
            <div class="step-card" id="step5">
                <div class="step-number">5</div>
                <div class="step-icon">🚀</div>
                <h2 class="step-title">إرسال الحملة</h2>
                <p class="step-description">
                    الخطوة الأخيرة والأهم! بعد اختيار المستلمين وكتابة الرسالة، كل ما عليك هو الضغط على زر الإرسال
                    وسيتولى زندر إرسال رسالتك لجميع المستلمين <strong>تلقائياً</strong>.
                </p>

                <!-- Benefits -->
                <div class="benefits-list">
                    <div class="benefits-title">
                        <i class="bi bi-stars"></i>
                        كيف يعمل الإرسال التلقائي؟
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-robot"></i></div>
                        <div class="benefit-text">
                            <strong>إرسال آلي 100%</strong>
                            <span>لا تحتاج لفعل أي شيء - النظام يرسل الرسائل واحدة تلو الأخرى تلقائياً</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-shield-check"></i></div>
                        <div class="benefit-text">
                            <strong>فاصل زمني ذكي (15 ثانية)</strong>
                            <span>يوجد فاصل 15 ثانية بين كل رسالة لحماية حسابك من الحظر والحفاظ على استقرار الخدمة</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <div class="benefit-text">
                            <strong>وصول مضمون</strong>
                            <span>الرسائل تُرسل مباشرة عبر واتساب مما يضمن وصولها للعميل</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-steps -->
                <div class="substeps-accordion">
                    <button class="substep-header" type="button" data-bs-toggle="collapse" data-bs-target="#sendSteps"
                        aria-expanded="true">
                        <span class="icon"><i class="bi bi-send-check"></i></span>
                        <span>خطوات الإرسال النهائية</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse show" id="sendSteps">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li><strong>راجع الحملة:</strong> تأكد من اختيار المستلمين الصحيحين وأن الرسالة مكتوبة بشكل
                                    صحيح
                                </li>
                                <li><strong>تحقق من العدد:</strong> تأكد أن عدد المستلمين يظهر بشكل صحيح (أعلى قائمة
                                    المستلمين)
                                </li>
                                <li><strong>اضغط "إرسال":</strong> زر الإرسال الأخضر أسفل مربع الرسالة</li>
                                <li><strong>انتظر:</strong> سيبدأ النظام بإرسال الرسائل تلقائياً - لا تغلق الصفحة!</li>
                                <li><strong>اكتمل!</strong> بعد انتهاء الإرسال ستظهر رسالة تأكيد بنجاح العملية</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="alert alert-warning mt-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>
                            <strong>تنبيه مهم!</strong>
                            <p class="mb-0 small">
                                لا تغلق الصفحة أثناء عملية الإرسال. انتظر حتى تظهر رسالة تأكيد نجاح الإرسال.
                                الفاصل الزمني 15 ثانية ضروري لحماية حسابك.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Subscription -->
            <div class="step-card" id="step6">
                <div class="step-number">6</div>
                <div class="step-icon">💎</div>
                <h2 class="step-title">الاشتراك والدفع</h2>
                <p class="step-description">
                    للاستمتاع بجميع مميزات زندر، تحتاج لاشتراك فعّال. النظام يوفر فترة تجريبية مجانية
                    للبدء، ثم يمكنك الاشتراك بخطوات بسيطة.
                </p>

                <!-- Benefits -->
                <div class="benefits-list">
                    <div class="benefits-title">
                        <i class="bi bi-stars"></i>
                        معلومات عن الاشتراكات
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-gift"></i></div>
                        <div class="benefit-text">
                            <strong>فترة تجريبية مجانية</strong>
                            <span>ابدأ بفترة تجريبية مجانية لتجربة جميع المميزات قبل الاشتراك</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-calendar-check"></i></div>
                        <div class="benefit-text">
                            <strong>اشتراك شهري</strong>
                            <span>اشترك شهرياً واستخدم جميع مميزات زندر بدون حدود</span>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="bi bi-credit-card-2-front"></i></div>
                        <div class="benefit-text">
                            <strong>دفع سهل</strong>
                            <span>ادفع عبر فودافون كاش أو إنستاباي وارفع إيصال الدفع - بسيط وسريع</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-steps Accordion -->
                <div class="substeps-accordion">
                    <button class="substep-header method-1" type="button" data-bs-toggle="collapse"
                        data-bs-target="#subPayment" aria-expanded="true">
                        <span class="icon"><i class="bi bi-wallet2"></i></span>
                        <span>كيفية الاشتراك والدفع</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse show" id="subPayment">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>من القائمة الجانبية، اضغط على <strong>"اشتراكي"</strong></li>
                                <li>ستجد <strong>رقم فودافون كاش أو إنستاباي</strong> لتحويل قيمة الاشتراك</li>
                                <li>قم بالتحويل عبر فودافون كاش أو إنستاباي أو أي محفظة إلكترونية</li>
                                <li>ارفع <strong>صورة إيصال الدفع</strong> من خلال الصفحة</li>
                                <li>انتظر <strong>تأكيد الإدارة</strong> - عادة خلال ساعات قليلة</li>
                            </ol>
                            <div class="alert alert-info py-2 mb-0 mt-3 small">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>ملاحظة:</strong> بعد تأكيد الدفع، سيتم تفعيل اشتراكك فوراً وستتمكن من استخدام جميع
                                المميزات
                            </div>
                        </div>
                    </div>

                    <button class="substep-header method-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#subStatus" aria-expanded="false">
                        <span class="icon"><i class="bi bi-clock-history"></i></span>
                        <span>متابعة حالة الاشتراك</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </button>
                    <div class="collapse" id="subStatus">
                        <div class="substep-body">
                            <ol class="substep-list">
                                <li>اضغط على <strong>"اشتراكي"</strong> من القائمة الجانبية</li>
                                <li>ستظهر لك <strong>حالة اشتراكك الحالي</strong> (نشط/تجريبي/منتهي)</li>
                                <li>كما ستظهر <strong>المدة المتبقية</strong> في اشتراكك بالتفصيل</li>
                                <li>إذا اقترب انتهاء اشتراكك، ستظهر لك <strong>تنبيهات</strong> للتجديد</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-section">
                    <div class="tips-title">
                        <i class="bi bi-lightbulb"></i>
                        نصائح مهمة
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>احرص على رفع إيصال واضح يظهر فيه المبلغ والتاريخ</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>جدد اشتراكك قبل انتهائه لتجنب انقطاع الخدمة</span>
                    </div>
                    <div class="tip-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>تواصل مع الدعم عبر واتساب لأي استفسار عن الاشتراك</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <div class="cta-content">
                <h3>🎉 أنت جاهز الآن!</h3>
                <p>لقد تعلمت كل ما تحتاجه لاستخدام زندر بكفاءة. ابدأ الآن!</p>
                <div class="cta-buttons">
                    <a href="{{ route('contacts.index') }}" class="cta-btn cta-btn-primary">
                        <i class="bi bi-people"></i>
                        جهات الاتصال
                    </a>
                    <a href="{{ route('campaigns.create') }}" class="cta-btn cta-btn-secondary">
                        <i class="bi bi-megaphone"></i>
                        إنشاء حملة
                    </a>
                    <a href="{{ route('subscription.index') }}" class="cta-btn cta-btn-secondary">
                        <i class="bi bi-gem"></i>
                        اشتراكي
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Scroll Animation for Step Cards
        const stepCards = document.querySelectorAll('.step-card');
        const progressSteps = document.querySelectorAll('.progress-step');
        const progressLine = document.getElementById('progressLine');

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    updateProgress(entry.target);
                }
            });
        }, observerOptions);

        stepCards.forEach(card => observer.observe(card));

        // Update progress bar based on visible step
        function updateProgress(visibleCard) {
            const stepId = visibleCard.id;
            const stepNumber = parseInt(stepId.replace('step', ''));

            progressSteps.forEach((step, index) => {
                const currentStep = index + 1;
                step.classList.remove('active', 'completed');

                if (currentStep < stepNumber) {
                    step.classList.add('completed');
                } else if (currentStep === stepNumber) {
                    step.classList.add('active');
                }
            });

            // Update progress line
            const percentage = ((stepNumber - 1) / (progressSteps.length - 1)) * 100;
            progressLine.style.width = percentage + '%';
        }

        // Scroll to step function
        function scrollToStep(stepNumber) {
            const stepElement = document.getElementById('step' + stepNumber);
            if (stepElement) {
                const yOffset = -150;
                const y = stepElement.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });
            }
        }

        // Update progress on scroll
        window.addEventListener('scroll', () => {
            let currentStep = 1;

            stepCards.forEach((card, index) => {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight / 2) {
                    currentStep = index + 1;
                }
            });

            progressSteps.forEach((step, index) => {
                const stepNum = index + 1;
                step.classList.remove('active', 'completed');

                if (stepNum < currentStep) {
                    step.classList.add('completed');
                } else if (stepNum === currentStep) {
                    step.classList.add('active');
                }
            });

            const percentage = ((currentStep - 1) / (progressSteps.length - 1)) * 100;
            progressLine.style.width = percentage + '%';
        });

        // Make first card visible on load
        setTimeout(() => {
            document.getElementById('step1').classList.add('visible');
        }, 300);
    </script>
@endpush
