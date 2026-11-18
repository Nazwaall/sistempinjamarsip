@extends('layouts.app')

@section('title', 'Cadangan & Backup Data')

@section('content')
<div class="sipa-page-hero mb-3">
    <div class="sipa-kicker">KEAMANAN &amp; SUPER ADMIN</div>
    <h3 class="fw-bold mb-1 text-slate-800">Cadangan &amp; Pemulihan Data Sistem</h3>
    <p class="text-muted small mb-0">Unduh salinan cadangan (*backup*) database dan riwayat log audit untuk proteksi aset kearsipan daerah dan pemulihan bencana.</p>
</div>

<!-- Kartu Ringkasan Status Database -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-xs rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-hdd-network text-primary fs-5"></i>
                <span class="text-muted small fw-semibold">Database Engine</span>
            </div>
            <div class="fw-bold text-slate-800 fs-5 text-uppercase">{{ $stats['driver'] }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">Basis Data: <span class="fw-semibold">{{ $stats['database'] }}</span></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-xs rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-archive text-success fs-5"></i>
                <span class="text-muted small fw-semibold">Total Berkas Arsip</span>
            </div>
            <div class="fw-bold text-slate-800 fs-5">{{ number_format($stats['total_arsip'], 0, ',', '.') }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">Tercatat di Depo Arsip</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-xs rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-file-earmark-text text-info fs-5"></i>
                <span class="text-muted small fw-semibold">Layanan Peminjaman</span>
            </div>
            <div class="fw-bold text-slate-800 fs-5">{{ number_format($stats['total_peminjaman'], 0, ',', '.') }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">Dari {{ $stats['total_peminjam'] }} Data Peminjam</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-xs rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-shield-check text-warning fs-5"></i>
                <span class="text-muted small fw-semibold">Total Rekam Audit</span>
            </div>
            <div class="fw-bold text-slate-800 fs-5">{{ number_format($stats['total_logs'], 0, ',', '.') }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">Aktivitas Tercatat</div>
        </div>
    </div>
</div>

<!-- 2 Opsi Utama Backup -->
<div class="row g-4 mb-4">
    <!-- Opsi 1: SQL Full Backup -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100 p-4 bg-white d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 text-primary" style="background-color: #eff6ff;">
                            <i class="bi bi-database-fill-down fs-2"></i>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 mb-1">Full Database Dump</span>
                            <h5 class="fw-bold text-slate-800 mb-0">Cadangan Penuh Database (.sql)</h5>
                        </div>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Menghasilkan file salinan skema struktur tabel (*CREATE TABLE*) serta seluruh data rekaman (*INSERT INTO*) untuk semua entitas: <strong>arsip</strong>, <strong>surat peminjaman</strong>, <strong>peminjam</strong>, <strong>pengguna</strong>, <strong>unit bidang</strong>, <strong>pengaturan</strong>, dan <strong>log audit</strong>.
                </p>
                <div class="p-3 bg-light rounded-3 border mb-3 small text-secondary">
                    <div class="fw-semibold text-slate-800 mb-1"><i class="bi bi-check2-circle text-success me-1"></i> Format Standar MySQL / MariaDB:</div>
                    <ul class="mb-0 ps-3" style="font-size: 0.8rem;">
                        <li>Kompatibel dengan phpMyAdmin, MySQL Workbench, DBeaver, dan terminal CLI.</li>
                        <li>Menggunakan proteksi <code>FOREIGN_KEY_CHECKS=0</code> saat proses impor.</li>
                        <li>Format file otomatis diberi penamaan tanggal dan jam unduhan.</li>
                    </ul>
                </div>
            </div>
            <div>
                <a href="{{ route('backup.database') }}" class="btn btn-primary w-100 rounded-pill py-2.5 fw-semibold shadow-xs d-flex align-items-center justify-content-center gap-2" style="background-color: #162660; border-color: #162660;">
                    <i class="bi bi-download fs-6"></i>
                    <span>Unduh Cadangan Database (.sql)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Opsi 2: CSV Log Audit Backup -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100 p-4 bg-white d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 text-success" style="background-color: #f0fdf4;">
                            <i class="bi bi-file-earmark-spreadsheet-fill fs-2"></i>
                        </div>
                        <div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 mb-1">Excel & Spreadsheet CSV</span>
                            <h5 class="fw-bold text-slate-800 mb-0">Cadangan Data Log Audit (.csv)</h5>
                        </div>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Mengekspor seluruh rekam jejak aktivitas, transaksi peminjaman, verifikasi fisik, penambahan/penghapusan data, dan aktivitas masuk petugas ke dalam format spreadsheet terstruktur untuk audit internal instansi.
                </p>
                <div class="p-3 bg-light rounded-3 border mb-3 small text-secondary">
                    <div class="fw-semibold text-slate-800 mb-1"><i class="bi bi-check2-circle text-success me-1"></i> Keunggulan Ekspor CSV:</div>
                    <ul class="mb-0 ps-3" style="font-size: 0.8rem;">
                        <li>Dilengkapi UTF-8 BOM agar langsung terbaca rapi di Microsoft Excel tanpa error karakter.</li>
                        <li>Memuat detail waktu WIB, nama petugas, username, peran, jenis aksi, dan rincian perubahan.</li>
                        <li>Cocok diserahkan sebagai laporan pertanggungjawaban pengawasan TI ke pimpinan.</li>
                    </ul>
                </div>
            </div>
            <div>
                <a href="{{ route('backup.audit-log') }}" class="btn btn-outline-success w-100 rounded-pill py-2.5 fw-semibold shadow-xs d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-filetype-csv fs-5"></i>
                    <span>Unduh Cadangan Log Audit (.csv)</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Unduhan Backup -->
<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-clock-history text-secondary me-2"></i>Riwayat Unduhan Cadangan Terakhir</h6>
        <span class="badge bg-light text-muted border">Tercatat Otomatis di Log Audit</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-3" style="width: 190px;">Waktu Ekspor</th>
                    <th style="width: 180px;">Super Admin</th>
                    <th>Jenis Cadangan</th>
                    <th>Nama Berkas</th>
                    <th class="pe-3" style="width: 130px;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBackups as $b)
                    <tr>
                        <td class="ps-3 text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> {{ $b->created_at?->translatedFormat('d M Y, H:i') }} WIB
                        </td>
                        <td>
                            <div class="fw-semibold text-slate-800">{{ $b->user?->name ?? 'SuperAdmin' }}</div>
                            <small class="text-muted">{{ $b->user?->username ?? '-' }}</small>
                        </td>
                        <td>
                            @if(str_contains($b->aksi, '.sql'))
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                    <i class="bi bi-database me-1"></i> Database (.sql)
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Log Audit (.csv)
                                </span>
                            @endif
                        </td>
                        <td class="font-monospace small text-slate-800">
                            {{ $b->detail['filename'] ?? '-' }}
                        </td>
                        <td class="pe-3 small text-muted">
                            {{ $b->detail['ip'] ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">
                            <i class="bi bi-inbox d-block fs-3 mb-1 text-secondary opacity-50"></i>
                            Belum ada riwayat pengunduhan cadangan yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
