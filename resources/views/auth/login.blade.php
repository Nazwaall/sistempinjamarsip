<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIPA Dinas Kearsipan & Perpustakaan Kab. Cirebon</title>
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
            transition: background 0.3s ease, color 0.3s ease;
        }
        .login-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 20px 40px -12px rgba(22, 38, 96, 0.14);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .touch-input {
            font-size: 16px; /* Mencegah auto-zoom di iPhone */
            min-height: 48px;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .spin-animation { animation: spin 0.6s linear infinite; display: inline-block; }

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] body {
            background: radial-gradient(circle at top right, #0f172a 0%, #0b1120 45%, #020617 100%) !important;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .login-card {
            background: #111a2e !important;
            border-color: #1e293b !important;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.6) !important;
        }
        [data-bs-theme="dark"] .text-slate-800 {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .bg-light {
            background-color: #16233f !important;
            border-color: #1e293b !important;
        }
        [data-bs-theme="dark"] .form-control {
            background-color: #0d1526 !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .form-control:focus {
            background-color: #0d1526 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .input-group-text {
            background-color: #16233f !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .btn-light {
            background-color: #16233f !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        [data-bs-theme="dark"] .badge.bg-secondary-subtle {
            background-color: rgba(148, 163, 184, 0.15) !important;
            color: #cbd5e1 !important;
            border-color: rgba(148, 163, 184, 0.25) !important;
        }
    </style>
</head>
<body>

<!-- Floating Theme Toggle -->
<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill position-fixed top-0 end-0 m-3 d-flex align-items-center gap-1 shadow-sm px-3 py-2" id="loginThemeToggle" onclick="toggleLoginTheme()" title="Ganti Mode Gelap / Terang">
    <i class="bi bi-moon-stars-fill" id="loginThemeIcon"></i>
    <span class="small fw-semibold" id="loginThemeText">Mode Gelap</span>
</button>

<div class="login-card p-4 p-sm-5">
    <!-- Header -->
    <div class="text-center mb-4">
        <div class="mb-3">
            <img src="{{ asset('images/logo-cirebon.png') }}" alt="Lambang Kabupaten Cirebon" style="height: 72px; width: auto;" class="drop-shadow">
        </div>
        <h3 class="fw-bold text-slate-800 mb-1">SIPA</h3>
        <p class="text-muted small mb-0">Sistem Informasi Peminjaman Arsip</p>
        <div class="badge bg-secondary-subtle text-secondary border mt-1">Dinas Kearsipan dan Perpustakaan Kab. Cirebon</div>
    </div>

    <!-- Alert Notifikasi -->
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

    <!-- Form Login -->
    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label small fw-semibold text-secondary">Username / NIP</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                <input type="text" name="username" id="username" class="form-control border-start-0 touch-input" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0 touch-input" placeholder="Masukkan password" required>
                <button type="button" class="btn btn-light border border-start-0 text-muted px-3" id="btnTogglePassword" onclick="togglePasswordVisibility('password', 'iconEyePassword')" title="Tampilkan / Sembunyikan Password">
                    <i class="bi bi-eye" id="iconEyePassword"></i>
                </button>
            </div>
        </div>

        <!-- Verifikasi Human (Math Challenge Anti-Bot) -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="human_verification" class="form-label small fw-semibold text-secondary mb-0">
                    <i class="bi bi-shield-check text-primary me-1"></i> Verifikasi Keamanan:
                </label>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" onclick="refreshCaptcha()" title="Ganti Pertanyaan">
                    <i class="bi bi-arrow-clockwise me-1" id="iconRefreshCaptcha"></i><span style="font-size: 0.75rem;">Ganti Soal</span>
                </button>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 fw-bold text-slate-800 px-3" id="captchaQuestionBadge" style="letter-spacing: 1px; font-size: 0.95rem; min-width: 110px; justify-content: center;">
                    {{ session('human_question', '5 + 3 = ?') }}
                </span>
                <input type="number" name="human_verification" id="human_verification" class="form-control border-start-0 touch-input" placeholder="Hasil hitungan..." required autocomplete="off">
            </div>
            <div class="form-text text-muted" style="font-size: 0.74rem;">
                Hitung hasil penjumlahan di atas untuk memverifikasi Anda bukan bot.
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small text-secondary" for="remember">Ingat Saya</label>
            </div>
            <a href="{{ route('password.request') }}" class="small text-decoration-none fw-semibold text-primary">
                Lupa Password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary w-100 touch-input fw-semibold shadow-sm mb-3 rounded-3" style="background-color: #162660; border-color: #162660;">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
        </button>
    </form>
</div>

<script>
    function updateLoginThemeUI(theme) {
        const icon = document.getElementById('loginThemeIcon');
        const text = document.getElementById('loginThemeText');
        if (!icon || !text) return;

        if (theme === 'dark') {
            icon.className = 'bi bi-sun-fill text-warning';
            text.textContent = 'Mode Terang';
        } else {
            icon.className = 'bi bi-moon-stars-fill text-primary';
            text.textContent = 'Mode Gelap';
        }
    }

    function toggleLoginTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', nextTheme);
        document.documentElement.setAttribute('data-theme', nextTheme);
        localStorage.setItem('sipa_theme', nextTheme);
        updateLoginThemeUI(nextTheme);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateLoginThemeUI(currentTheme);
    });

    function refreshCaptcha() {
        const icon = document.getElementById('iconRefreshCaptcha');
        const badge = document.getElementById('captchaQuestionBadge');
        const input = document.getElementById('human_verification');
        if (icon) icon.classList.add('spin-animation');

        fetch('{{ route("captcha.refresh") }}')
            .then(res => res.json())
            .then(data => {
                if (data.question && badge) {
                    badge.textContent = data.question;
                }
                if (input) {
                    input.value = '';
                    input.focus();
                }
            })
            .catch(() => {})
            .finally(() => {
                if (icon) icon.classList.remove('spin-animation');
            });
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>

</body>
</html>
