<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SIPA Dinas Kearsipan & Perpustakaan Kab. Cirebon</title>
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('sipa_theme');
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const activeTheme = savedTheme ? savedTheme : (systemPrefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', activeTheme);
                document.documentElement.setAttribute('data-theme', activeTheme);
            } catch (e) {}
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: radial-gradient(circle at top right, #dbeafe 0%, #f6f8fc 45%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            padding: 16px;
        }
        .auth-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 20px 40px -12px rgba(22, 38, 96, 0.14);
            max-width: 470px;
            width: 100%;
            overflow: hidden;
        }
        .touch-input {
            font-size: 15px;
            min-height: 46px;
        }

        /* Dark Mode */
        [data-bs-theme="dark"] body {
            background: radial-gradient(circle at top right, #0f172a 0%, #0b1120 45%, #020617 100%) !important;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .auth-card {
            background: #111a2e !important;
            border-color: #1e293b !important;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.6) !important;
        }
        [data-bs-theme="dark"] .text-slate-800 {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .form-control {
            background-color: #0d1526 !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .input-group-text {
            background-color: #16233f !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .nav-tabs {
            border-bottom-color: #1e293b !important;
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link {
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link.active {
            background-color: #16233f !important;
            color: #60a5fa !important;
            border-color: #1e293b #1e293b transparent !important;
        }
        [data-bs-theme="dark"] .border-top {
            border-top-color: #1e293b !important;
        }
    </style>
</head>
<body>

@php
    $tabAdminAktif = session('active_tab') === 'admin' || $errors->has('identitas_admin');
@endphp

<div class="auth-card p-4 p-sm-5">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-cirebon.png') }}" alt="Lambang Kabupaten Cirebon" style="height: 60px; width: auto;" class="mb-2">
        <h4 class="fw-bold text-slate-800 mb-1">Pemulihan Password</h4>
        <p class="text-muted small mb-0">Pilih cara reset melalui tautan email atau ajukan ke Super Admin</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
            <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    @if (session('sukses'))
        <div class="alert alert-success py-2 px-3 small rounded-3 mb-3">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('sukses') }}
        </div>
    @endif

    <!-- Tabs Metode Pemulihan -->
    <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 small mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ !$tabAdminAktif ? 'active' : '' }} py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tabEmailReset" type="button" role="tab">
                <i class="bi bi-envelope-check me-1"></i> Via Email
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $tabAdminAktif ? 'active' : '' }} py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tabAdminReset" type="button" role="tab">
                <i class="bi bi-shield-lock me-1"></i> Ajukan ke Super Admin
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab 1: Token Email Laravel -->
        <div class="tab-pane fade {{ !$tabAdminAktif ? 'show active' : '' }}" id="tabEmailReset" role="tabpanel">
            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="identitas" class="form-label small fw-semibold text-secondary">Username atau Email Terdaftar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="text" name="identitas" id="identitas" class="form-control border-start-0 touch-input" placeholder="Contoh: operator.arsip atau nama@gmail.com" value="{{ old('identitas') }}" required>
                    </div>
                </div>
                <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Sistem akan mengirimkan tautan reset bertoken ke email akun Anda (berlaku 60 menit).
                </div>
                <button type="submit" class="btn btn-primary w-100 touch-input fw-semibold shadow-sm" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    <i class="bi bi-send me-1"></i> Kirim Tautan Reset Password
                </button>
            </form>
        </div>

        <!-- Tab 2: Permohonan Reset ke Super Admin (Offline Laragon Ready) -->
        <div class="tab-pane fade {{ $tabAdminAktif ? 'show active' : '' }}" id="tabAdminReset" role="tabpanel">
            <form action="{{ route('password.admin-request') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="identitas_admin" class="form-label small fw-semibold text-secondary">Username atau NIP <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                        <input type="text" name="identitas_admin" id="identitas_admin" class="form-control border-start-0 touch-input" placeholder="Contoh: operator.arsip atau NIP" value="{{ old('identitas_admin') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="catatan_pemohon" class="form-label small fw-semibold text-secondary">Keterangan Tambahan (Opsional)</label>
                    <input type="text" name="catatan_pemohon" id="catatan_pemohon" class="form-control touch-input" placeholder="Contoh: Lupa password setelah ganti perangkat" value="{{ old('catatan_pemohon') }}">
                </div>
                <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3">
                    <i class="bi bi-person-gear me-1"></i>
                    Permintaan akan masuk langsung ke antrean menu <strong>Pengguna &amp; Petugas</strong> milik Super Admin agar password Anda segera disetel ulang.
                </div>
                <button type="submit" class="btn btn-success w-100 touch-input fw-semibold shadow-sm">
                    <i class="bi bi-check2-square me-1"></i> Kirim Permohonan ke Super Admin
                </button>
            </form>
        </div>
    </div>

    <div class="text-center mt-4 pt-3 border-top">
        <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Masuk
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
