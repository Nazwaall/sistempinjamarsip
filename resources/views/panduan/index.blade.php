@extends('layouts.app')

@section('title', 'Panduan & Alur Penggunaan SIPA')

@push('styles')
<style>
    .stepper-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-bottom: 1.5rem;
    }
    .stepper-btn {
        background: #ffffff;
        border: 1px solid var(--sipa-border);
        border-radius: 12px;
        padding: 0.65rem 1rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.18s ease;
    }
    .stepper-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
    }
    .stepper-btn.active {
        background: #162660;
        border-color: #162660;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(22, 38, 96, 0.2);
    }
    .step-badge {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.74rem;
        font-weight: 700;
        background: rgba(22, 38, 96, 0.1);
        color: #162660;
    }
    .stepper-btn.active .step-badge {
        background: #ffffff;
        color: #162660;
    }

    .guide-card {
        border: 1px solid var(--sipa-border);
        border-radius: 18px;
        background: #ffffff;
        padding: 1.4rem;
        margin-bottom: 1.5rem;
        transition: box-shadow 0.2s ease;
    }
    .guide-card:hover {
        box-shadow: 0 8px 24px rgba(22, 38, 96, 0.06);
    }
    .guide-card .btn-step-action {
        white-space: nowrap !important;
        font-size: 0.82rem;
        padding: 0.45rem 1.15rem;
    }
    @media (max-width: 767.98px) {
        .guide-card .btn-step-action {
            width: 100% !important;
            display: block !important;
            text-align: center !important;
            margin-top: 0.5rem;
        }
    }

    .flow-step-number {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 800;
        flex-shrink: 0;
    }

    .tip-box {
        border-left: 4px solid #3b82f6;
        background: #eff6ff;
        border-radius: 0 12px 12px 0;
        padding: 0.85rem 1.1rem;
        font-size: 0.84rem;
    }

    .warning-box {
        border-left: 4px solid #f59e0b;
        background: #fffbeb;
        border-radius: 0 12px 12px 0;
        padding: 0.85rem 1.1rem;
        font-size: 0.84rem;
    }

    .role-card {
        border: 1px solid var(--sipa-border);
        border-radius: 16px;
        background: #ffffff;
        padding: 1.25rem;
        height: 100%;
        transition: transform 0.18s ease;
    }
    .role-card:hover {
        transform: translateY(-2px);
    }

    /* Print Styles */
    @media print {
        .sipa-sidebar, .sipa-topbar, .sipa-footer, .stepper-nav, .no-print {
            display: none !important;
        }
        .sipa-main-wrapper {
            margin-left: 0 !important;
        }
        .sipa-content-area {
            padding: 0 !important;
        }
        .guide-card {
            border: 1px solid #ccc !important;
            page-break-inside: avoid;
            box-shadow: none !important;
        }
    }

    [data-bs-theme="dark"] .stepper-btn {
        background: #141f36;
        border-color: #243453;
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .stepper-btn:hover {
        background: #1e2e4a;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .stepper-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .step-badge {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }
    [data-bs-theme="dark"] .guide-card,
    [data-bs-theme="dark"] .role-card {
        background: #111a2e;
        border-color: #1e2e4a;
        color: #e2e8f0;
    }
    [data-bs-theme="dark"] .tip-box {
        background: #162544;
        border-color: #3b82f6;
        color: #dbeafe;
    }
    [data-bs-theme="dark"] .warning-box {
        background: #2a2012;
        border-color: #f59e0b;
        color: #fef3c7;
    }
</style>
@endpush

@section('content')
<!-- Hero Header Panduan -->
<div class="sipa-page-hero mb-3">
    <div class="row align-items-center g-3">
        <div class="col-12 col-lg-8">
            <div class="sipa-kicker">BUKU PANDUAN RESMI PENGGUNAAN APLIKASI</div>
            <h2 class="fw-bold text-slate-800 mb-2">Panduan &amp; Tata Cara Penggunaan SIPA</h2>
            <p class="text-muted mb-3" style="max-width: 650px; font-size: 0.92rem;">
                Pelajari alur operasional Sistem Informasi Peminjaman Arsip (SIPA) Dinas Kearsipan dan Perpustakaan Kab. Cirebon, mulai dari penyiapan berkas arsip, penerbitan surat resmi, pemindaian cerdas, pengembalian berkas, hingga rekapitulasi tahunan.
            </p>
            <div class="d-flex flex-wrap align-items-center gap-2 no-print">
                <button type="button" class="btn btn-navy rounded-3 px-3 py-2 shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan Panduan PDF
                </button>
                <a href="{{ route('peminjaman.create') }}" class="btn btn-outline-primary rounded-3 px-3 py-2">
                    <i class="bi bi-file-earmark-plus me-1"></i> Mulai Buat Peminjaman
                </a>
                <a href="#faqSection" class="btn btn-outline-secondary rounded-3 px-3 py-2">
                    <i class="bi bi-question-circle me-1"></i> Tanya Jawab (FAQ)
                </a>
            </div>
        </div>

        <div class="col-12 col-lg-4 d-none d-lg-block text-end">
            <div class="bg-white border rounded-4 p-3 shadow-sm text-start" style="max-width: 310px; margin-left: auto;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="pastel-icon-box pastel-navy"><i class="bi bi-person-check-fill"></i></span>
                    <div>
                        <div class="small fw-bold text-slate-800">Masuk Sebagai:</div>
                        <div class="text-primary fw-bold small">{{ Auth::user()->name }}</div>
                    </div>
                </div>
                <div class="small text-muted mb-2">
                    Peran: <span class="badge bg-primary-subtle text-primary border">{{ Auth::user()->peranLabel() }}</span>
                </div>
                <div class="text-muted" style="font-size: 0.74rem;">
                    Bidang: <strong>{{ Auth::user()->unitPengolah?->nama ?? 'Seluruh Bidang (Dinas)' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigasi Pintasan Alur 6 Langkah -->
<div class="stepper-nav no-print">
    <a href="#langkah1" class="stepper-btn">
        <span class="step-badge">1</span>
        <span>Data &amp; Impor Arsip</span>
    </a>
    <a href="#langkah2" class="stepper-btn">
        <span class="step-badge">2</span>
        <span>Direktori Peminjam</span>
    </a>
    <a href="#langkah3" class="stepper-btn">
        <span class="step-badge">3</span>
        <span>Buat Surat Peminjaman</span>
    </a>
    <a href="#langkah4" class="stepper-btn">
        <span class="step-badge">4</span>
        <span>Pemindai OpenCV</span>
    </a>
    <a href="#langkah5" class="stepper-btn">
        <span class="step-badge">5</span>
        <span>Pengembalian &amp; Fisik</span>
    </a>
    <a href="#langkah6" class="stepper-btn">
        <span class="step-badge">6</span>
        <span>Laporan &amp; Ekspor</span>
    </a>
</div>

<!-- DIAGRAM ALUR PROSES BESAR (VISUAL OVERVIEW) -->
<div class="guide-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <div class="sipa-kicker mb-0">GAMBARAN ALUR PROSES</div>
            <h5 class="fw-bold mb-0 text-slate-800"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Peta Alur Kerja Sirkulasi Arsip (End-to-End)</h5>
        </div>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">6 Tahapan Utama</span>
    </div>

    <div class="row g-2 text-center">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column align-items-center justify-content-center">
                <span class="pastel-icon-circle pastel-navy mb-2"><i class="bi bi-folder2-open"></i></span>
                <span class="badge bg-primary rounded-pill mb-1" style="font-size: 0.65rem;">Langkah 1</span>
                <div class="fw-bold small text-slate-800">Pendataan Arsip</div>
                <div class="text-muted" style="font-size: 0.72rem;">Input / Impor Excel</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column align-items-center justify-content-center">
                <span class="pastel-icon-circle pastel-lavender mb-2"><i class="bi bi-people"></i></span>
                <span class="badge bg-secondary rounded-pill mb-1" style="font-size: 0.65rem;">Langkah 2</span>
                <div class="fw-bold small text-slate-800">Data Peminjam</div>
                <div class="text-muted" style="font-size: 0.72rem;">Catat Kategori &amp; Jaminan</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column align-items-center justify-content-center">
                <span class="pastel-icon-circle pastel-sky mb-2"><i class="bi bi-file-earmark-text"></i></span>
                <span class="badge bg-primary rounded-pill mb-1" style="font-size: 0.65rem;">Langkah 3</span>
                <div class="fw-bold small text-slate-800">Surat Pinjam &amp; TTD</div>
                <div class="text-muted" style="font-size: 0.72rem;">Cek Detail &amp; Terbit PDF</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column align-items-center justify-content-center">
                <span class="pastel-icon-circle pastel-rose mb-2"><i class="bi bi-bounding-box-circles"></i></span>
                <span class="badge bg-danger rounded-pill mb-1" style="font-size: 0.65rem;">Langkah 4</span>
                <div class="fw-bold small text-slate-800">Scan Bukti Cerdas</div>
                <div class="text-muted" style="font-size: 0.72rem;">OpenCV KTP/Surat</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column align-items-center justify-content-center">
                <span class="pastel-icon-circle pastel-amber mb-2"><i class="bi bi-arrow-return-left"></i></span>
                <span class="badge bg-warning text-dark rounded-pill mb-1" style="font-size: 0.65rem;">Langkah 5</span>
                <div class="fw-bold small text-slate-800">Pengembalian</div>
                <div class="text-muted" style="font-size: 0.72rem;">Cek Fisik / CNN &amp; Selesai</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column align-items-center justify-content-center">
                <span class="pastel-icon-circle pastel-mint mb-2"><i class="bi bi-file-earmark-bar-graph"></i></span>
                <span class="badge bg-success rounded-pill mb-1" style="font-size: 0.65rem;">Langkah 6</span>
                <div class="fw-bold small text-slate-800">Laporan &amp; Rekap</div>
                <div class="text-muted" style="font-size: 0.72rem;">Ekspor PDF &amp; Excel</div>
            </div>
        </div>
    </div>
</div>

<!-- RINCIAN LANGKAH 1 SAMPAI 6 -->
<div class="row g-4">
    <!-- LANGKAH 1 -->
    <div class="col-12" id="langkah1">
        <div class="guide-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0">
                    <span class="flow-step-number pastel-navy">1</span>
                    <div>
                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 mb-1">Tahap Penyiapan</span>
                        <h4 class="fw-bold mb-1 text-slate-800">Pendataan &amp; Impor Berkas Arsip</h4>
                        <p class="text-muted small mb-0">Memasukkan data arsip dinas agar siap dicari dan dipinjam oleh pemohon layanan.</p>
                    </div>
                </div>
                <a href="{{ route('arsip.index') }}" class="btn btn-outline-primary btn-sm rounded-pill btn-step-action no-print">
                    Buka Menu Arsip <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <h6 class="fw-bold text-slate-800 mb-2"><i class="bi bi-check2-circle text-primary me-1"></i> Prosedur Operasional:</h6>
                    <ol class="small text-muted vstack gap-2 ps-3 mb-3">
                        <li>Buka menu <strong>Data Arsip</strong> dari navigasi sidebar kiri.</li>
                        <li>Untuk menambah berkas satu per satu, klik tombol <strong>Tambah Berkas</strong>. Lengkapi field: <code>Kode Berkas / Internal</code>, <code>Uraian Berkas</code>, <code>Uraian Informasi</code>, <code>Unit Pengolah Asal</code>, <code>Kurun Waktu</code>, <code>Jumlah Lembar</code>, serta <code>Lokasi Depo &amp; Boks</code>.</li>
                        <li>Untuk memasukkan arsip secara massal, klik tombol <strong>Impor Excel</strong> dan unggah file <code>.xlsx</code> / <code>.csv</code> sesuai template Daftar Arsip dinas.</li>
                        <li>Arsip yang tersimpan dan berstatus <strong>Tersedia</strong> dapat langsung dipinjamkan.</li>
                    </ol>
                </div>
                <div class="col-12 col-md-5">
                    <div class="tip-box mb-2">
                        <strong><i class="bi bi-lightbulb-fill me-1 text-primary"></i> Tips Pengelolaan:</strong><br>
                        Gunakan kode internal yang konsisten (contoh: <code>ARS-2026-001</code>) agar pencarian berkas via live search dan scanner barcode/OCR berjalan presisi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LANGKAH 2 -->
    <div class="col-12" id="langkah2">
        <div class="guide-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0">
                    <span class="flow-step-number pastel-lavender">2</span>
                    <div>
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1 mb-1">Tahap Registrasi</span>
                        <h4 class="fw-bold mb-1 text-slate-800">Direktori &amp; Registrasi Data Peminjam</h4>
                        <p class="text-muted small mb-0">Mencatat identitas penanggung jawab peminjam sebelum surat dibuat.</p>
                    </div>
                </div>
                <a href="{{ route('peminjam.index') }}" class="btn btn-outline-primary btn-sm rounded-pill btn-step-action no-print">
                    Buka Direktori Peminjam <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <h6 class="fw-bold text-slate-800 mb-2"><i class="bi bi-check2-circle text-primary me-1"></i> Prosedur Operasional:</h6>
                    <ol class="small text-muted vstack gap-2 ps-3 mb-3">
                        <li>Buka menu <strong>Data Peminjam</strong>. Klik tombol <strong>Tambah Peminjam</strong>.</li>
                        <li>Pilih kategori peminjam:
                            <ul>
                                <li><strong>Internal Dinas</strong>: Pegawai / bidang di lingkungan Disarpus.</li>
                                <li><strong>SKPD / Dinas Lain</strong>: Instansi dinas, badan, atau RSUD di Kab. Cirebon.</li>
                                <li><strong>Kecamatan / Kelurahan</strong>: Perangkat kewilayahan kecamatan / desa.</li>
                                <li><strong>Eksternal</strong>: Peneliti, mahasiswa, media, atau masyarakat umum (wajib mengisi alamat lengkap).</li>
                            </ul>
                        </li>
                        <li>Isi nomor HP / WhatsApp yang aktif untuk keperluan notifikasi jatuh tempo.</li>
                        <li>Tentukan <strong>Jaminan</strong> (KTP, SIM, Kartu Pegawai, Surat Tugas, atau Lainnya).</li>
                    </ol>
                </div>
                <div class="col-12 col-md-5">
                    <div class="tip-box mb-2">
                        <strong><i class="bi bi-file-earmark-pdf me-1 text-danger"></i> Fitur Ekspor Direktori:</strong><br>
                        Anda dapat mengunduh daftar seluruh peminjam aktif ke dalam dokumen <strong>PDF Resmi</strong> atau melihat riwayat perorangan melalui tombol <em>Ekspor Riwayat PDF</em> di halaman detail peminjam.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LANGKAH 3 -->
    <div class="col-12" id="langkah3">
        <div class="guide-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0">
                    <span class="flow-step-number pastel-sky">3</span>
                    <div>
                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 mb-1">Tahap Sirkulasi Inti</span>
                        <h4 class="fw-bold mb-1 text-slate-800">Pembuatan Surat Peminjaman &amp; Tanda Tangan</h4>
                        <p class="text-muted small mb-0">Menerbitkan surat peminjaman resmi, memeriksa detail berkas, dan membubuhkan tanda tangan.</p>
                    </div>
                </div>
                <a href="{{ route('peminjaman.create') }}" class="btn btn-navy btn-sm rounded-pill btn-step-action no-print">
                    Buka Formulir Peminjaman <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <h6 class="fw-bold text-slate-800 mb-2"><i class="bi bi-check2-circle text-primary me-1"></i> Alur Pembuatan Surat Pinjam:</h6>
                    <ol class="small text-muted vstack gap-2 ps-3 mb-3">
                        <li>Buka menu <strong>Peminjaman Baru</strong>.</li>
                        <li><strong>Pilih Bidang Pembuat Surat</strong> (contoh: Bidang Pengelolaan Arsip, Sekretariat, atau Bidang P2A).</li>
                        <li><strong>Tentukan Bentuk Layanan</strong>:
                            <ul>
                                <li><strong>Fisik</strong>: Berkas dipinjam keluar, wajib ada jaminan &amp; jatuh tempo.</li>
                                <li><strong>Digital (PDF)</strong>: Penerbitan salinan berkas tanpa meminjam fisik.</li>
                                <li><strong>Layanan Informasi</strong>: Konsultasi / baca di tempat.</li>
                            </ul>
                        </li>
                        <li><strong>Pilih Peminjam</strong> dari direktori.</li>
                        <li><strong>Pilih Berkas Arsip (Fitur Detail Berkas)</strong>: Anda dapat menekan tombol <strong>"Lihat Detail"</strong> pada katalog atau pencarian arsip untuk memeriksa uraian isi berkas sebelum menambahkannya ke daftar pinjam.</li>
                        <li><strong>Pilih Petugas Layanan yang Bertugas</strong>: Masukkan nama asli dan NIP petugas (tidak menggunakan "Super Admin SIPA").</li>
                        <li><strong>Tanda Tangan Peminjam</strong>: Bubuhkan TTD digital pada canvas interaktif atau pilih opsi TTD manual.</li>
                        <li>Klik <strong>Simpan &amp; Terbitkan Surat</strong>: Surat otomatis berstatus aktif, nomor surat resmi tercetak, dan PDF ber-Kop Dinas siap dicetak/diunduh.</li>
                    </ol>
                </div>
                <div class="col-12 col-md-5">
                    <div class="warning-box mb-2">
                        <strong><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Ketentuan Petugas Layanan:</strong><br>
                        Tanda tangan pada surat peminjaman wajib mencantumkan nama orang yang melayani beserta NIP dan bidangnya secara jelas. Sistem secara otomatis menolak nama default "Super Admin SIPA".
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LANGKAH 4 -->
    <div class="col-12" id="langkah4">
        <div class="guide-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0">
                    <span class="flow-step-number pastel-rose">4</span>
                    <div>
                        <span class="badge bg-danger-subtle text-danger border rounded-pill px-3 py-1 mb-1">Teknologi Cerdas</span>
                        <h4 class="fw-bold mb-1 text-slate-800">Pemindaian Dokumen Bukti (OpenCV.js)</h4>
                        <p class="text-muted small mb-0">Meluruskan foto KTP jaminan, surat tugas, atau berkas arsip secara otomatis seperti mesin scanner.</p>
                    </div>
                </div>
                <a href="{{ route('pemindai.index') }}" class="btn btn-outline-primary btn-sm rounded-pill btn-step-action no-print">
                    Buka Pemindai Dokumen <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <h6 class="fw-bold text-slate-800 mb-2"><i class="bi bi-check2-circle text-primary me-1"></i> Cara Kerja Pemindai Cerdas:</h6>
                    <ol class="small text-muted vstack gap-2 ps-3 mb-3">
                        <li>Buka menu <strong>Pemindai Dokumen</strong>.</li>
                        <li>Gunakan tombol <strong>Kamera</strong> untuk mengambil foto langsung, atau tombol <strong>Unggah Foto</strong> untuk memilih gambar dari galeri.</li>
                        <li>Komputer vision (OpenCV.js) akan mendeteksi 4 sudut tepi kertas secara cerdas. Anda dapat menggeser titik sudut secara manual jika pencahayaan foto kurang rata.</li>
                        <li>Klik tombol <strong>Pangkas &amp; Luruskan Dokumen</strong>. Berkas akan terpotong tegak simetris dan bersih.</li>
                        <li>Simpan hasil pindaian dan lampirkan ke surat peminjaman sebagai bukti jaminan yang sah.</li>
                    </ol>
                </div>
                <div class="col-12 col-md-5">
                    <div class="tip-box mb-2">
                        <strong><i class="bi bi-camera-fill me-1 text-primary"></i> Tips Pemotretan:</strong><br>
                        Letakkan dokumen di atas latar belakang yang kontras (misal meja berwarna gelap untuk dokumen putih) agar algoritma pendeteksi sudut bekerja maksimal.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LANGKAH 5 -->
    <div class="col-12" id="langkah5">
        <div class="guide-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0">
                    <span class="flow-step-number pastel-amber">5</span>
                    <div>
                        <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-3 py-1 mb-1">Tahap Pengembalian</span>
                        <h4 class="fw-bold mb-1 text-slate-800">Pengembalian Berkas &amp; Pemeriksaan Fisik</h4>
                        <p class="text-muted small mb-0">Memverifikasi berkas kembali ke depo, memeriksa kondisi fisik, dan mengembalikan jaminan.</p>
                    </div>
                </div>
                <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-primary btn-sm rounded-pill btn-step-action no-print">
                    Buka Pengembalian <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <h6 class="fw-bold text-slate-800 mb-2"><i class="bi bi-check2-circle text-primary me-1"></i> Prosedur Pengembalian:</h6>
                    <ol class="small text-muted vstack gap-2 ps-3 mb-3">
                        <li>Buka menu <strong>Pengembalian</strong> saat peminjam datang menyerahkan berkas.</li>
                        <li>Cari nomor surat atau nama peminjam pada daftar peminjaman aktif.</li>
                        <li>Periksa kondisi berkas fisik:
                            <ul>
                                <li>Pilih opsi kondisi saat kembali: <strong>Baik</strong>, <strong>Rusak Ringan</strong>, atau <strong>Rusak Berat</strong>.</li>
                                <li>Opsional: Anda dapat menggunakan modul CNN untuk analisis kerusakan otomatis.</li>
                            </ul>
                        </li>
                        <li>Klik <strong>Proses Pengembalian</strong>. Sistem akan mengubah status surat menjadi <strong>Selesai</strong> dan status berkas arsip kembali menjadi <strong>Tersedia</strong> di depo.</li>
                        <li>Serahkan kembali barang jaminan (KTP/SIM/dll) kepada peminjam.</li>
                    </ol>
                </div>
                <div class="col-12 col-md-5">
                    <div class="tip-box mb-2">
                        <strong><i class="bi bi-clock-history me-1 text-info"></i> Perpanjangan Masa Pinjam:</strong><br>
                        Jika peminjam belum selesai menggunakan berkas, peminjaman dapat diperpanjang maksimal <strong>2 kali</strong> sebelum melewati tanggal jatuh tempo melalui tombol <em>Perpanjang</em> di detail surat.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LANGKAH 6 -->
    <div class="col-12" id="langkah6">
        <div class="guide-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1 min-w-0">
                    <span class="flow-step-number pastel-mint">6</span>
                    <div>
                        <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-1 mb-1">Tahap Pelaporan &amp; Evaluasi</span>
                        <h4 class="fw-bold mb-1 text-slate-800">Laporan Tahunan &amp; Ekspor Resmi (PDF &amp; Excel)</h4>
                        <p class="text-muted small mb-0">Merekapitulasi seluruh layanan tahunan untuk keperluan laporan ke pimpinan dinas.</p>
                    </div>
                </div>
                <a href="{{ route('laporan.index') }}" class="btn btn-outline-primary btn-sm rounded-pill btn-step-action no-print">
                    Buka Laporan Tahunan <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <h6 class="fw-bold text-slate-800 mb-2"><i class="bi bi-check2-circle text-primary me-1"></i> Fitur &amp; Dokumen Laporan:</h6>
                    <ol class="small text-muted vstack gap-2 ps-3 mb-3">
                        <li>Buka menu <strong>Laporan Tahunan</strong>. Pilih tahun dan bidang yang ingin dievaluasi.</li>
                        <li>Pantau metrik utama: total permohonan, tingkat penyelesaian (%), rincian layanan fisik vs digital vs informasi, dan kasus keterlambatan.</li>
                        <li>Analisis grafik batang tren peminjaman per bulan dan daftar 10 berkas arsip terpopuler.</li>
                        <li>Unduh laporan:
                            <ul>
                                <li><strong>Ekspor ke PDF (.pdf)</strong>: Dokumen resmi A4 Lanskap lengkap dengan Kop Dinas Kabupaten Cirebon, tabel rekap bulanan, rincian permohonan, dan kolom tanda tangan pimpinan.</li>
                                <li><strong>Ekspor ke Excel (.xlsx)</strong>: Spreadsheet tabel rekapitulasi lengkap untuk arsip data dinas.</li>
                            </ul>
                        </li>
                    </ol>
                </div>
                <div class="col-12 col-md-5">
                    <div class="tip-box mb-2">
                        <strong><i class="bi bi-shield-check me-1 text-success"></i> Standar Resmi Kearsipan:</strong><br>
                        Format PDF rekapitulasi telah disesuaikan dengan pedoman evaluasi kearsipan daerah dan tata naskah dinas Kabupaten Cirebon.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KOMPARASI 3 BENTUK LAYANAN ARSIP -->
<div class="guide-card mb-4">
    <div class="sipa-kicker mb-1">PERBEDAAN JENIS SIRKULASI</div>
    <h5 class="fw-bold text-slate-800 mb-3"><i class="bi bi-collection-fill text-primary me-2"></i>Panduan Memilih Bentuk Layanan</h5>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="role-card">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="pastel-icon-box pastel-navy"><i class="bi bi-archive-fill"></i></span>
                    <h6 class="fw-bold text-slate-800 mb-0">1. Peminjaman Fisik</h6>
                </div>
                <p class="small text-muted mb-2">Berkas arsip asli dikeluarkan dari boks/rak depo untuk dibawa oleh peminjam.</p>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Wajib mencatat jaminan identitas asli.</li>
                    <li>Ada batas waktu jatuh tempo (maksimal 7 hari).</li>
                    <li>Wajib melalui prosedur pengembalian fisik dan pengecekan kondisi berkas.</li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="role-card">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="pastel-icon-box pastel-sky"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                    <h6 class="fw-bold text-slate-800 mb-0">2. Salinan Digital (PDF)</h6>
                </div>
                <p class="small text-muted mb-2">Peminjam memperoleh salinan dokumen digital dalam format PDF.</p>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Berkas fisik asli tetap aman tersimpan di depo.</li>
                    <li>Tidak ada risiko kehilangan atau kerusakan fisik.</li>
                    <li>Layanan dapat langsung diselesaikan tanpa perlu menunggu pengembalian fisik.</li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="role-card">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="pastel-icon-box pastel-mint"><i class="bi bi-book-half"></i></span>
                    <h6 class="fw-bold text-slate-800 mb-0">3. Layanan Informasi</h6>
                </div>
                <p class="small text-muted mb-2">Peminjam / pemohon membaca atau mencatat informasi berkas langsung di ruang baca dinas.</p>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Tidak diperkenankan membawa berkas keluar kantor.</li>
                    <li>Tetap dicatat dalam surat bukti pemanfaatan arsip.</li>
                    <li>Selesai pada hari yang sama setelah konsultasi tuntas.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- TANYA JAWAB (FAQ) SECTION -->
<div class="guide-card" id="faqSection">
    <div class="sipa-kicker mb-1">PERTANYAAN YANG SERING DIAJUKAN</div>
    <h5 class="fw-bold text-slate-800 mb-3"><i class="bi bi-question-circle-fill text-primary me-2"></i>Tanya Jawab &amp; Penanganan Kendala (FAQ)</h5>

    <div class="accordion accordion-flush" id="accordionFaq">
        <div class="accordion-item border rounded-3 mb-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold text-slate-800" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                    1. Bagaimana jika peminjam terlambat mengembalikan berkas arsip fisik?
                </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                    Sistem SIPA secara otomatis menandai surat peminjaman yang melewati tanggal jatuh tempo dengan status <strong>Terlambat</strong> dan menampilkannya pada kartu peringatan merah di Dasbor. Petugas dapat menghubungi peminjam melalui nomor kontak WhatsApp yang tertera di surat untuk penarikan berkas.
                </div>
            </div>
        </div>

        <div class="accordion-item border rounded-3 mb-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold text-slate-800" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    2. Apakah masa peminjaman berkas bisa diperpanjang?
                </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                    Bisa. Peminjaman dapat diperpanjang maksimal <strong>2 kali</strong>, dengan durasi perpanjangan maksimal sesuai aturan batas pinjam unit pengolah. Buka detail surat peminjaman lalu klik tombol <strong>Perpanjang</strong> dan masukkan tanggal kembali yang baru serta alasan perpanjangan.
                </div>
            </div>
        </div>

        <div class="accordion-item border rounded-3 mb-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold text-slate-800" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                    3. Siapa saja yang harus menandatangani surat peminjaman arsip?
                </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                    Surat peminjaman memuat 3 kolom tanda tangan resmi:
                    <ul>
                        <li><strong>Peminjam</strong> (kiri): Orang yang menerima berkas / layanan.</li>
                        <li><strong>Mengetahui Kepala Bidang (Kabid)</strong> (tengah): Pejabat struktural penanggung jawab unit pengolah.</li>
                        <li><strong>Petugas Layanan</strong> (kanan): Petugas dinas yang bertugas menyerahkan berkas dengan mencantumkan nama asli, NIP, dan bidangnya.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item border rounded-3 mb-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold text-slate-800" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                    4. Bagaimana jika ingin mengganti password akun atau lupa kata sandi?
                </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                    Untuk mengganti password saat sudah masuk ke sistem, klik tombol <strong>Sandi</strong> (ikon kunci) pada bilah atas layar. Jika lupa password dan tidak dapat masuk, gunakan tautan <strong>Lupa Password?</strong> di halaman login untuk mengajukan permohonan reset langsung kepada Super Admin.
                </div>
            </div>
        </div>

        <div class="accordion-item border rounded-3 mb-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold text-slate-800" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                    5. Bagaimana cara mengecek detail uraian arsip sebelum memilih berkas ke keranjang peminjaman?
                </button>
            </h2>
            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                    Pada formulir peminjaman baru, setiap pilihan berkas memiliki tombol <strong>Lihat Detail</strong> atau <strong>Detail</strong>. Saat diklik, modal rincian akan muncul menampilkan uraian berkas, uraian informasi isi naskah, tahun, kurun waktu, jumlah lembar, serta lokasi depo fisik berkas tersebut.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
