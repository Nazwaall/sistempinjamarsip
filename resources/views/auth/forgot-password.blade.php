<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SIPA Dinas Kearsipan & Perpustakaan Kab. Cirebon</title>
    <script>
        try { localStorage.removeItem('sipa_theme'); } catch (e) {}
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
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }
        .touch-input {
            font-size: 15px;
            min-height: 48px;
        }
    </style>
</head>
<body>

<div class="auth-card p-4 p-sm-5">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-cirebon.png') }}" alt="Lambang Kabupaten Cirebon" style="height: 64px; width: auto;" class="mb-3">
        <h4 class="fw-bold text-dark mb-1">Permohonan Reset Password</h4>
        <p class="text-muted small mb-0">Ajukan permohonan setel ulang kata sandi langsung ke Super Admin</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
            <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    @if (session('sukses'))
        <div class="alert alert-success py-2.5 px-3 small rounded-3 mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('sukses') }}</div>
            </div>
        </div>
    @endif

    <form action="{{ route('password.admin-request') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="identitas_admin" class="form-label small fw-semibold text-secondary">Username atau NIP Petugas <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                <input type="text" name="identitas_admin" id="identitas_admin" class="form-control border-start-0 touch-input" placeholder="Masukkan username atau NIP Anda" value="{{ old('identitas_admin') }}" required autofocus>
            </div>
            <div class="form-text text-muted" style="font-size: 0.75rem;">
                Masukkan username atau NIP akun Anda yang terdaftar pada sistem SIPA.
            </div>
        </div>

        <div class="mb-3">
            <label for="catatan_pemohon" class="form-label small fw-semibold text-secondary">Alasan / Keterangan (Opsional)</label>
            <textarea name="catatan_pemohon" id="catatan_pemohon" rows="2" class="form-control" placeholder="Contoh: Lupa password lama setelah pembaruan">{{ old('catatan_pemohon') }}</textarea>
        </div>

        <div class="alert alert-light border py-2.5 px-3 small rounded-3 mb-4 text-secondary">
            <div class="d-flex gap-2">
                <i class="bi bi-shield-lock-fill text-primary mt-0.5"></i>
                <div style="font-size: 0.8rem; line-height: 1.4;">
                    Permohonan ini akan otomatis masuk ke menu antrean <strong>Pengguna &amp; Petugas</strong> pada akun <strong>Super Admin</strong> untuk diverifikasi dan diterbitkan password baru.
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 touch-input fw-semibold shadow-sm mb-3 rounded-3" style="background-color: #162660; border-color: #162660;">
            <i class="bi bi-send-check me-1"></i> Kirim Permohonan ke Super Admin
        </button>
    </form>

    <div class="text-center mt-3 pt-3 border-top">
        <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold text-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali ke Halaman Masuk
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
