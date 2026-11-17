@extends('layouts.app')

@section('title', 'Dasbor Utama')

@push('styles')
<style>
    .dash-hero-stat {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.4rem 0;
    }
    .dash-hero-stat i {
        font-size: 1.55rem;
        color: #162660;
        opacity: 0.85;
    }
    .dash-floating-search {
        margin-top: -12px;
        position: relative;
        z-index: 2;
    }
    .service-tile {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.1rem 0.75rem;
        text-align: center;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .service-tile:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(22, 38, 96, 0.08);
        border-color: #cbd5e1;
    }
    .loan-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1.15rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s ease;
    }
    .loan-item-row:last-child {
        border-bottom: none;
    }
    .loan-item-row:hover {
        background-color: #f8faff;
    }
    .chip-quick {
        font-size: 0.74rem;
        padding: 0.25rem 0.72rem;
        border-radius: 8px;
        background: #f1f5f9;
        color: #334155;
        text-decoration: none;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        transition: all 0.15s ease;
    }
    .chip-quick:hover {
        background: #162660;
        color: #ffffff;
        border-color: #162660;
    }
</style>
@endpush

@section('content')
<!-- 1. HERO SECTION BERGAYA BERSIH & TERSTRUKTUR -->
<div class="sipa-page-hero mb-2">
    <div class="row align-items-center g-4">
        <div class="col-12 col-lg-8">
            <div class="sipa-kicker">SISTEM INFORMASI PEMINJAMAN ARSIP &bull; DISARPUS KAB. CIREBON</div>
            <h2 class="fw-bold text-slate-800 mb-2" style="letter-spacing: -0.02em;">
                Selamat Datang, {{ $user->name }}
            </h2>
            <p class="text-muted mb-3" style="max-width: 600px; font-size: 0.92rem;">
                Kelola peminjaman fisik, layanan salinan digital, dan pengembalian arsip secara tertib untuk
                <strong class="text-dark">{{ $user->unitPengolah ? $user->unitPengolah->nama . ' (' . $user->unitPengolah->kode . ')' : 'Seluruh Unit (Tingkat Dinas)' }}</strong>.
            </p>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <a href="{{ route('peminjaman.create') }}" class="btn btn-navy rounded-3 px-4 py-2 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Buat Peminjaman Baru
                </a>
                <a href="{{ route('arsip.index') }}" class="btn btn-outline-primary rounded-3 px-3 py-2">
                    <i class="bi bi-folder2-open me-1"></i> Data Arsip
                </a>
                <button type="button" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPanduanCepat">
                    <i class="bi bi-compass-fill me-1"></i> Alur &amp; Panduan Penggunaan
                </button>
                <span class="badge bg-white text-dark border rounded-pill px-3 py-2 fw-semibold ms-sm-1">
                    <i class="bi bi-shield-check text-primary me-1"></i> Peran: {{ $user->peranLabel() }}
                </span>
            </div>
        </div>

        <!-- Panel Ringkas Sisi Kanan Hero -->
        <div class="col-12 col-lg-4 d-none d-lg-block">
            <div class="bg-white border rounded-4 p-3 shadow-sm">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="pastel-icon-box pastel-navy">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold small text-slate-800">Ringkasan Status Hari Ini</div>
                        <div class="text-muted" style="font-size: 0.76rem;">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
                    </div>
                </div>
                <div class="row g-2 text-center mt-1">
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-2">
                            <div class="fw-bold fs-5 text-primary">{{ number_format($arsipTersedia) }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">Arsip Tersedia</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded-3 p-2">
                            <div class="fw-bold fs-5 text-warning-emphasis">{{ number_format($totalMenunggu) }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">Menunggu Persetujuan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Statistik Horisontal di Bawah Hero -->
    <div class="row g-3 pt-3 mt-1 border-top border-primary-subtle">
        <div class="col-6 col-md-3">
            <div class="dash-hero-stat">
                <i class="bi bi-folder2"></i>
                <div>
                    <div class="fw-bold fs-5 lh-1 text-slate-800">{{ number_format($totalArsip) }}</div>
                    <div class="text-muted small">{{ $arsipTersedia }} Berkas Tersedia</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-hero-stat">
                <i class="bi bi-journal-arrow-up"></i>
                <div>
                    <div class="fw-bold fs-5 lh-1 text-slate-800">{{ number_format($totalPinjamAktif) }}</div>
                    <div class="text-muted small">Peminjaman Aktif</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-hero-stat">
                <i class="bi bi-hourglass-split"></i>
                <div>
                    <div class="fw-bold fs-5 lh-1 text-slate-800">{{ number_format($totalMenunggu) }}</div>
                    <div class="text-muted small">Menunggu Persetujuan</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-hero-stat">
                <i class="bi bi-exclamation-octagon text-danger"></i>
                <div>
                    <div class="fw-bold fs-5 lh-1 text-danger">{{ number_format($totalTerlambat) }}</div>
                    <div class="text-muted small">Terlambat Kembali</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. FLOATING SEARCH BAR PENCARIAN CEPAT ARSIP -->
<div class="dash-floating-search mb-4">
    <div class="sipa-filter-card">
        <div class="text-center mb-2">
            <span class="fw-bold text-slate-800" style="font-size: 0.95rem;">Temukan Berkas Arsip dengan Cepat</span>
        </div>
        <form action="{{ route('arsip.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Kata kunci (contoh: kode berkas, uraian, depo)...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status Berkas</option>
                    <option value="Tersedia">Tersedia</option>
                    <option value="Dipinjam">Dipinjam</option>
                    <option value="Rusak">Rusak</option>
                    <option value="Hilang">Hilang</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <button type="submit" class="btn btn-navy btn-sm w-100 py-2 rounded-3">
                    <i class="bi bi-search me-1"></i> Cari Berkas
                </button>
            </div>
        </form>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2 pt-1">
            <span class="text-muted" style="font-size: 0.75rem;">Pintasan cepat:</span>
            <a href="{{ route('arsip.index', ['status' => 'Tersedia']) }}" class="chip-quick">Berkas Tersedia</a>
            <a href="{{ route('peminjaman.index', ['status' => 'Menunggu Persetujuan']) }}" class="chip-quick">Menunggu Persetujuan</a>
            <a href="{{ route('pengembalian.index') }}" class="chip-quick">Proses Pengembalian</a>
            <a href="{{ route('laporan.index') }}" class="chip-quick">Laporan Tahunan</a>
            <a href="{{ route('panduan') }}" class="chip-quick"><i class="bi bi-compass me-1"></i>Buku Panduan &amp; Alur</a>
        </div>
    </div>
</div>

<!-- 3. SEKSI LAYANAN UTAMA DENGAN IKON PASTEL -->
<div class="mb-4">
    <div class="text-center mb-3">
        <div class="sipa-kicker">NAVIGASI LAYANAN SIPA</div>
        <h5 class="fw-bold text-slate-800 mb-0">Akses Cepat Seluruh Modul Sirkulasi</h5>
    </div>
    <div class="row g-2 g-md-3">
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('peminjaman.create') }}" class="service-tile">
                <div class="pastel-icon-circle pastel-navy mb-2"><i class="bi bi-file-earmark-plus"></i></div>
                <div class="fw-bold small text-slate-800">Peminjaman Baru</div>
                <div class="text-muted" style="font-size: 0.74rem;">Buat surat peminjaman arsip</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('arsip.index') }}" class="service-tile">
                <div class="pastel-icon-circle pastel-lavender mb-2"><i class="bi bi-folder2-open"></i></div>
                <div class="fw-bold small text-slate-800">Data Arsip</div>
                <div class="text-muted" style="font-size: 0.74rem;">Kelola daftar berkas &amp; impor</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('peminjam.index') }}" class="service-tile">
                <div class="pastel-icon-circle pastel-mint mb-2"><i class="bi bi-people"></i></div>
                <div class="fw-bold small text-slate-800">Data Peminjam</div>
                <div class="text-muted" style="font-size: 0.74rem;">Direktori peminjam &amp; PDF</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pengembalian.index') }}" class="service-tile">
                <div class="pastel-icon-circle pastel-amber mb-2"><i class="bi bi-arrow-return-left"></i></div>
                <div class="fw-bold small text-slate-800">Pengembalian</div>
                <div class="text-muted" style="font-size: 0.74rem;">Verifikasi kembali &amp; CNN</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pemindai.index') }}" class="service-tile">
                <div class="pastel-icon-circle pastel-rose mb-2"><i class="bi bi-bounding-box-circles"></i></div>
                <div class="fw-bold small text-slate-800">Pemindai Cerdas</div>
                <div class="text-muted" style="font-size: 0.74rem;">Scan KTP &amp; surat pengantar</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('laporan.index') }}" class="service-tile">
                <div class="pastel-icon-circle pastel-sky mb-2"><i class="bi bi-file-earmark-bar-graph"></i></div>
                <div class="fw-bold small text-slate-800">Laporan Tahunan</div>
                <div class="text-muted" style="font-size: 0.74rem;">Ekspor rekap Excel &amp; PDF</div>
            </a>
        </div>
    </div>
</div>

<!-- 4. PERINGATAN JATUH TEMPO (JIKA ADA) -->
@if ($perluPerhatian->isNotEmpty())
    <div class="card border-warning-subtle rounded-4 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-warning-subtle text-warning-emphasis fw-bold d-flex align-items-center justify-content-between py-2 px-3">
            <span><i class="bi bi-bell-fill me-2"></i>Perhatian: Peminjaman Mendekati / Melewati Jatuh Tempo</span>
            <span class="badge bg-warning text-dark rounded-pill">{{ $perluPerhatian->count() }} surat</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                <thead>
                    <tr>
                        <th class="ps-3">Nomor Surat</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($perluPerhatian as $item)
                        @php
                            $hariIni = \Carbon\Carbon::today();
                            $tglKembali = \Carbon\Carbon::parse($item->tanggal_kembali)->startOfDay();
                            $selisihHari = $hariIni->diffInDays($tglKembali, false);
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $item->nomor_surat }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->peminjam->nama }}</div>
                                <small class="text-muted">{{ $item->peminjam->instansi_unit }} &bull; {{ $item->peminjam->no_hp }}</small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                            <td>
                                <span class="{{ $selisihHari < 0 ? 'text-danger fw-bold' : ($selisihHari === 0 ? 'text-warning-emphasis fw-bold' : 'text-dark fw-semibold') }}">
                                    {{ \Carbon\Carbon::parse($item->tanggal_kembali)->translatedFormat('d F Y') }}
                                </span>
                            </td>
                            <td>
                                @if ($selisihHari < 0)
                                    <span class="badge bg-danger rounded-pill">Terlambat {{ abs($selisihHari) }} Hari</span>
                                @elseif ($selisihHari === 0)
                                    <span class="badge bg-warning text-dark rounded-pill">Jatuh Tempo Hari Ini</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning rounded-pill">Jatuh Tempo {{ $selisihHari }} Hari Lagi</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('peminjaman.show', $item->id) }}" class="btn-detail-outline">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- 5. DAFTAR TRANSAKSI TERBARU + PANEL KANAN FITUR -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden">
            <div class="card-header bg-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <div class="sipa-kicker mb-0">TRANSAKSI TERKINI</div>
                    <h6 class="fw-bold mb-0 text-slate-800">Riwayat Peminjaman Terbaru</h6>
                </div>
                <a href="{{ route('peminjaman.index') }}" class="btn-detail-outline">
                    Lihat Semua Surat
                </a>
            </div>
            <div class="card-body p-0">
                @if ($peminjamanTerbaru->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <p class="mb-1 fw-semibold">Belum ada transaksi peminjaman.</p>
                        <small>Silakan buat peminjaman baru melalui tombol di atas.</small>
                    </div>
                @else
                    @php
                        $pastelCycle = ['pastel-navy', 'pastel-amber', 'pastel-rose', 'pastel-sky', 'pastel-mint'];
                    @endphp
                    @foreach ($peminjamanTerbaru as $idx => $p)
                        @php
                            $stInfo = $p->statusBadgeInfo();
                            $iconBox = $pastelCycle[$idx % count($pastelCycle)];
                        @endphp
                        <div class="loan-item-row">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <div class="pastel-icon-box {{ $iconBox }}">
                                    <i class="bi {{ $p->isFisik() ? 'bi-archive' : 'bi-file-earmark-pdf' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <a href="{{ route('peminjaman.show', $p->id) }}" class="fw-bold text-decoration-none text-slate-800">
                                            {{ $p->nomor_surat }}
                                        </a>
                                        <span class="badge {{ $stInfo['class'] }} rounded-pill" style="font-size: 0.7rem;">{{ $stInfo['label'] }}</span>
                                    </div>
                                    <div class="text-muted small text-truncate">
                                        {{ $p->peminjam->nama }} ({{ $p->peminjam->jenisLabel() }})
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 text-muted mt-1" style="font-size: 0.74rem;">
                                        <span><i class="bi bi-layers me-1"></i>{{ $p->bentuk_layanan ?: 'Fisik' }}</span>
                                        <span><i class="bi bi-folder2 me-1"></i>{{ $p->items->count() }} berkas</span>
                                        <span><i class="bi bi-calendar-event me-1"></i>Tempo: {{ \Carbon\Carbon::parse($p->tanggal_kembali)->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('peminjaman.show', $p->id) }}" class="btn-detail-outline">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Kartu Sorotan Fitur (Serupa Kartu Kanan di Referensi) -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-3">
            <div class="p-4 text-white" style="background: linear-gradient(135deg, #162660 0%, #1e3a8a 100%);">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-1 mb-2" style="font-size: 0.7rem;">FITUR UNGGULAN</span>
                <h5 class="fw-bold mb-1">Butuh Memindai Dokumen atau Impor Arsip?</h5>
                <p class="small text-white-50 mb-0">Gunakan Pemindai OpenCV untuk meluruskan foto KTP/surat secara otomatis atau impor daftar arsip dari Excel.</p>
            </div>
            <div class="card-body p-3 d-grid gap-2">
                <a href="{{ route('pemindai.index') }}" class="btn btn-navy rounded-3 py-2">
                    <i class="bi bi-bounding-box-circles me-1"></i> Buka Pemindai Dokumen
                </a>
                <a href="{{ route('arsip.import') }}" class="btn btn-outline-primary rounded-3 py-2">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Impor Data Arsip Excel
                </a>
                <a href="{{ route('pengembalian.index') }}" class="btn btn-light border rounded-3 py-2 text-dark fw-semibold small">
                    <i class="bi bi-arrow-return-left me-1"></i> Proses Pengembalian Arsip
                </a>
                <a href="{{ route('panduan') }}" class="btn btn-outline-info rounded-3 py-2 text-start small fw-semibold">
                    <i class="bi bi-compass-fill me-1"></i> Buka Panduan &amp; Tata Cara Lengkap
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 6. BANNER CTA PENUTUP NAVY GELAP -->
<div class="sipa-cta-banner d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-1 text-white">Siap Memproses Layanan Peminjaman Arsip Hari Ini?</h5>
        <p class="mb-0 text-white-50 small">Catat peminjaman, lihat detail uraian berkas, dan terbitkan surat resmi beserta TTD petugas layanan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('panduan') }}" class="btn btn-outline-light rounded-3 px-3 py-2 fw-semibold">
            <i class="bi bi-compass me-1"></i> Pelajari Alur
        </a>
        <a href="{{ route('peminjaman.create') }}" class="btn btn-light text-dark fw-bold rounded-3 px-4 py-2 flex-shrink-0 shadow-sm">
            Buat Peminjaman Baru <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>

<!-- MODAL PANDUAN CEPAT DASBOR -->
<div class="modal fade" id="modalPanduanCepat" tabindex="-1" aria-labelledby="modalPanduanCepatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-navy text-white py-3 px-4" style="background: #162660;">
                <div class="d-flex align-items-center gap-2">
                    <span class="pastel-icon-box pastel-navy" style="width: 36px; height: 36px;"><i class="bi bi-compass-fill text-white"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalPanduanCepatLabel">Ringkasan Alur Penggunaan SIPA</h5>
                        <small class="text-white-50">SOP Sirkulasi Arsip Dinas Kearsipan &amp; Perpustakaan Kab. Cirebon</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary rounded-circle" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center;">1</span>
                                <strong class="text-slate-800">Pendataan &amp; Impor Arsip</strong>
                            </div>
                            <p class="small text-muted mb-0">Input berkas secara manual atau impor file Excel massal pada menu <em>Data Arsip</em>. Berkas berstatus <strong>Tersedia</strong> siap untuk dipinjam.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-secondary rounded-circle" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center;">2</span>
                                <strong class="text-slate-800">Registrasi Peminjam &amp; Jaminan</strong>
                            </div>
                            <p class="small text-muted mb-0">Catat identitas peminjam (Internal, SKPD, Kecamatan, atau Eksternal) beserta nomor WhatsApp aktif dan barang jaminan (KTP/SIM/dll).</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary rounded-circle" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center;">3</span>
                                <strong class="text-slate-800">Buat Surat Peminjaman &amp; TTD</strong>
                            </div>
                            <p class="small text-muted mb-0">Pilih bidang, bentuk layanan (Fisik / Digital / Informasi), cek <strong>Detail Berkas</strong> sebelum memilih, pilih nama Petugas Layanan asli, dan bubuhkan TTD.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger rounded-circle" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center;">4</span>
                                <strong class="text-slate-800">Pemindaian Bukti (OpenCV)</strong>
                            </div>
                            <p class="small text-muted mb-0">Foto KTP jaminan atau surat tugas, modul cerdas OpenCV akan otomatis meluruskan dokumen tegak simetris untuk dilampirkan.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-warning text-dark rounded-circle" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center;">5</span>
                                <strong class="text-slate-800">Pengembalian &amp; Cek Fisik</strong>
                            </div>
                            <p class="small text-muted mb-0">Saat peminjam menyerahkan berkas: periksa kondisi fisik, proses selesai, berkas kembali <em>Tersedia</em>, dan jaminan diserahkan kembali.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success rounded-circle" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center;">6</span>
                                <strong class="text-slate-800">Laporan Tahunan &amp; Ekspor</strong>
                            </div>
                            <p class="small text-muted mb-0">Evaluasi statistik peminjaman tahunan dan ekspor dokumen resmi ber-Kop Dinas ke format <strong>PDF Lanskap</strong> maupun <strong>Excel</strong>.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 justify-content-between">
                <span class="small text-muted">Ingin panduan lengkap dengan tips dan FAQ?</span>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                    <a href="{{ route('panduan') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-book-half me-1"></i> Buka Buku Panduan Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
