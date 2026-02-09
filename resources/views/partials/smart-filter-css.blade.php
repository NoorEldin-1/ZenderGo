<style>
    /* ========== SMART FILTER DROPDOWN ========== */
    .smart-filter-dropdown .smart-filter-btn {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        padding: 0.4rem 0.85rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--bs-body-color);
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .smart-filter-dropdown .smart-filter-btn:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.15);
    }

    .smart-filter-dropdown .smart-filter-btn:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.2);
    }

    .smart-filter-dropdown .smart-filter-btn .filter-icon {
        font-size: 0.9rem;
    }

    .smart-filter-dropdown .smart-filter-btn .dropdown-chevron {
        font-size: 0.7rem;
        transition: transform 0.2s ease;
        margin-right: -0.25rem;
    }

    .smart-filter-dropdown.show .smart-filter-btn .dropdown-chevron {
        transform: rotate(180deg);
    }

    /* Outer menu: transparent container, no animation (Popper controls position) */
    .smart-filter-dropdown .smart-filter-menu {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0;
        min-width: 220px;
    }

    /* Inner content: all visual styles + animation */
    .smart-filter-dropdown .smart-filter-content {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 0.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        transform-origin: top center;
        animation: smartFilterFadeIn 0.15s ease-out;
    }

    @keyframes smartFilterFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .smart-filter-dropdown .smart-filter-item {
        padding: 0.6rem 0.85rem;
        font-size: 0.875rem;
        transition: all 0.15s ease;
        color: var(--bs-body-color);
        margin-bottom: 2px;
    }

    .smart-filter-dropdown .smart-filter-item:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    .smart-filter-dropdown .smart-filter-item.active {
        background-color: var(--bs-primary);
        color: #fff;
    }

    .smart-filter-dropdown .smart-filter-item.active i {
        color: #fff !important;
    }

    /* Dark Mode Styles */
    [data-bs-theme="dark"] .smart-filter-dropdown .smart-filter-btn {
        background: #2b3035;
        border-color: #495057;
    }

    [data-bs-theme="dark"] .smart-filter-dropdown .smart-filter-btn:hover {
        border-color: var(--bs-primary);
        background: #343a40;
    }

    [data-bs-theme="dark"] .smart-filter-dropdown .smart-filter-content {
        background: #212529;
        border-color: #343a40;
    }

    [data-bs-theme="dark"] .smart-filter-dropdown .smart-filter-item:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.2);
    }

    /* ========== END SMART FILTER ========== */
</style>
