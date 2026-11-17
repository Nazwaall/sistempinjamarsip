<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Password - SIPA Dinas Kearsipan & Perpustakaan Kab. Cirebon</title>
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
            max-width: 440px;
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
        [data-bs-theme="dark"] .border-top {
            border-top-color: #1e293b !important;
        }
    </style>
</head>
<body>

<div class="auth-card p-4 p-sm-5">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-cirebon.png') }}" alt="Lambang Kabupaten Cirebon" style="height: 60px; width: auto;" class="mb-2">
        <h4 class="fw-bold text-slate-800 mb-1">Buat Password Baru</h4>
        <p class="text-muted small mb-0">Masukkan password baru minimal 6 karakter untuk akun SIPA Anda</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
            <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-secondary">Email Akun</label>
            <input type="email" name="email" id="email" class="form-control touch-input" placeholder="nama@gmail.com" value="{{ old('email', $email) }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-secondary">Password Baru</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control touch-input" placeholder="Minimal 6 karakter" required autofocus>
                <button type="button" class="btn btn-light border text-muted px-3" onclick="togglePassField('password', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Konfirmasi Password Baru</label>
            <div class="input-group">
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control touch-input" placeholder="Ulangi password baru" required>
                <button type="button" class="btn btn-light border text-muted px-3" onclick="togglePassField('password_confirmation', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 touch-input fw-semibold shadow-sm" style="background-color: #1e3a8a; border-color: #1e3a8a;">
            <i class="bi bi-check-lg me-1"></i> Simpan Password Baru
        </button>
    </form>

    <div class="text-center mt-4 pt-3 border-top">
        <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Masuk
        </a>
    </div>
</div>

<script>
    function togglePassField(inputId, btn) {
        const inp = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>
</body>
</html>
