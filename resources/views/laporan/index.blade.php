@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi Tahunan')

@push('styles')
<style>
    .kpi-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        padding: 1.1rem 1.15rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(22, 38, 96, 0.08);
    }
    .kpi-accent {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
    }
    .kpi-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .month-chart-wrap {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0.45rem;
        align-items: end;
        height: 155px;
        padding-top: 1.25rem;
    }
    .month-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
    }
    .month-bar-track {
        width: 100%;
        max-width: 28px;
        height: 105px;
        background: #f1f5f9;
        border-radius: 8px;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
    }
    .month-bar-fill {
        width: 100%;
        border-radius: 8px 8px 4px 4px;
        background: linear-gradient(180deg, #38bdf8 0%, #2563eb 100%);
        transition: height 0.35s ease;
        min-height: 4px;
    }
    .month-bar-fill.is-zero {
        background: #e2e8f0;
        min-height: 4px;
    }
    .month-bar-val {
        font-size: 0.7rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 3px;
    }
    .month-bar-lbl {
        font-size: 0.68rem;
        font-weight: 600;
        color: #64748b;
        margin-top: 5px;
    }
    .rank-medal {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .rank-top-1 { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .rank-top-2 { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .rank-top-3 { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .rank-other { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
@php
    $pctSelesai = $totalPeminjaman > 0 ? round(($totalDikembalikan / $totalPeminjaman) * 100) : 0;
    $namaBulanPendek = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $maxBulanan = max(1, (int) collect($rekapBulanan)->max());
    $maxArsipPinjam = max(1, (int) ($arsipTerbanyak->max('total_pinjam') ?? 1));
    $selectedUnitObj = request('unit_pengolah_id') ? $unitList->firstWhere('id', (int) request('unit_pengolah_id')) : null;
@endphp

<div class="row g-3">
    <!-- Hero Header & Tombol Ekspor -->
    <div class="col-12">
        <div class="sipa-page-hero d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="sipa-kicker mb-0">LAPORAN &amp; REKAPITULASI TAHUNAN</span>
                    <span class="badge bg-white text-dark border rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bi bi-calendar3 text-primary me-1"></i> Periode {{ $tahun }}
                    </span>
                    @if ($selectedUnitObj)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.72rem;">
                            {{ $selectedUnitObj->nama }}
                        </span>
                    @endif
                </div>
                <h3 class="fw-bold mb-1 text-slate-800">Laporan &amp; Rekapitulasi Pemanfaatan Arsip</h3>
                <p class="mb-0 text-muted small">Pantau tren bulanan, peringkat berkas terpopuler, dan unduh dokumen rekap resmi untuk evaluasi pimpinan.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('laporan.pdf', ['tahun' => $tahun, 'unit_pengolah_id' => request('unit_pengolah_id')]) }}" class="btn btn-navy btn-sm rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ekspor ke PDF (.pdf)
                </a>
                <a href="{{ route('laporan.excel', ['tahun' => $tahun, 'unit_pengolah_id' => request('unit_pengolah_id')]) }}" class="btn btn-outline-success btn-sm rounded-3 px-3 py-2 fw-semibold bg-white d-inline-flex align-items-center">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Ekspor ke Excel (.xlsx)
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Tahun & Bidang -->
    <div class="col-12">
        <div class="sipa-filter-card">
            <form action="{{ route('laporan.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pilih Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @foreach ($tahunList as $th)
                            <option value="{{ (int)$th }}" {{ $tahun == $th ? 'selected' : '' }}>Tahun {{ (int)$th }}</option>
                        @endforeach
                    </select>
                </div>

                @if (Auth::user()->isSuperAdmin())
                    <div class="col-6 col-md-5 col-lg-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Bidang Pembuat Surat</label>
                        <select name="unit_pengolah_id" class="form-select form-select-sm">
                            <option value="">Seluruh Bidang (Dinas)</option>
                            @foreach ($unitList as $u)
                                <option value="{{ $u->id }}" {{ request('unit_pengolah_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama }} ({{ $u->kode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                    </button>
                    @if(request()->filled('unit_pengolah_id') || (request()->filled('tahun') && (int) request('tahun') !== (int) date('Y')))
                        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- 4 Kartu KPI Laporan -->
    <div class="col-6 col-lg-3">
        <div class="kpi-card h-100">
            <div class="kpi-accent bg-primary"></div>
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-secondary small fw-semibold">Total Permohonan Layanan</div>
                    <div class="fs-2 fw-bold text-primary lh-sm mt-1">{{ number_format($totalPeminjaman) }}</div>
                </div>
                <span class="kpi-icon-wrap bg-primary-subtle text-primary">
                    <i class="bi bi-journals"></i>
                </span>
            </div>
            <div class="d-flex flex-wrap gap-1 mt-2" style="font-size: 0.72rem;">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill">Menunggu: {{ $totalMenunggu }}</span>
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill">Aktif: {{ $totalAktif }}</span>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="kpi-card h-100">
            <div class="kpi-accent bg-success"></div>
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-secondary small fw-semibold">Selesai / Dikembalikan</div>
                    <div class="fs-2 fw-bold text-success lh-sm mt-1">{{ number_format($totalDikembalikan) }}</div>
                </div>
                <span class="kpi-icon-wrap bg-success-subtle text-success">
                    <i class="bi bi-check2-circle"></i>
                </span>
            </div>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-success rounded-pill" style="width: {{ $pctSelesai }}%;"></div>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Tingkat penyelesaian {{ $pctSelesai }}%</small>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="kpi-card h-100">
            <div class="kpi-accent bg-info"></div>
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-secondary small fw-semibold">Sedang Dipinjam (Aktif)</div>
                    <div class="fs-2 fw-bold text-info lh-sm mt-1">{{ number_format($totalAktif) }}</div>
                </div>
                <span class="kpi-icon-wrap bg-info-subtle text-info">
                    <i class="bi bi-hourglass-split"></i>
                </span>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                Fisik: <strong>{{ $totalFisik ?? 0 }}</strong> &bull; Digital: <strong>{{ $totalDigital ?? 0 }}</strong> &bull; Info: <strong>{{ $totalInformasi ?? 0 }}</strong>
            </small>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="kpi-card h-100">
            <div class="kpi-accent bg-danger"></div>
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-secondary small fw-semibold">Terlambat Kembali</div>
                    <div class="fs-2 fw-bold text-danger lh-sm mt-1">{{ number_format($totalTerlambat) }}</div>
                </div>
                <span class="kpi-icon-wrap bg-danger-subtle text-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                Int: {{ $peminjamanInternal }} &bull; SKPD: {{ $peminjamanSkpd ?? 0 }} &bull; Kec: {{ $peminjamanKecamatan ?? 0 }} &bull; Eks: {{ $peminjamanEksternal }}
            </small>
        </div>
    </div>

    <!-- Grafik Tren Bulanan & Komposisi Layanan -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Tren Peminjaman Bulanan Tahun {{ $tahun }}</h6>
                <span class="badge bg-light text-secondary border rounded-pill">Total {{ number_format($totalPeminjaman) }} Surat</span>
            </div>
            <div class="card-body">
                <div class="month-chart-wrap">
                    @foreach ($namaBulanPendek as $bln => $nm)
                        @php
                            $val = (int) ($rekapBulanan[$bln] ?? 0);
                            $heightPct = $val > 0 ? max(10, round(($val / $maxBulanan) * 100)) : 4;
                        @endphp
                        <div class="month-col" title="{{ $nm }} {{ $tahun }}: {{ $val }} surat">
                            <span class="month-bar-val">{{ $val }}</span>
                            <div class="month-bar-track">
                                <div class="month-bar-fill {{ $val === 0 ? 'is-zero' : '' }}" style="height: {{ $heightPct }}%;"></div>
                            </div>
                            <span class="month-bar-lbl">{{ $nm }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-pie-chart-fill text-info me-2"></i>Komposisi Layanan &amp; Peminjam</h6>
            </div>
            <div class="card-body small">
                @php
                    $layananBars = [
                        ['label' => 'Peminjaman Fisik', 'val' => $totalFisik ?? 0, 'cls' => 'bg-primary'],
                        ['label' => 'Salinan Digital (PDF)', 'val' => $totalDigital ?? 0, 'cls' => 'bg-info'],
                        ['label' => 'Layanan Informasi', 'val' => $totalInformasi ?? 0, 'cls' => 'bg-success'],
                    ];
                    $kategoriBars = [
                        ['label' => 'Internal Dinas', 'val' => $peminjamanInternal ?? 0, 'cls' => 'bg-primary'],
                        ['label' => 'SKPD / Dinas Lain', 'val' => $peminjamanSkpd ?? 0, 'cls' => 'bg-info'],
                        ['label' => 'Kecamatan / Kelurahan', 'val' => $peminjamanKecamatan ?? 0, 'cls' => 'bg-warning'],
                        ['label' => 'Eksternal / Masyarakat', 'val' => $peminjamanEksternal ?? 0, 'cls' => 'bg-success'],
                    ];
                @endphp

                <div class="fw-semibold text-secondary mb-2">Bentuk Layanan</div>
                @foreach ($layananBars as $lb)
                    @php $pct = $totalPeminjaman > 0 ? round(($lb['val'] / $totalPeminjaman) * 100) : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $lb['label'] }}</span>
                            <span class="fw-bold">{{ $lb['val'] }} <span class="text-muted fw-normal">({{ $pct }}%)</span></span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $lb['cls'] }} rounded-pill" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach

                <hr class="my-3">

                <div class="fw-semibold text-secondary mb-2">Kategori Peminjam</div>
                @foreach ($kategoriBars as $kb)
                    @php $pct = $totalPeminjaman > 0 ? round(($kb['val'] / $totalPeminjaman) * 100) : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $kb['label'] }}</span>
                            <span class="fw-bold">{{ $kb['val'] }}</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar {{ $kb['cls'] }} rounded-pill" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 10 Arsip Paling Sering Dipinjam -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-trophy-fill text-warning me-2"></i>10 Berkas Paling Sering Dipinjam</h6>
                <span class="text-muted small">{{ $arsipTerbanyak->count() }} arsip</span>
            </div>
            <div class="card-body p-0">
                @if ($arsipTerbanyak->isEmpty())
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                        Belum ada statistik peminjaman pada tahun {{ $tahun }}.
                    </div>
                @else
                    <ul class="list-group list-group-flush mb-0">
                        @foreach ($arsipTerbanyak as $idx => $at)
                            @php
                                $rankCls = match ($idx) {
                                    0 => 'rank-top-1',
                                    1 => 'rank-top-2',
                                    2 => 'rank-top-3',
                                    default => 'rank-other',
                                };
                            @endphp
                            <li class="list-group-item py-3 px-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                        <span class="rank-medal {{ $rankCls }} flex-shrink-0">{{ $idx + 1 }}</span>
                                        <div class="min-w-0 flex-grow-1">
                                            @if ($at->arsip)
                                                <a href="{{ route('arsip.show', $at->arsip) }}" class="fw-bold text-decoration-none text-dark d-block text-truncate mb-0.5" style="font-size: 0.85rem;">
                                                    {{ $at->arsip->kode_internal }}
                                                </a>
                                                <div class="text-secondary small text-truncate" style="font-size: 0.77rem; line-height: 1.3;">
                                                    {{ $at->arsip->uraian_berkas }}
                                                </div>
                                            @else
                                                <span class="text-muted small">Arsip #{{ $at->arsip_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">
                                            {{ $at->total_pinjam }}x Pinjam
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <!-- Daftar Surat Rekapitulasi -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-table text-primary me-2"></i>Rincian Surat Peminjaman Tahun {{ $tahun }}</h6>
                <span class="text-muted small">{{ $daftarSurat->total() }} surat</span>
            </div>
            <div class="card-body p-0">
                @if ($daftarSurat->isEmpty())
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-journal-x fs-2 d-block mb-2 opacity-50"></i>
                        Tidak ada data surat pada tahun {{ $tahun }}.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 text-nowrap" style="font-size: 0.82rem;">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th class="ps-3">No. Surat &amp; Layanan</th>
                                    <th>Peminjam</th>
                                    <th>Tgl Pinjam &amp; Tempo</th>
                                    <th>Pengembalian</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($daftarSurat as $s)
                                    @php
                                        $isTerlambat = $s->isTerlambat();
                                        $stInfo = $s->statusBadgeInfo();
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('peminjaman.show', $s) }}" class="fw-bold text-decoration-none text-primary">
                                                {{ $s->nomor_surat }}
                                            </a>
                                            <small class="text-muted d-block">{{ $s->unitPengolah?->nama ?? '-' }}</small>
                                            <span class="badge {{ $s->layananBadgeClass() }}" style="font-size: 0.68rem;">{{ $s->bentuk_layanan ?: 'Fisik' }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $s->peminjam?->nama ?? '-' }}</div>
                                            <small class="text-muted">{{ $s->peminjam?->instansi_unit ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <div>{{ \Carbon\Carbon::parse($s->tanggal_mulai)->translatedFormat('d M Y') }}</div>
                                            <small class="text-muted" style="font-size: 0.72rem;">Tempo: {{ \Carbon\Carbon::parse($s->tanggal_kembali)->translatedFormat('d M Y') }}</small>
                                        </td>
                                        <td>
                                            @if ($s->status === 'Menunggu Persetujuan')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                                    <i class="bi bi-hourglass-split me-1"></i> Belum Disetujui
                                                </span>
                                            @elseif ($s->status === 'Janjian')
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill">
                                                    <i class="bi bi-calendar2-check me-1"></i> {{ $s->rencana_ambil_pada ? $s->rencana_ambil_pada->format('d/m H:i') : 'Jadwal Janjian' }}
                                                </span>
                                            @elseif ($s->status === 'Ditolak' || $s->status === 'Dibatalkan')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                    <i class="bi bi-x-circle me-1"></i> {{ $s->status }}
                                                </span>
                                            @elseif ($s->status === 'Selesai')
                                                <div class="text-success fw-semibold small">
                                                    <i class="bi bi-check-circle-fill me-1"></i> {{ $s->tanggal_selesai ? \Carbon\Carbon::parse($s->tanggal_selesai)->translatedFormat('d M Y') : 'Selesai' }}
                                                </div>
                                            @elseif ($isTerlambat)
                                                <div class="text-danger small fw-semibold">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Belum Kembali
                                                </div>
                                            @else
                                                <div class="text-primary small fw-semibold">
                                                    <i class="bi bi-clock-history me-1"></i> {{ $s->isFisik() ? 'Sedang Dipinjam' : 'Layanan Berjalan' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $stInfo['class'] }} rounded-pill">
                                                <i class="bi {{ $stInfo['icon'] }} me-1"></i> {{ $stInfo['label'] }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="action-btn-group">
                                                <a href="{{ route('peminjaman.show', $s) }}" class="btn-action btn-action-primary" title="Detail Surat">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('peminjaman.pdf', $s) }}" class="btn-action btn-action-danger" title="Unduh Surat PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        {{ $daftarSurat->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
