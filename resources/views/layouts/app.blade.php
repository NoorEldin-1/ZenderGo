<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#25D366">
    <title>@yield('title', 'زندر') - تسويق واتساب</title>

    <!-- Bootstrap 5.3 RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet"
        integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts - Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bs-body-bg: #f5f6fa;
            --whatsapp-green: #25D366;
            --whatsapp-dark: #128C7E;
            --sidebar-width: 220px;
        }

        * {
            font-family: 'Cairo', sans-serif;
        }

        body {
            background-color: var(--bs-body-bg);
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            padding: 0.75rem 1rem;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--whatsapp-green) !important;
        }

        /* Dropdown Fix for RTL */
        .dropdown-menu {
            text-align: right;
            min-width: 180px;
        }

        .dropdown-menu-end {
            --bs-position: end;
        }

        /* WhatsApp Button */
        .btn-whatsapp {
            background-color: var(--whatsapp-green);
            border-color: var(--whatsapp-green);
            color: white;
        }

        .btn-whatsapp:hover,
        .btn-whatsapp:focus {
            background-color: var(--whatsapp-dark);
            border-color: var(--whatsapp-dark);
            color: white;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border-radius: 12px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            border-radius: 12px 12px 0 0 !important;
        }

        .auth-card {
            max-width: 400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Sidebar - Desktop */
        .sidebar {
            width: var(--sidebar-width);
            min-height: calc(100vh - 60px);
            background: linear-gradient(180deg, #1a1d21 0%, #212529 100%);
            position: fixed;
            top: 60px;
            right: 0;
            padding-top: 1rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.875rem 1.25rem;
            border-radius: 8px;
            margin: 4px 12px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .sidebar .nav-link.active {
            background: rgba(37, 211, 102, 0.15);
            color: var(--whatsapp-green);
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: calc(100vh - 60px);
            padding: 1.5rem;
        }

        /* Mobile Bottom Navigation */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #212529;
            z-index: 1050;
            padding: 8px 0;
            padding-bottom: calc(8px + env(safe-area-inset-bottom));
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        }

        .mobile-nav .nav-link {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            padding: 6px 8px;
            font-size: 0.7rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .mobile-nav .nav-link i {
            font-size: 1.35rem;
            display: block;
            margin-bottom: 2px;
        }

        .mobile-nav .nav-link.active {
            color: var(--whatsapp-green);
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                display: none !important;
            }

            .main-content {
                margin-right: 0;
                padding: 1rem;
            }

            .mobile-nav {
                display: block;
            }

            body {
                padding-bottom: 75px;
            }
        }

        @media (max-width: 575.98px) {
            .auth-card {
                margin: 1rem auto;
                padding: 0 0.75rem;
            }

            .card {
                border-radius: 10px;
            }

            h2,
            .h2 {
                font-size: 1.35rem;
            }

            .btn {
                padding: 0.625rem 1rem;
                font-size: 0.9rem;
            }

            .table {
                font-size: 0.85rem;
            }

            .table td,
            .table th {
                padding: 0.625rem 0.5rem;
            }
        }

        /* Form Controls */
        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 0.625rem 0.875rem;
            border-color: #dee2e6;
        }

        .form-control-lg {
            padding: 0.75rem 1rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--whatsapp-green);
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.15);
        }

        /* Alerts */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 0.875rem 1rem;
        }

        /* Badge */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        /* Table */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            font-weight: 600;
            font-size: 0.85rem;
            color: #6c757d;
            border-bottom-width: 1px;
        }

        /* ========== Modern Pagination Styling ========== */
        .pagination {
            gap: 4px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .pagination .page-item {
            margin: 2px;
        }

        .pagination .page-link {
            border: none;
            border-radius: 10px !important;
            padding: 0.5rem 0.85rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: #6c757d;
            background: #f8f9fa;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .pagination .page-link:hover {
            color: var(--whatsapp-dark);
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.35);
            transform: scale(1.05);
        }

        .pagination .page-item.disabled .page-link {
            background: #f1f3f4;
            color: #adb5bd;
            pointer-events: none;
            box-shadow: none;
        }

        /* Previous/Next Buttons with Icons */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            min-width: auto;
            background: linear-gradient(135deg, #343a40 0%, #495057 100%);
            color: white;
        }

        .pagination .page-item:first-child .page-link:hover,
        .pagination .page-item:last-child .page-link:hover {
            background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .pagination .page-item:first-child.disabled .page-link,
        .pagination .page-item:last-child.disabled .page-link {
            background: #e9ecef;
            color: #adb5bd;
        }

        /* Ellipsis Styling */
        .pagination .page-item.disabled .page-link[aria-disabled="true"],
        .pagination .page-item.disabled span.page-link {
            background: transparent;
            box-shadow: none;
            font-weight: 700;
            letter-spacing: 2px;
        }

        /* Pagination Container */
        .pagination-wrapper,
        #pagination,
        .card-footer nav {
            padding: 1rem 0;
        }

        /* Pagination Info Text */
        #paginationInfo,
        .pagination-info {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Responsive Pagination */
        @media (max-width: 767.98px) {
            .pagination {
                gap: 3px;
            }

            .pagination .page-link {
                padding: 0.4rem 0.65rem;
                min-width: 36px;
                height: 36px;
                font-size: 0.8rem;
                border-radius: 8px !important;
            }

            .pagination .page-item:first-child .page-link,
            .pagination .page-item:last-child .page-link {
                padding: 0.4rem 0.75rem;
                font-size: 0.75rem;
            }

            /* Hide page numbers on very small screens, show only prev/next and current */
            .pagination .page-item:not(:first-child):not(:last-child):not(.active) {
                display: none;
            }

            .pagination .page-item.active {
                display: flex;
            }

            /* Show first/last and ellipsis neighbors */
            .pagination .page-item:nth-child(2),
            .pagination .page-item:nth-last-child(2) {
                display: flex;
            }

            /* Card footer responsive */
            .card-footer {
                flex-direction: column !important;
                gap: 1rem !important;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .pagination .page-link {
                padding: 0.45rem 0.75rem;
                min-width: 38px;
                height: 38px;
            }
        }

        /* Pagination inside card footer layout */
        .card-footer.d-flex {
            gap: 1rem;
        }

        .card-footer .pagination-sm .page-link {
            padding: 0.35rem 0.6rem;
            min-width: 34px;
            height: 34px;
            font-size: 0.8rem;
        }

        /* Laravel Default Pagination Override */
        nav[aria-label="Pagination Navigation"],
        nav[role="navigation"],
        .pagination-container {
            display: flex;
            justify-content: center;
        }

        nav[aria-label="Pagination Navigation"] ul,
        nav[role="navigation"] ul {
            gap: 4px;
        }

        /* Hide "Showing X to Y of Z results" on mobile */
        @media (max-width: 575.98px) {

            nav[aria-label="Pagination Navigation"]>div:first-child,
            .pagination-results,
            .pagination span.text-sm {
                display: none;
            }
        }

        /* Loading Overlay - Block User Interaction */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            backdrop-filter: blur(4px);
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-overlay .spinner-container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 90%;
        }

        .loading-overlay .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--whatsapp-green);
        }

        .loading-overlay .loading-text {
            margin-top: 1rem;
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }

        .loading-overlay .loading-subtext {
            margin-top: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-container">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <div class="loading-text" id="loadingText">جاري المعالجة...</div>
            <div class="loading-subtext" id="loadingSubtext">يرجى الانتظار وعدم إغلاق الصفحة</div>
        </div>
    </div>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                <i class="bi bi-whatsapp me-1"></i>زندر
            </a>

            @auth
                <div class="dropdown">
                    <button class="btn btn-dark dropdown-toggle d-flex align-items-center gap-2 py-1 px-2" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="d-none d-sm-inline small">{{ Auth::user()->phone }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="d-sm-none">
                            <span class="dropdown-item-text text-muted small">
                                <i class="bi bi-phone me-1"></i>{{ Auth::user()->phone }}
                            </span>
                        </li>
                        <li class="d-sm-none">
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('settings.index') }}">
                                <i class="bi bi-gear me-2"></i>الإعدادات
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </nav>

    @auth
        <!-- Sidebar - Desktop Only -->
        <nav class="sidebar d-none d-lg-block">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        لوحة التحكم
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}"
                        href="{{ route('contacts.index') }}">
                        <i class="bi bi-people"></i>
                        جهات الاتصال
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}"
                        href="{{ route('campaigns.create') }}">
                        <i class="bi bi-megaphone"></i>
                        الحملات
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    @if ($errors->count() == 1)
                        {{ $errors->first() }}
                    @else
                        <ul class="mb-0 pe-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Mobile Bottom Navigation -->
        <nav class="mobile-nav d-lg-none">
            <div class="container-fluid">
                <div class="row g-0 text-center">
                    <div class="col-4">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            الرئيسية
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}"
                            href="{{ route('contacts.index') }}">
                            <i class="bi bi-people"></i>
                            جهات الاتصال
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}"
                            href="{{ route('campaigns.create') }}">
                            <i class="bi bi-megaphone"></i>
                            الحملات
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    @else
        <!-- Guest Content -->
        <div class="container">
            @if (session('success'))
                <div class="auth-card">
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="auth-card">
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    @endauth

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <!-- Loading Overlay Functions -->
    <script>
        function showLoadingOverlay(text = 'جاري المعالجة...', subtext = 'يرجى الانتظار وعدم إغلاق الصفحة') {
            const overlay = document.getElementById('loadingOverlay');
            const loadingText = document.getElementById('loadingText');
            const loadingSubtext = document.getElementById('loadingSubtext');

            if (loadingText) loadingText.textContent = text;
            if (loadingSubtext) loadingSubtext.textContent = subtext;
            if (overlay) overlay.classList.add('active');

            // Prevent scrolling and any interaction
            document.body.style.overflow = 'hidden';
            document.body.style.pointerEvents = 'none';
            if (overlay) overlay.style.pointerEvents = 'auto';
        }

        function hideLoadingOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
            document.body.style.pointerEvents = '';
        }

        // Only hide overlay when navigating back from bfcache (back button)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was restored from bfcache (user pressed back button)
                hideLoadingOverlay();
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
