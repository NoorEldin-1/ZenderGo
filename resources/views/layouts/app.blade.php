<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="{{ $currentTheme ?? 'light' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#25D366">
    <title>@yield('title', 'زندر') - تسويق واتساب</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

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

        /* Dark Mode: Loading Overlay */
        [data-bs-theme="dark"] .loading-overlay .spinner-container {
            background: #212529 !important;
        }

        [data-bs-theme="dark"] .loading-overlay .loading-text {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .loading-overlay .loading-subtext {
            color: #adb5bd !important;
        }

        /* Dark Mode: Scrollbar */
        [data-bs-theme="dark"] ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-track {
            background: #1a1d21;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background: #495057;
            border-radius: 5px;
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

        /* ========== PAGE-SPECIFIC DARK MODE OVERRIDES ========== */

        /* Subscription Page */
        [data-bs-theme="dark"] .subscription-card,
        [data-bs-theme="dark"] .subscription-container .card {
            background: #1a1d21 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .info-card {
            background: #212529 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .info-card:hover {
            background: #2b3035 !important;
        }

        [data-bs-theme="dark"] .info-card .icon {
            background: #343a40 !important;
        }

        [data-bs-theme="dark"] .trial-countdown,
        [data-bs-theme="dark"] .trial-notice {
            background: #212529 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .progress-container {
            background: #343a40 !important;
        }

        /* Admin Settings Page */
        [data-bs-theme="dark"] .settings-card {
            background: #1a1d21 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .setting-group {
            background: #212529 !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .setting-label {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .setting-description {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .icon-box {
            background: #343a40 !important;
        }

        /* Guide Page */
        [data-bs-theme="dark"] .guide-container .card,
        [data-bs-theme="dark"] .guide-step {
            background: #1a1d21 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .step-content {
            background: #212529 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .feature-box {
            background: #212529 !important;
            border-color: #495057 !important;
        }

        /* Campaigns Page */
        [data-bs-theme="dark"] .message-editor,
        [data-bs-theme="dark"] #messageEditor,
        [data-bs-theme="dark"] [contenteditable="true"] {
            background: #212529 !important;
            color: #e9ecef !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .emoji-picker {
            background: #1a1d21 !important;
        }

        [data-bs-theme="dark"] .emoji-category-btn {
            background: #212529 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .emoji-category-btn.active,
        [data-bs-theme="dark"] .emoji-category-btn:hover {
            background: #343a40 !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .contact-item {
            border-color: #343a40 !important;
        }

        [data-bs-theme="dark"] .contact-item:hover {
            background: #212529 !important;
        }

        [data-bs-theme="dark"] #contactsListContainer {
            background: #1a1d21 !important;
        }

        /* Dashboard Cards with Inline Styles */
        [data-bs-theme="dark"] .dashboard-card,
        [data-bs-theme="dark"] .quick-action-card {
            background: #1a1d21 !important;
        }

        /* Badge Overrides */
        [data-bs-theme="dark"] .badge.bg-light {
            background-color: #343a40 !important;
            color: #e9ecef !important;
        }

        /* Input/Textarea with inline white background */
        [data-bs-theme="dark"] textarea,
        [data-bs-theme="dark"] input[type="text"],
        [data-bs-theme="dark"] input[type="number"],
        [data-bs-theme="dark"] input[type="email"],
        [data-bs-theme="dark"] input[type="password"],
        [data-bs-theme="dark"] input[type="search"],
        [data-bs-theme="dark"] input[type="tel"] {
            background-color: #212529 !important;
            color: #e9ecef !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] textarea::placeholder,
        [data-bs-theme="dark"] input::placeholder {
            color: #6c757d !important;
        }

        /* Background color overrides for elements with style="background: white" */
        [data-bs-theme="dark"] [style*="background: white"],
        [data-bs-theme="dark"] [style*="background:white"],
        [data-bs-theme="dark"] [style*="background-color: white"],
        [data-bs-theme="dark"] [style*="background-color:white"],
        [data-bs-theme="dark"] [style*="background: #fff"],
        [data-bs-theme="dark"] [style*="background:#fff"],
        [data-bs-theme="dark"] [style*="background-color: #fff"],
        [data-bs-theme="dark"] [style*="background-color:#fff"] {
            background: #1a1d21 !important;
            color: #e9ecef !important;
        }

        /* Card header with bg-white class */
        [data-bs-theme="dark"] .card-header.bg-white {
            background-color: #212529 !important;
        }

        /* Card footer with bg-white class */
        [data-bs-theme="dark"] .card-footer.bg-white {
            background-color: #212529 !important;
        }

        /* Toolbar bg-light */
        [data-bs-theme="dark"] .bg-light {
            background-color: #212529 !important;
        }

        /* Text colors that might be invisible */
        [data-bs-theme="dark"] .text-dark {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] p,
        [data-bs-theme="dark"] span:not(.badge):not(.status-badge) {
            color: inherit;
        }

        /* Campaign Page - Quota Widget */
        [data-bs-theme="dark"] .quota-widget {
            background: linear-gradient(135deg, rgba(26, 29, 33, 0.9) 0%, rgba(33, 37, 41, 0.9) 100%) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .quota-widget .progress {
            background-color: #343a40 !important;
        }

        [data-bs-theme="dark"] .quota-widget .text-muted {
            color: #adb5bd !important;
        }

        /* Campaign Page - Message Editor Container */
        [data-bs-theme="dark"] .message-editor-container {
            background: linear-gradient(135deg, #1a1d21 0%, #212529 100%) !important;
        }

        [data-bs-theme="dark"] .message-editor-container:focus-within {
            background: linear-gradient(135deg, #212529 0%, #2b3035 100%) !important;
        }

        [data-bs-theme="dark"] .rich-message-editor {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .rich-message-editor:empty::before {
            color: #6c757d !important;
        }

        /* Loading Indicator */
        [data-bs-theme="dark"] #loadingIndicator {
            background: #1a1d21 !important;
        }

        /* Image Preview Items */
        [data-bs-theme="dark"] .image-preview-item {
            background: linear-gradient(135deg, #212529 0%, #2b3035 100%) !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .image-preview-item .image-name {
            color: #adb5bd !important;
        }

        /* ========== DASHBOARD PAGE DARK MODE ========== */
        [data-bs-theme="dark"] .dashboard-card {
            background: #1a1d21 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
        }

        [data-bs-theme="dark"] .dashboard-card .card-body {
            background: #1a1d21 !important;
        }

        [data-bs-theme="dark"] .dashboard-card .card-title,
        [data-bs-theme="dark"] .dashboard-card .card-stat {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .dashboard-card .card-text {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .dashboard-card.highlight {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.15) 0%, #1a1d21 100%) !important;
            border-color: rgba(37, 211, 102, 0.3) !important;
        }

        [data-bs-theme="dark"] .dashboard-card .icon-wrapper.primary {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.25), rgba(13, 110, 253, 0.1)) !important;
        }

        [data-bs-theme="dark"] .dashboard-card .icon-wrapper.success {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.3), rgba(37, 211, 102, 0.1)) !important;
        }

        [data-bs-theme="dark"] .dashboard-card .icon-wrapper.info {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.25), rgba(13, 202, 240, 0.1)) !important;
        }

        [data-bs-theme="dark"] .dashboard-card .card-btn.btn-primary-soft {
            background: rgba(13, 110, 253, 0.2) !important;
        }

        [data-bs-theme="dark"] .dashboard-card .card-btn.btn-success-soft {
            background: rgba(37, 211, 102, 0.2) !important;
        }

        [data-bs-theme="dark"] .dashboard-card .card-btn.btn-info-soft {
            background: rgba(13, 202, 240, 0.2) !important;
        }

        /* ========== GUIDE PAGE DARK MODE ========== */
        [data-bs-theme="dark"] .progress-container {
            background: rgba(26, 29, 33, 0.95) !important;
            backdrop-filter: blur(10px);
        }

        [data-bs-theme="dark"] .progress-step {
            background: #212529 !important;
            border-color: #495057 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .progress-step-label {
            background: #212529 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .progress-steps::before {
            background: #495057 !important;
        }

        [data-bs-theme="dark"] .step-card {
            background: #1a1d21 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .step-title {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .step-description {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .benefits-list {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.1), rgba(37, 211, 102, 0.05)) !important;
        }

        [data-bs-theme="dark"] .benefit-text strong {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .benefit-text span {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .substeps-accordion {
            background: #212529 !important;
        }

        [data-bs-theme="dark"] .substep-header {
            background: #1a1d21 !important;
            color: #e9ecef !important;
            border-bottom-color: #495057 !important;
        }

        [data-bs-theme="dark"] .substep-header:hover {
            background: #2b3035 !important;
        }

        [data-bs-theme="dark"] .substep-body {
            background: #1a1d21 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .substep-list li {
            color: #adb5bd !important;
            border-bottom-color: #495057 !important;
        }

        [data-bs-theme="dark"] .substep-list li::before {
            background: #343a40 !important;
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .tips-section {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.15), rgba(255, 193, 7, 0.1)) !important;
            border-color: rgba(255, 193, 7, 0.3) !important;
        }

        [data-bs-theme="dark"] .tips-title {
            color: #ffc107 !important;
        }

        [data-bs-theme="dark"] .tip-item {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .timeline::before {
            background: linear-gradient(180deg, var(--whatsapp-green), var(--whatsapp-dark), #667eea) !important;
        }

        /* ========== SUBSCRIPTION PAGE DARK MODE FIX ========== */
        [data-bs-theme="dark"] .info-card {
            background: #212529 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .info-card .value,
        [data-bs-theme="dark"] .info-cards .info-card .value,
        [data-bs-theme="dark"] .subscription-card .info-card .value {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .info-card .label,
        [data-bs-theme="dark"] .info-cards .info-card .label,
        [data-bs-theme="dark"] .subscription-card .info-card .label {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .info-card .icon {
            background: #343a40 !important;
        }

        [data-bs-theme="dark"] .subscription-card {
            background: #1a1d21 !important;
        }

        [data-bs-theme="dark"] .subscription-active-card,
        [data-bs-theme="dark"] .active-subscription-card {
            background: #1a1d21 !important;
            border-color: var(--whatsapp-green) !important;
        }

        [data-bs-theme="dark"] .trial-countdown {
            background: #212529 !important;
        }

        [data-bs-theme="dark"] .countdown-item {
            background: #343a40 !important;
        }

        [data-bs-theme="dark"] .countdown-item .value {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .countdown-item .label {
            color: #adb5bd !important;
        }

        /* Subscription Page - History Table */
        [data-bs-theme="dark"] .history-table {
            background: #1a1d21 !important;
        }

        [data-bs-theme="dark"] .history-table table {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .history-table th,
        [data-bs-theme="dark"] .history-table td,
        [data-bs-theme="dark"] .payment-history-table th,
        [data-bs-theme="dark"] .payment-history-table td {
            color: #e9ecef !important;
            border-color: #495057 !important;
            background: transparent !important;
        }

        [data-bs-theme="dark"] .history-table thead th {
            background: #343a40 !important;
            color: #fff !important;
        }

        /* Subscription Page - Payment Section */
        [data-bs-theme="dark"] .payment-section {
            background: linear-gradient(135deg, #212529 0%, #1a1d21 100%) !important;
        }

        [data-bs-theme="dark"] .payment-section h6 {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .upload-area {
            background: #1a1d21 !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .upload-area i,
        [data-bs-theme="dark"] .upload-area p {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .payment-tab {
            background: #1a1d21 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .payment-tab:hover {
            background: #2b3035 !important;
        }

        /* Trial Notice */
        [data-bs-theme="dark"] .trial-notice {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.15) 0%, rgba(23, 162, 184, 0.1) 100%) !important;
            border-color: #17a2b8 !important;
        }

        [data-bs-theme="dark"] .trial-notice h5 {
            color: #17a2b8 !important;
        }

        /* Progress Label */
        [data-bs-theme="dark"] .progress-label,
        [data-bs-theme="dark"] .progress-label span {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .progress-bar-custom {
            background: #343a40 !important;
        }

        /* ========== ADMIN PANEL DARK MODE ========== */
        [data-bs-theme="dark"] .stat-box {
            background: #1a1d21 !important;
        }

        [data-bs-theme="dark"] .stat-box .value {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .stat-box .label {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .filter-card {
            background: #1a1d21 !important;
        }

        /* Admin Detail Page Dark Mode */
        [data-bs-theme="dark"] .detail-card {
            background: #1a1d21 !important;
        }

        [data-bs-theme="dark"] .detail-card h6 {
            color: #fff !important;
            border-bottom-color: #495057 !important;
        }

        [data-bs-theme="dark"] .detail-row {
            border-bottom-color: #495057 !important;
        }

        [data-bs-theme="dark"] .detail-row .label {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .detail-row .value {
            color: #fff !important;
        }

        [data-bs-theme="dark"] .action-card {
            background: linear-gradient(135deg, #212529 0%, #1a1d21 100%) !important;
        }

        [data-bs-theme="dark"] .action-card h6 {
            color: #fff !important;
        }

        /* Status Badges Dark Mode */
        [data-bs-theme="dark"] .status-pending {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.2) 0%, rgba(255, 193, 7, 0.1) 100%) !important;
            border-color: #ffc107 !important;
            color: #ffc107 !important;
        }

        [data-bs-theme="dark"] .status-approved {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.2) 0%, rgba(40, 167, 69, 0.1) 100%) !important;
            border-color: #28a745 !important;
            color: #28a745 !important;
        }

        [data-bs-theme="dark"] .status-rejected {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2) 0%, rgba(220, 53, 69, 0.1) 100%) !important;
            border-color: #dc3545 !important;
            color: #f8d7da !important;
        }

        /* Alert Secondary Dark Mode */
        [data-bs-theme="dark"] .alert-secondary {
            background: #343a40 !important;
            border-color: #495057 !important;
            color: #adb5bd !important;
        }

        /* Modal Dark Mode */
        [data-bs-theme="dark"] .modal-content {
            background: #1a1d21 !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .modal-header {
            border-bottom-color: #495057 !important;
        }

        [data-bs-theme="dark"] .modal-body {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .modal-footer {
            border-top-color: #495057 !important;
        }

        /* Form Controls Dark Mode */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] input.form-control,
        [data-bs-theme="dark"] select.form-select {
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
            border-color: #25D366 !important;
            color: #fff !important;
            box-shadow: 0 0 0 0.25rem rgba(37, 211, 102, 0.25) !important;
        }

        [data-bs-theme="dark"] .form-label {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .input-group-text {
            background-color: #343a40 !important;
            border-color: #495057 !important;
            color: #e9ecef !important;
        }

        /* Admin Tables */
        [data-bs-theme="dark"] .table {
            color: #e9ecef !important;
        }

        [data-bs-theme="dark"] .table thead th {
            background: #343a40 !important;
            color: #fff !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .table tbody td {
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .table-warning {
            background: rgba(255, 193, 7, 0.1) !important;
        }

        [data-bs-theme="dark"] .table-responsive {
            background: transparent !important;
        }

        /* Cards in Dark Mode */
        [data-bs-theme="dark"] .card {
            background: #1a1d21 !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .card-body {
            background: transparent !important;
        }

        [data-bs-theme="dark"] .card-footer {
            background: #212529 !important;
            border-color: #495057 !important;
        }

        /* Alerts Dark Mode Compatibility */
        [data-bs-theme="dark"] .alert-warning {
            background: rgba(255, 193, 7, 0.15) !important;
            border-color: rgba(255, 193, 7, 0.3) !important;
            color: #ffc107 !important;
        }

        [data-bs-theme="dark"] .alert-success {
            background: rgba(37, 211, 102, 0.15) !important;
            border-color: rgba(37, 211, 102, 0.3) !important;
            color: #25D366 !important;
        }

        [data-bs-theme="dark"] .alert-danger {
            background: rgba(220, 53, 69, 0.15) !important;
            border-color: rgba(220, 53, 69, 0.3) !important;
            color: #f8d7da !important;
        }

        [data-bs-theme="dark"] .alert-info {
            background: rgba(13, 202, 240, 0.15) !important;
            border-color: rgba(13, 202, 240, 0.3) !important;
            color: #0dcaf0 !important;
        }

        /* Expired Alert on Subscription - Override inline styles */
        [data-bs-theme="dark"] .expired-alert,
        [data-bs-theme="dark"] .subscription-card .expired-alert {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.2) 0%, rgba(255, 193, 7, 0.1) 100%) !important;
            border-color: #ffc107 !important;
        }

        [data-bs-theme="dark"] .expired-alert h5,
        [data-bs-theme="dark"] .expired-alert h6,
        [data-bs-theme="dark"] [style*="color: #856404"],
        [data-bs-theme="dark"] [style*="color: #721c24"] {
            color: #ffc107 !important;
        }

        [data-bs-theme="dark"] .expired-alert p,
        [data-bs-theme="dark"] .expired-alert span {
            color: #e9ecef !important;
        }

        /* Override inline background styles on alerts */
        [data-bs-theme="dark"] div[style*="background: linear-gradient(135deg, #fff3cd"],
        [data-bs-theme="dark"] div[style*="background: linear-gradient(135deg, #ffeeba"],
        [data-bs-theme="dark"] div[style*="background: linear-gradient(135deg, #fff5f5"],
        [data-bs-theme="dark"] div[style*="background: linear-gradient(135deg, #f8d7da"] {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.25) 0%, rgba(255, 193, 7, 0.15) 100%) !important;
        }

        /* Override inline text colors on alerts */
        [data-bs-theme="dark"] h5[style*="color:"],
        [data-bs-theme="dark"] .expired-alert *[style*="color:"] {
            color: #ffc107 !important;
        }

        /* Pending Alert */
        [data-bs-theme="dark"] .pending-alert {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 193, 7, 0.1) 100%) !important;
            border-color: rgba(255, 193, 7, 0.3) !important;
        }

        [data-bs-theme="dark"] .pending-alert h6 {
            color: #ffc107 !important;
        }

        /* ========== ADDITIONAL TEXT VISIBILITY FIXES ========== */
        [data-bs-theme="dark"] strong {
            color: inherit;
        }

        [data-bs-theme="dark"] .small,
        [data-bs-theme="dark"] small {
            color: inherit;
        }

        [data-bs-theme="dark"] code {
            background: #343a40 !important;
            color: #f8d7da !important;
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

        /* Support link in nav */
        .nav-link.support-link {
            color: rgba(37, 211, 102, 0.9) !important;
        }

        .nav-link.support-link:hover {
            color: #25D366 !important;
            background: rgba(37, 211, 102, 0.15);
        }

        .mobile-nav .nav-link.support-link {
            color: rgba(37, 211, 102, 0.9) !important;
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
                <!-- User Actions Group (Theme Toggle + Dropdown) -->
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
                                <a class="dropdown-item" href="{{ route('password.change') }}">
                                    <i class="bi bi-key me-2"></i>تغيير كلمة المرور
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
                </div>

                <!-- WhatsApp Connection Status Indicator -->
                <div id="whatsappStatusIndicator" class="d-none ms-2">
                    <a href="{{ route('login.reconnect') }}"
                        class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1" title="اضغط لإعادة الربط">
                        <i class="bi bi-wifi-off"></i>
                        <span class="d-none d-sm-inline">غير متصل</span>
                    </a>
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

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('guide') ? 'active' : '' }}" href="{{ route('guide') }}">
                        <i class="bi bi-book"></i>
                        دليل الاستخدام
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('subscription.*') ? 'active' : '' }}"
                        href="{{ route('subscription.index') }}">
                        <i class="bi bi-gem"></i>
                        اشتراكي
                    </a>
                </li>
                @php
                    $supportPhone = \App\Models\SystemSetting::getSupportPhoneNumber();
                @endphp
                <li class="nav-item mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <a class="nav-link support-link" href="https://wa.me/2{{ $supportPhone }}" target="_blank">
                        <i class="bi bi-whatsapp"></i>
                        تواصل مع الدعم
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
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            الرئيسية
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}"
                            href="{{ route('contacts.index') }}">
                            <i class="bi bi-people"></i>
                            جهات الاتصال
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}"
                            href="{{ route('campaigns.create') }}">
                            <i class="bi bi-megaphone"></i>
                            الحملات
                        </a>
                    </div>

                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('subscription.*') ? 'active' : '' }}"
                            href="{{ route('subscription.index') }}">
                            <i class="bi bi-gem"></i>
                            اشتراكي
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link {{ request()->routeIs('guide') ? 'active' : '' }}"
                            href="{{ route('guide') }}">
                            <i class="bi bi-book"></i>
                            الدليل
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link support-link" href="https://wa.me/2{{ $supportPhone }}" target="_blank">
                            <i class="bi bi-whatsapp"></i>
                            الدعم
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

    <!-- Theme Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const html = document.documentElement;

            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const currentTheme = html.getAttribute('data-bs-theme') || 'light';
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    // Immediately update UI for instant feedback
                    html.setAttribute('data-bs-theme', newTheme);

                    // Update icon
                    if (themeIcon) {
                        themeIcon.className = newTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                    }

                    // Update title
                    themeToggle.title = newTheme === 'dark' ? 'التبديل للوضع الفاتح' :
                        'التبديل للوضع الداكن';

                    // Determine the correct route based on current URL path
                    const isAdminPanel = window.location.pathname.startsWith('/admin');
                    const toggleUrl = isAdminPanel ? '/admin/theme/toggle' : '/theme/toggle';

                    // Save to database via AJAX
                    fetch(toggleUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                theme: newTheme
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                console.error('Failed to save theme preference');
                            }
                        })
                        .catch(error => {
                            console.error('Error saving theme:', error);
                        });
                });
            }
        });
    </script>



    @stack('scripts')
</body>

</html>
