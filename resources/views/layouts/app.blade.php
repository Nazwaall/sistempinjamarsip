<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dasbor') - SIPA Dinas Kearsipan & Perpustakaan Kab. Cirebon</title>
    
    <!-- PWA Web App Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-cirebon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-cirebon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Immediate Theme Setup (Mencegah kedipan putih/FOUC saat berpindah halaman) -->
    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('sipa_theme');
                const theme = storedTheme || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', theme);
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
    
    <!-- Custom SIPA Sidebar Layout Style -->
    <style>
        :root {
            --sidebar-width: 268px;
            --sidebar-collapsed-width: 80px;
            --sidebar-bg: #111c44;
            --sidebar-surface: #162660;
            --sidebar-border: rgba(255, 255, 255, 0.09);
            --sidebar-text: #cbd5e1;
            --sidebar-text-active: #ffffff;
            --sidebar-accent: #60a5fa;
            --content-bg: #f6f8fc;
            --topbar-height: 64px;
            --sipa-navy: #162660;
            --sipa-navy-hover: #0f1b47;
            --sipa-navy-soft: #eef2ff;
            --sipa-border: #e2e8f0;
            --bs-primary: #162660;
            --bs-primary-rgb: 22, 38, 96;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--content-bg);
            color: #0f172a;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .text-slate-800 { color: #0f172a !important; }

        /* --- BRAND NAVY BUTTONS & CONTROLS --- */
        .btn-primary, .btn-navy {
            background-color: var(--sipa-navy) !important;
            border-color: var(--sipa-navy) !important;
            color: #ffffff !important;
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-navy:hover, .btn-navy:focus {
            background-color: var(--sipa-navy-hover) !important;
            border-color: var(--sipa-navy-hover) !important;
            color: #ffffff !important;
            box-shadow: 0 6px 16px rgba(22, 38, 96, 0.22);
        }
        .btn-outline-primary {
            color: var(--sipa-navy) !important;
            border-color: #cbd5e1 !important;
            background-color: #ffffff;
            font-weight: 600;
        }
        .btn-outline-primary:hover {
            background-color: var(--sipa-navy) !important;
            border-color: var(--sipa-navy) !important;
            color: #ffffff !important;
        }
        .btn-detail-outline {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.8rem;
            border-radius: 10px;
            padding: 0.38rem 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.16s ease;
        }
        .btn-detail-outline:hover {
            background: var(--sipa-navy);
            border-color: var(--sipa-navy);
            color: #ffffff;
        }

        /* --- SIDEBAR STYLING --- */
        .sipa-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #10193b 0%, #162660 100%);
            color: var(--sidebar-text);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--sidebar-border);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.14);
        }

        .sidebar-brand {
            height: var(--topbar-height);
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--sidebar-border);
            text-decoration: none;
            color: #ffffff;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
        }

        .sidebar-brand-logo {
            height: 42px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
            transition: transform 0.2s ease;
        }

        .sidebar-brand:hover .sidebar-brand-logo {
            transform: scale(1.05);
        }

        .sidebar-brand-text {
            line-height: 1.2;
            margin-left: 0.75rem;
            transition: opacity 0.2s ease;
        }

        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.75rem;
        }

        /* Custom Scrollbar for Sidebar */
        .sidebar-nav-container::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        .sidebar-section-title {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #64748b;
            padding: 0.75rem 0.75rem 0.35rem 0.75rem;
            margin-top: 0.5rem;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.85rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar-nav-link i.nav-icon {
            font-size: 1.15rem;
            width: 1.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            color: #64748b;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .sidebar-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(3px);
        }

        .sidebar-nav-link:hover i.nav-icon {
            color: var(--sidebar-accent);
            transform: scale(1.1);
        }

        .sidebar-nav-link.active {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.18) 0%, rgba(56, 189, 248, 0.05) 100%);
            border-left: 3px solid var(--sidebar-accent);
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(56, 189, 248, 0.08);
        }

        .sidebar-nav-link.active i.nav-icon {
            color: var(--sidebar-accent);
        }

        .sidebar-nav-badge {
            margin-left: auto;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            font-weight: 600;
        }

        /* Sidebar Footer / User Profile */
        .sidebar-footer {
            padding: 0.85rem 1rem;
            border-top: 1px solid var(--sidebar-border);
            background: rgba(15, 23, 42, 0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0284c7 0%, #1e3a8a 100%);
            color: #ffffff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-user-info {
            margin-left: 0.65rem;
            line-height: 1.2;
            overflow: hidden;
            flex: 1;
        }

        /* --- MAIN WRAPPER & TOPBAR --- */
        .sipa-main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sipa-topbar {
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .topbar-toggle-btn {
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #475569;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .topbar-toggle-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .sipa-content-area {
            flex: 1;
            padding: 1.5rem 1.5rem 2rem 1.5rem;
        }

        .sipa-footer {
            padding: 1rem 1.5rem;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            font-size: 0.78rem;
            color: #64748b;
        }

        /* Mobile Sidebar Overlay */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1035;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 991.98px) {
            .sipa-sidebar {
                transform: translateX(-100%);
            }
            .sipa-sidebar.show {
                transform: translateX(0);
            }
            .sidebar-backdrop.show {
                display: block;
                opacity: 1;
            }
            .sipa-main-wrapper {
                margin-left: 0;
            }
            .sipa-content-area {
                padding: 1rem;
            }
        }

        /* Desktop Collapsed Sidebar Mode */
        @media (min-width: 992px) {
            body.sidebar-collapsed .sipa-sidebar {
                width: var(--sidebar-collapsed-width);
            }
            body.sidebar-collapsed .sipa-main-wrapper {
                margin-left: var(--sidebar-collapsed-width);
            }
            body.sidebar-collapsed .sidebar-brand-text,
            body.sidebar-collapsed .sidebar-section-title,
            body.sidebar-collapsed .nav-text,
            body.sidebar-collapsed .sidebar-nav-badge,
            body.sidebar-collapsed .sidebar-user-info {
                display: none !important;
            }
            body.sidebar-collapsed .sidebar-nav-link {
                justify-content: center;
                padding: 0.75rem 0;
            }
            body.sidebar-collapsed .sidebar-nav-link i.nav-icon {
                margin-right: 0;
                font-size: 1.3rem;
            }
            body.sidebar-collapsed .sidebar-footer {
                justify-content: center;
                padding: 0.75rem 0;
            }
        }

        /* Interactive Card & Action Styling */
        .card {
            border: 1px solid var(--sipa-border);
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .card:hover {
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        }

        /* Clean Hero & Reference-Inspired Components */
        .sipa-page-hero {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5ff 58%, #e6eeff 100%);
            border: 1px solid #dbe4f3;
            border-radius: 18px;
            padding: 1.4rem 1.6rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(22, 38, 96, 0.05);
        }
        .sipa-page-hero::after {
            content: '';
            position: absolute;
            right: -36px;
            top: -42px;
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(22, 38, 96, 0.08) 0%, rgba(22, 38, 96, 0) 72%);
            pointer-events: none;
        }
        .sipa-kicker {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: #3b55a4;
            margin-bottom: 0.25rem;
        }
        .sipa-filter-card {
            background: #ffffff;
            border: 1px solid var(--sipa-border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            padding: 1rem 1.15rem;
        }
        .sipa-cta-banner {
            background: linear-gradient(135deg, #10193b 0%, #162660 60%, #1e3a8a 100%);
            color: #ffffff;
            border-radius: 18px;
            padding: 1.4rem 1.75rem;
            box-shadow: 0 12px 28px rgba(22, 38, 96, 0.18);
        }
        .pastel-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .pastel-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .pastel-lavender { background: #f3e8ff; color: #7e22ce; }
        .pastel-navy     { background: #e0e7ff; color: #162660; }
        .pastel-mint     { background: #dcfce7; color: #15803d; }
        .pastel-amber    { background: #fef3c7; color: #b45309; }
        .pastel-rose     { background: #ffe4e6; color: #be123c; }
        .pastel-sky      { background: #e0f2fe; color: #0369a1; }

        /* Clean Table Polish */
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 1px solid #e2e8f0;
            padding-top: 0.78rem;
            padding-bottom: 0.78rem;
        }
        .table tbody td {
            padding-top: 0.78rem;
            padding-bottom: 0.78rem;
            border-color: #f1f5f9;
        }
        .form-control, .form-select {
            border-color: #cbd5e1;
            border-radius: 10px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #162660;
            box-shadow: 0 0 0 0.2rem rgba(22, 38, 96, 0.12);
        }
        .input-group .form-control { border-radius: 0 10px 10px 0; }
        .input-group .input-group-text { border-radius: 10px 0 0 10px; border-color: #cbd5e1; }

        /* Enforce LTR and Left Alignment on Form Controls */
        select.form-select, select.form-select option {
            direction: ltr !important;
            text-align: left !important;
        }

        /* Unified Table Action Buttons (Spaced, Non-Crowded, Modern Pill/Tile) */
        .action-btn-group {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.45rem;
            flex-wrap: nowrap;
        }
        .action-btn-group form {
            margin: 0;
            display: inline-flex;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            font-size: 0.85rem;
            border: 1px solid transparent;
            transition: all 0.16s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-action-pill {
            height: 32px;
            padding: 0 0.72rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.32rem;
            border-radius: 9px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid transparent;
            white-space: nowrap;
            transition: all 0.16s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-action:hover, .btn-action-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.12);
        }
        .btn-action-info { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
        .btn-action-info:hover { background: #0284c7; color: #ffffff; border-color: #0284c7; }
        .btn-action-primary { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .btn-action-primary:hover { background: #1d4ed8; color: #ffffff; border-color: #1d4ed8; }
        .btn-action-warning { background: #fef3c7; color: #b45309; border-color: #fde68a; }
        .btn-action-warning:hover { background: #d97706; color: #ffffff; border-color: #d97706; }
        .btn-action-secondary { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
        .btn-action-secondary:hover { background: #475569; color: #ffffff; border-color: #475569; }
        .btn-action-danger { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
        .btn-action-danger:hover { background: #dc2626; color: #ffffff; border-color: #dc2626; }
        .btn-action-success { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
        .btn-action-success:hover { background: #16a34a; color: #ffffff; border-color: #16a34a; }

        /* --- DARK THEME STYLING (MODE GELAP ELEGAN) --- */
        [data-bs-theme="dark"] {
            --content-bg: #0b1120;
            --sipa-navy: #3b82f6;
            --sipa-navy-hover: #60a5fa;
            --sipa-navy-soft: #1e293b;
            --sipa-border: #1e2e4a;
            --bs-body-bg: #0b1120;
            --bs-body-color: #e2e8f0;
        }

        [data-bs-theme="dark"] body {
            background-color: #0b1120 !important;
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .text-slate-800,
        [data-bs-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .text-muted,
        [data-bs-theme="dark"] .text-secondary {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .bg-white {
            background-color: #111a2e !important;
            color: #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: #162036 !important;
            color: #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .border,
        [data-bs-theme="dark"] .border-top,
        [data-bs-theme="dark"] .border-bottom,
        [data-bs-theme="dark"] .border-start,
        [data-bs-theme="dark"] .border-end {
            border-color: #1e2e4a !important;
        }

        [data-bs-theme="dark"] .sipa-topbar {
            background: #0d1527;
            border-bottom-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .topbar-toggle-btn {
            border-color: #243453;
            color: #cbd5e1;
        }
        [data-bs-theme="dark"] .topbar-toggle-btn:hover {
            background: #162036;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .card {
            background-color: #111a2e;
            border-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: #111a2e;
            border-bottom-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .card-footer {
            background-color: #111a2e;
            border-top-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .sipa-page-hero {
            background: linear-gradient(135deg, #0e172e 0%, #16254a 55%, #1b3168 100%);
            border-color: #22355c;
            color: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        [data-bs-theme="dark"] .sipa-page-hero .text-slate-800 {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .sipa-page-hero .text-muted {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .sipa-kicker {
            color: #93c5fd;
        }

        [data-bs-theme="dark"] .sipa-filter-card {
            background: #111a2e;
            border-color: #1e2e4a;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .service-tile {
            background: #111a2e;
            border-color: #1e2e4a;
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .service-tile:hover {
            border-color: #3b82f6;
            background: #16233f;
        }

        [data-bs-theme="dark"] .loan-item-row {
            border-bottom-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .loan-item-row:hover {
            background-color: #16233f;
        }

        [data-bs-theme="dark"] .btn-detail-outline {
            background: #162036;
            border-color: #243453;
            color: #f1f5f9;
        }

        [data-bs-theme="dark"] .btn-detail-outline:hover {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .chip-quick {
            background: #162036;
            border-color: #243453;
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] .chip-quick:hover {
            background: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }

        [data-bs-theme="dark"] .sipa-footer {
            background: #0d1527;
            border-top-color: #1e2e4a;
            color: #94a3b8;
        }

        [data-bs-theme="dark"] .table {
            color: #e2e8f0;
            border-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .table thead th {
            background-color: #16233f;
            color: #cbd5e1;
            border-bottom-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .table tbody td {
            border-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .table-hover tbody tr:hover td {
            background-color: #16233f !important;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #141f36;
            border-color: #243453;
            color: #f1f5f9;
        }

        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #141f36;
            border-color: #3b82f6;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .input-group-text {
            background-color: #16233f;
            border-color: #243453;
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: #111a2e;
            border-color: #1e2e4a;
            color: #f1f5f9;
        }

        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            border-color: #1e2e4a;
        }

        [data-bs-theme="dark"] .modal-header.bg-light,
        [data-bs-theme="dark"] .modal-footer.bg-light {
            background-color: #16233f !important;
        }

        [data-bs-theme="dark"] .kpi-card,
        [data-bs-theme="dark"] .peminjam-stat-card {
            background-color: #111a2e;
            border-color: #1e2e4a;
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .peminjam-stat-card.is-active {
            background-color: #16254a;
            border-color: #3b82f6;
        }

        [data-bs-theme="dark"] .chip-filter {
            background-color: #162036;
            border-color: #243453;
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] .chip-filter:hover {
            background-color: #1e2e4a;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .chip-filter.active {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .list-group-item {
            background-color: #111a2e;
            border-color: #1e2e4a;
            color: #e2e8f0;
        }

        [data-bs-theme="dark"] .btn-outline-secondary {
            border-color: #243453;
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] .btn-outline-secondary:hover {
            background-color: #1e2e4a;
            border-color: #1e2e4a;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .badge.bg-light {
            background-color: #16233f !important;
            color: #cbd5e1 !important;
            border-color: #243453 !important;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Mobile Overlay Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR UTAMA -->
    <aside class="sipa-sidebar" id="sipaSidebar">
        <!-- Logo & Header Instansi -->
        <a href="{{ route('dasbor') }}" class="sidebar-brand">
            <img src="{{ asset('images/logo-cirebon.png') }}" alt="Lambang Kabupaten Cirebon" class="sidebar-brand-logo">
            <div class="sidebar-brand-text">
                <div class="fw-bold fs-5 tracking-tight text-white d-flex align-items-center">
                    SIPA
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2" style="font-size: 0.6rem; letter-spacing: 0.05em;">DISARPUS</span>
                </div>
                <div class="text-white-50 small" style="font-size: 0.68rem;">Kabupaten Cirebon</div>
            </div>
        </a>

        <!-- Container Navigasi Menu -->
        <div class="sidebar-nav-container">
            <!-- 1. Menu Utama -->
            <div class="sidebar-section-title">Menu Utama</div>
            
            <a href="{{ route('dasbor') }}" class="sidebar-nav-link {{ request()->routeIs('dasbor') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill nav-icon"></i>
                <span class="nav-text">Dasbor</span>
            </a>

            <a href="{{ route('arsip.index') }}" class="sidebar-nav-link {{ request()->routeIs('arsip.*') ? 'active' : '' }}">
                <i class="bi bi-folder2-open nav-icon"></i>
                <span class="nav-text">Data Arsip</span>
            </a>

            <a href="{{ route('peminjam.index') }}" class="sidebar-nav-link {{ request()->routeIs('peminjam.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill nav-icon"></i>
                <span class="nav-text">Data Peminjam</span>
            </a>

            <!-- 2. Layanan Sirkulasi -->
            <div class="sidebar-section-title">Layanan Sirkulasi</div>

            <a href="{{ route('peminjaman.create') }}" class="sidebar-nav-link {{ request()->routeIs('peminjaman.create') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-plus-fill nav-icon"></i>
                <span class="nav-text">Peminjaman Baru</span>
            </a>

            <a href="{{ route('peminjaman.index', 'peminjaman.show') }}" class="sidebar-nav-link {{ request()->routeIs('peminjaman.index', 'peminjaman.show') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark-fill nav-icon"></i>
                <span class="nav-text">Daftar Peminjaman</span>
            </a>

            <a href="{{ route('pengembalian.index') }}" class="sidebar-nav-link {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left nav-icon"></i>
                <span class="nav-text">Pengembalian</span>
            </a>

            <a href="{{ route('pemindai.index') }}" class="sidebar-nav-link {{ request()->routeIs('pemindai.*') ? 'active' : '' }}">
                <i class="bi bi-bounding-box-circles nav-icon"></i>
                <span class="nav-text">Pemindai Dokumen</span>
            </a>

            <a href="{{ route('laporan.index') }}" class="sidebar-nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph-fill nav-icon"></i>
                <span class="nav-text">Laporan Tahunan</span>
            </a>

            <!-- Panduan & Bantuan -->
            <div class="sidebar-section-title">Panduan & Bantuan</div>

            <a href="{{ route('panduan') }}" class="sidebar-nav-link {{ request()->routeIs('panduan*') ? 'active' : '' }}">
                <i class="bi bi-compass-fill nav-icon text-info"></i>
                <span class="nav-text">Panduan & Alur Kerja</span>
            </a>

            <!-- 3. Master Data (SuperAdmin) -->
            @auth
                @if(Auth::user()->isSuperAdmin())
                    @php
                        $pendingResetCount = \App\Models\PermintaanResetPassword::where('status', 'Menunggu')->count();
                    @endphp
                    <div class="sidebar-section-title">Master Data</div>

                    <a href="{{ route('pengguna.index') }}" class="sidebar-nav-link {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge-fill nav-icon"></i>
                        <span class="nav-text d-flex align-items-center justify-content-between w-100">
                            <span>Pengguna & Petugas</span>
                            @if($pendingResetCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill ms-2">{{ $pendingResetCount }}</span>
                            @endif
                        </span>
                    </a>
                @endif

                <!-- 4. Sistem & Pengawasan -->
                @if(Auth::user()->isSuperAdmin())
                    <div class="sidebar-section-title">Sistem & Audit</div>

                    <a href="{{ route('audit-log.index') }}" class="sidebar-nav-link {{ request()->routeIs('audit-log.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check nav-icon"></i>
                        <span class="nav-text">Log Audit</span>
                    </a>

                    <a href="{{ route('pengaturan.index') }}" class="sidebar-nav-link {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
                        <i class="bi bi-sliders nav-icon"></i>
                        <span class="nav-text">Pengaturan & Kop</span>
                    </a>
                @endif
            @endauth
        </div>

        <!-- Profil Pengguna & Keluar di Bawah Sidebar -->
        @auth
            <div class="sidebar-footer">
                <div class="d-flex align-items-center overflow-hidden">
                    <div class="sidebar-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="sidebar-user-info">
                        <div class="text-white fw-semibold small text-truncate" style="max-width: 140px;">{{ Auth::user()->name }}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">
                            <span class="badge bg-secondary-subtle text-secondary py-0 px-1">{{ Auth::user()->peranLabel() }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-link text-danger p-1 text-decoration-none" title="Keluar dari Sistem">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <!-- WRAPPER KONTEN UTAMA -->
    <div class="sipa-main-wrapper">
        <!-- Topbar Sederhana & Fungsional -->
        <header class="sipa-topbar">
            <div class="d-flex align-items-center">
                <!-- Tombol Buka/Tutup Sidebar -->
                <button type="button" class="topbar-toggle-btn me-3" onclick="toggleSidebar()" title="Buka/Tutup Sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                
                <!-- Indikator Halaman Aktif -->
                <div class="d-flex align-items-center">
                    <span class="fw-bold text-slate-800 fs-6">@yield('title', 'Dasbor')</span>
                    <span class="text-muted mx-2 d-none d-sm-inline">&bull;</span>
                    <small class="text-muted d-none d-sm-inline">
                        <i class="bi bi-building me-1 text-primary"></i> {{ Auth::user()?->unitPengolah?->nama ?? 'Dinas Kearsipan dan Perpustakaan' }}
                    </small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Tombol Mode Gelap / Terang -->
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center shadow-xs" id="themeToggleBtn" onclick="toggleTheme()" title="Ganti Mode Gelap / Terang" aria-label="Ganti Tema">
                    <i class="bi bi-moon-stars-fill text-primary" id="themeToggleIcon"></i>
                    <span class="ms-1 small fw-semibold d-none d-sm-inline" id="themeToggleText">Gelap</span>
                </button>

                <!-- Tombol Panduan Cepat Topbar -->
                <a href="{{ route('panduan') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 d-none d-md-inline-flex align-items-center shadow-xs text-decoration-none" title="Buku Panduan & Alur Penggunaan">
                    <i class="bi bi-compass text-info me-1"></i>
                    <span class="small fw-semibold">Panduan</span>
                </a>

                <!-- Tanggal Hari Ini (Indonesian Format) -->
                <div class="badge bg-light text-secondary border px-3 py-2 rounded-pill d-none d-lg-flex align-items-center shadow-xs">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                    <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>

                <!-- Tombol Cepat Peminjaman Baru -->
                <a href="{{ route('peminjaman.create') }}" class="btn btn-navy btn-sm rounded-3 px-3 shadow-sm d-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-sm-inline">Pinjam Arsip</span>
                </a>
            </div>
        </header>

        <!-- Area Konten Halaman -->
        <main class="sipa-content-area">
            <!-- Alert Notifikasi Sukses -->
            @if (session('sukses'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3 border-0 bg-success-subtle text-success-emphasis" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                    <strong>Berhasil!</strong> {{ session('sukses') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Alert Notifikasi Gagal -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3 border-0 bg-danger-subtle text-danger-emphasis" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
                    <strong>Peringatan!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer Bersih Bergaya Instansi Modern -->
        <footer class="mt-auto">
            <div class="sipa-footer d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/logo-cirebon.png') }}" alt="Logo" style="height: 24px; width: auto;">
                    <span><strong>SIPA DISARPUS</strong> &bull; Dinas Kearsipan dan Perpustakaan Kabupaten Cirebon</span>
                </div>
                <div class="text-muted mt-1 mt-sm-0 small">
                    Senin - Jumat, 08.00 - 16.00 WIB
                </div>
            </div>
            <div class="px-4 py-2 d-flex flex-column flex-sm-row justify-content-between align-items-center text-white-50" style="background: var(--sipa-navy); font-size: 0.74rem;">
                <span>&copy; {{ date('Y') }} Dinas Kearsipan dan Perpustakaan Kab. Cirebon. Seluruh hak cipta dilindungi.</span>
                <span>Sistem Informasi Peminjaman Arsip &bull; Versi 2.0</span>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Interactive Script -->
    <script>
        function toggleSidebar() {
            const isMobile = window.innerWidth < 992;
            const sidebar = document.getElementById('sipaSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (isMobile) {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                localStorage.setItem('sipa_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            }
        }

        // Restore sidebar state from localStorage on desktop
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth >= 992) {
                const savedState = localStorage.getItem('sipa_sidebar_collapsed');
                if (savedState === 'true') {
                    document.body.classList.add('sidebar-collapsed');
                }
            }
        });

        // Theme Controller (Mode Gelap / Terang)
        function updateThemeUI(theme) {
            const btn = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            if (!icon) return;
            if (theme === 'dark') {
                icon.className = 'bi bi-sun-fill text-warning';
                if (text) text.textContent = 'Terang';
                if (btn) btn.setAttribute('title', 'Beralih ke Mode Terang');
            } else {
                icon.className = 'bi bi-moon-stars-fill text-primary';
                if (text) text.textContent = 'Gelap';
                if (btn) btn.setAttribute('title', 'Beralih ke Mode Gelap');
            }
        }

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            document.documentElement.setAttribute('data-theme', next);
            try {
                localStorage.setItem('sipa_theme', next);
            } catch (e) {}
            updateThemeUI(next);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const activeTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            updateThemeUI(activeTheme);
        });

        // Register PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.log('SW registration failed: ', err);
                });
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
