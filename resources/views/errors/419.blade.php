<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Kedaluwarsa | SIPA Disarpus</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-cirebon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f0f4fd 0%, #e2e8f8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1e293b;
        }
        .error-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(22, 38, 96, 0.1);
            padding: 2.5rem 2rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #eff6ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.25rem;
        }
        .btn-navy {
            background: #162660;
            color: #ffffff;
            border: none;
            padding: 0.65rem 1.4rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-navy:hover {
            background: #0e1b47;
            color: #ffffff;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-circle">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 fw-bold mb-2">STATUS 419</span>
        <h4 class="fw-bold mb-2 text-slate-800">Sesi Formulir Kedaluwarsa</h4>
        <p class="text-muted small mb-4" style="line-height: 1.6;">
            Halaman ini didiamkan terlalu lama atau server baru saja diperbarui. Demi keamanan sistem SIPA, token keamanan formulir telah disegarkan kembali.
        </p>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
            <button onclick="window.history.back()" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali &amp; Muat Ulang
            </button>
            <a href="{{ route('dasbor') }}" class="btn-navy">
                <i class="bi bi-house-door-fill me-1"></i> Ke Dasbor Utama
            </a>
        </div>
    </div>
</body>
</html>
