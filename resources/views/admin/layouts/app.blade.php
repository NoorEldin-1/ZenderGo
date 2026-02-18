<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="{{ $currentTheme ?? 'light' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#25D366">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'لوحة الإدارة') | زندر Admin</title>

    <!-- Favicon Set -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

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
            --sidebar-collapsed-width: 80px;
        }

        /* ========== DARK MODE THEME ========== */
        :root[data-bs-theme="dark"] {
            --bs-body-bg: #121518;
            --bs-body-color: #e9ecef;
            --bs-secondary-bg: #1a1d21;
            --bs-tertiary-bg: #212529;
            --bs-border-color: #495057;
            --bs-card-bg: #1a1d21;
            --bs-modal-bg: #1a1d21;
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
            --text-muted: #adb5bd;
        }

        [data-bs-theme="dark"] body {
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
        }

        /* Dark Mode: Cards */
        [data-bs-theme="dark"] .card {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
            box-shadow: var(--card-shadow);
        }

        [data-bs-theme="dark"] .card-header {
            background-color: #212529 !important;
            border-bottom-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }

        [data-bs-theme="dark"] .card-body {
            background-color: var(--bs-card-bg) !important;
            color: var(--bs-body-color) !important;
        }

        [data-bs-theme="dark"] .card-footer {
            background-color: #212529 !important;
            border-top-color: var(--bs-border-color) !important;
        }

        /* Dark Mode: Form Controls */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #212529 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #6c757d !important;
        }

        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #2b3035 !important;
            border-color: var(--whatsapp-green) !important;
            color: #fff !important;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.25) !important;
        }

        [data-bs-theme="dark"] .form-control:disabled,
        [data-bs-theme="dark"] .form-control[readonly] {
            background-color: #343a40 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .input-group-text {
            background-color: #343a40 !important;
            border-color: #495057 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .form-check-input {
            background-color: #343a40 !important;
            border-color: #6c757d !important;
        }

        [data-bs-theme="dark"] .form-check-input:checked {
            background-color: var(--whatsapp-green) !important;
            border-color: var(--whatsapp-green) !important;
        }

        [data-bs-theme="dark"] .form-label {
            color: #e9ecef !important;
        }

        /* Dark Mode: Tables */
        [data-bs-theme="dark"] .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e9ecef;
            --bs-table-border-color: #495057;
            --bs-table-striped-bg: rgba(255, 255, 255, 0.03);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .table thead th {
            background-color: #212529 !important;
            color: #adb5bd !important;
            border-bottom-color: #495057 !important;
        }

        [data-bs-theme="dark"] .table tbody td {
            border-bottom-color: #343a40 !important;
        }

        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        /* Dark Mode: Dropdowns */
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #212529 !important;
            border-color: #495057 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus {
            background-color: #343a40 !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .dropdown-divider {
            border-color: #495057 !important;
        }

        /* Dark Mode: Modals */
        [data-bs-theme="dark"] .modal-content {
            background-color: #1a1d21 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .modal-header {
            border-bottom-color: #495057 !important;
            background-color: #212529 !important;
        }

        [data-bs-theme="dark"] .modal-footer {
            border-top-color: #495057 !important;
            background-color: #212529 !important;
        }

        [data-bs-theme="dark"] .modal-title {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Dark Mode: Alerts */
        [data-bs-theme="dark"] .alert-success {
            background-color: rgba(37, 211, 102, 0.15) !important;
            border-color: rgba(37, 211, 102, 0.3) !important;
            color: #5fd98b !important;
        }

        [data-bs-theme="dark"] .alert-danger {
            background-color: rgba(220, 53, 69, 0.15) !important;
            border-color: rgba(220, 53, 69, 0.3) !important;
            color: #f08090 !important;
        }

        [data-bs-theme="dark"] .alert-warning {
            background-color: rgba(255, 193, 7, 0.15) !important;
            border-color: rgba(255, 193, 7, 0.3) !important;
            color: #ffc107 !important;
        }

        [data-bs-theme="dark"] .alert-info {
            background-color: rgba(13, 202, 240, 0.15) !important;
            border-color: rgba(13, 202, 240, 0.3) !important;
            color: #4dd4f0 !important;
        }

        /* Dark Mode: Buttons */
        [data-bs-theme="dark"] .btn-outline-secondary {
            color: #adb5bd !important;
            border-color: #6c757d !important;
        }

        [data-bs-theme="dark"] .btn-outline-secondary:hover {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .btn-light {
            background-color: #343a40 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .btn-light:hover {
            background-color: #495057 !important;
            color: #fff !important;
        }

        /* Dark Mode: Pagination */
        [data-bs-theme="dark"] .pagination .page-link {
            background-color: #212529 !important;
            border-color: #495057 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .pagination .page-link:hover {
            background-color: #343a40 !important;
            color: var(--whatsapp-green) !important;
        }

        [data-bs-theme="dark"] .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%) !important;
            border-color: var(--whatsapp-green) !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .pagination .page-item.disabled .page-link {
            background-color: #1a1d21 !important;
            border-color: #343a40 !important;
            color: #6c757d !important;
        }

        /* Dark Mode: Text Utilities */
        [data-bs-theme="dark"] .text-muted {
            color: #8c959f !important;
        }

        [data-bs-theme="dark"] .text-dark {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: #212529 !important;
        }

        [data-bs-theme="dark"] .bg-white {
            background-color: #1a1d21 !important;
        }

        [data-bs-theme="dark"] .border {
            border-color: #495057 !important;
        }

        /* Dark Mode: Stat Cards (Dashboard) */
        [data-bs-theme="dark"] .stat-card {
            background: #1a1d21 !important;
            box-shadow: var(--card-shadow);
        }

        [data-bs-theme="dark"] .stat-card .stat-value {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .stat-card .stat-label {
            color: #adb5bd !important;
        }

        /* Global Checkbox/Radio Enhancement */
        .form-check-input:checked {
            background-color: var(--whatsapp-green);
            border-color: var(--whatsapp-green);
        }

        /* Global Scrollbar - Apply to both Light and Dark modes */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Dark Mode: Scrollbar Overrides */
        [data-bs-theme="dark"] ::-webkit-scrollbar-track {
            background: #1a1d21;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background: #495057;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background: #6c757d;
        }

        /* Dark Mode: Links */
        [data-bs-theme="dark"] a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link) {
            color: #6ea8fe;
        }

        [data-bs-theme="dark"] a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link):hover {
            color: #9ec5fe;
        }

        /* Dark Mode: Headings */
        [data-bs-theme="dark"] h1,
        [data-bs-theme="dark"] h2,
        [data-bs-theme="dark"] h3,
        [data-bs-theme="dark"] h4,
        [data-bs-theme="dark"] h5,
        [data-bs-theme="dark"] h6,
        [data-bs-theme="dark"] .h1,
        [data-bs-theme="dark"] .h2,
        [data-bs-theme="dark"] .h3,
        [data-bs-theme="dark"] .h4,
        [data-bs-theme="dark"] .h5,
        [data-bs-theme="dark"] .h6 {
            color: #fff !important;
        }

        /* Dark Mode: List Groups */
        [data-bs-theme="dark"] .list-group-item {
            background-color: #1a1d21 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .list-group-item:hover {
            background-color: #212529 !important;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .theme-toggle:hover {
            background: rgba(37, 211, 102, 0.2);
            border-color: var(--whatsapp-green);
            transform: rotate(15deg);
        }

        .theme-toggle i {
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .theme-toggle i.bi-sun-fill {
            color: #ffc107;
        }

        [data-bs-theme="light"] .theme-toggle i.bi-moon-fill {
            color: #6c757d;
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

        /* Sidebar - Desktop */
        @media (min-width: 992px) {
            .sidebar {
                width: var(--sidebar-width);
                min-height: calc(100vh - 60px);
                background: linear-gradient(180deg, #1a1d21 0%, #212529 100%);
                position: fixed;
                top: 60px;
                right: 0;
                padding-top: 1rem;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1000;
                /* Overflow moved to inner container to allow button to pop out */
            }

            .sidebar-scroll-container {
                height: calc(100vh - 80px);
                /* Adjust for header + padding */
                overflow-y: auto;
                overflow-x: hidden;
                padding-bottom: 2rem;
            }

            .sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }

            /* Sidebar Toggle Button */
            .sidebar-toggle-btn {
                position: absolute;
                top: 24px;
                left: -14px;
                /* Half outside */
                width: 28px;
                height: 28px;
                background: #fff;
                border: 1px solid #dee2e6;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 1010;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                padding: 0;
                transition: all 0.2s ease;
                color: #6c757d;
            }

            .sidebar-toggle-btn:hover {
                background: #f8f9fa;
                color: var(--whatsapp-green);
                transform: scale(1.1);
            }

            [data-bs-theme="dark"] .sidebar-toggle-btn {
                background: #212529;
                border-color: #495057;
                color: #adb5bd;
            }

            [data-bs-theme="dark"] .sidebar-toggle-btn:hover {
                background: #343a40;
                color: #fff;
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
                white-space: nowrap;
                overflow: hidden;
            }

            .sidebar.collapsed .nav-link {
                padding: 0.875rem 0;
                justify-content: center;
                margin: 4px 10px;
            }

            .sidebar.collapsed .nav-link span {
                opacity: 0;
                width: 0;
                display: none;
            }

            .sidebar.collapsed .nav-link i {
                margin: 0;
                font-size: 1.3rem;
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
                transition: all 0.2s;
            }

            /* Main Content */
            .main-content {
                margin-right: var(--sidebar-width);
                min-height: calc(100vh - 60px);
                padding: 1.5rem;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .main-content.expanded {
                margin-right: var(--sidebar-collapsed-width);
            }
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
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-whatsapp me-1"></i>زندر
            </a>

            @if (auth('admin')->check())
                <!-- Admin Actions Group (Theme Toggle + Dropdown) -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Theme Toggle Button -->
                    <button class="btn theme-toggle" type="button" id="themeToggle"
                        title="{{ ($currentTheme ?? 'light') === 'dark' ? 'التبديل للوضع الفاتح' : 'التبديل للوضع الداكن' }}">
                        <i class="bi {{ ($currentTheme ?? 'light') === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill' }}"
                            id="themeIcon"></i>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle d-flex align-items-center gap-2 py-1 px-2"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-shield-lock fs-5"></i>
                            <span class="d-none d-sm-inline small">{{ auth('admin')->user()->phone }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="d-sm-none">
                                <span class="dropdown-item-text text-muted small">
                                    <i class="bi bi-phone me-1"></i>{{ auth('admin')->user()->phone }}
                                </span>
                            </li>
                            <li class="d-sm-none">
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                    data-bs-target="#logoutModal">
                                    <i class="bi bi-box-arrow-right me-2"></i>تسجيل الخروج
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </nav>

    @if (auth('admin')->check())
        <!-- Sidebar - Desktop Only -->
        <!-- Sidebar - Desktop Only -->
        <nav class="sidebar d-none d-lg-block" id="sidebar">
            <button class="sidebar-toggle-btn" id="sidebarToggle">
                <i class="bi bi-chevron-right"></i>
            </button>

            <div class="sidebar-scroll-container">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>لوحة الإدارة</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i>
                            <span>إدارة المستخدمين</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.payment-requests.*') ? 'active' : '' }}"
                            href="{{ route('admin.payment-requests.index') }}">
                            <i class="bi bi-credit-card"></i>
                            <span>طلبات الدفع</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                            href="{{ route('admin.settings') }}">
                            <i class="bi bi-sliders"></i>
                            <span>الإعدادات</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
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
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            الرئيسية
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i>
                            المستخدمين
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('admin.payment-requests.*') ? 'active' : '' }}"
                            href="{{ route('admin.payment-requests.index') }}">
                            <i class="bi bi-credit-card"></i>
                            الدفع
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                            href="{{ route('admin.settings') }}">
                            <i class="bi bi-sliders"></i>
                            الإعدادات
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    @else
        <!-- Guest Content (Login Page) -->
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

    @endif

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                            style="width: 70px; height: 70px;">
                            <i class="bi bi-box-arrow-right text-danger"
                                style="font-size: 2rem; margin-left: 3px;"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-2">تسجيل الخروج</h6>
                    <p class="text-muted small mb-0">هل أنت متأكد أنك تريد تسجيل الخروج من لوحة الإدارة؟</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">إلغاء</button>
                    <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                        @csrf
                </div>
            </div>
        </div>

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Sidebar Toggle Logic
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                const toggleBtn = document.getElementById('sidebarToggle');
                const toggleIcon = toggleBtn ? toggleBtn.querySelector('i') : null;

                if (sidebar && mainContent && toggleBtn) {
                    // Load saved state
                    const isCollapsed = localStorage.getItem('adminSidebarState') === 'collapsed';
                    if (isCollapsed) {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('expanded');
                        if (toggleIcon) {
                            toggleIcon.classList.remove('bi-chevron-right');
                            toggleIcon.classList.add('bi-chevron-left');
                        }
                    }

                    // Toggle click handler
                    toggleBtn.addEventListener('click', function() {
                        sidebar.classList.toggle('collapsed');
                        mainContent.classList.toggle('expanded');

                        // Save state
                        const collapsed = sidebar.classList.contains('collapsed');
                        localStorage.setItem('adminSidebarState', collapsed ? 'collapsed' : 'expanded');

                        // Update icon
                        if (toggleIcon) {
                            if (collapsed) {
                                toggleIcon.classList.remove('bi-chevron-right');
                                toggleIcon.classList.add('bi-chevron-left');
                            } else {
                                toggleIcon.classList.remove('bi-chevron-left');
                                toggleIcon.classList.add('bi-chevron-right');
                            }
                        }
                    });
                }

                // Theme Toggle Logic
                const themeToggle = document.getElementById('themeToggle');
                const themeIcon = document.getElementById('themeIcon');
                const htmlElement = document.documentElement;

                if (themeToggle) {
                    themeToggle.addEventListener('click', () => {
                        const currentTheme = htmlElement.getAttribute('data-bs-theme');
                        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                        htmlElement.setAttribute('data-bs-theme', newTheme);

                        // Update Icon
                        if (newTheme === 'dark') {
                            themeIcon.classList.remove('bi-moon-fill');
                            themeIcon.classList.add('bi-sun-fill');
                            themeToggle.title = 'التبديل للوضع الفاتح';
                        } else {
                            themeIcon.classList.remove('bi-sun-fill');
                            themeIcon.classList.add('bi-moon-fill');
                            themeToggle.title = 'التبديل للوضع الداكن';
                        }

                        // Save preference via AJAX if needed, or localStorage
                        // localStorage.setItem('theme', newTheme);
                    });
                }
            });
        </script>

        @stack('scripts')
</body>

</html>
