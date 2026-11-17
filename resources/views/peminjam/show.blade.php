@extends('layouts.app')

@section('title', 'Detail Peminjam: ' . $peminjam->nama)

@push('styles')
<style>
    .profile-banner {
        height: 78px;
        background: linear-gradient(135deg, #10193b 0%, #162660 60%, #1e3a8a 100%);
    }
    .profile-avatar {
        width: 74px;
        height: 74px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        margin-top: -38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        font-weight: 800;
        background: #eef2ff;
        color: #162660;
        box-shadow: 0 4px 12px rgba(22, 38, 96, 0.14);
    }
    .stat-mini {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 1rem;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }
    .stat-mini:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
</style>
@endpush

@section('content')
@php
    $parts = preg_split('/\s+/', trim($peminjam->nama));
    $initials = strtoupper(mb_substr($parts[0] ?? 'P', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
@endphp

<div class="row align-items-center mb-3 g-2">
    <div class="col-12 col-md-auto me-auto">
        <a href="{{ route('peminjam.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill mb-1">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Peminjam
        </a>
        <h4 class="fw-bold mb-0 mt-1"><i class="bi bi-person-badge-fill text-primary me-2"></i>{{ $peminjam->nama }}</h4>
    </div>
    <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
        <a href="{{ route('peminjam.show.pdf', $peminjam) }}" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm fw-semibold">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ekspor Riwayat PDF
        </a>
        <a href="{{ route('peminjaman.create') }}?peminjam_id={{ $peminjam->id }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
            <i class="bi bi-plus-circle me-1"></i> Buat Peminjaman untuk Peminjam Ini
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Profil Peminjam -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="profile-banner"></div>
            <div class="card-body pt-0 px-4 pb-4">
                <div class="text-center mb-3">
                    <div class="profile-avatar">{{ $initials }}</div>
                    <h5 class="fw-bold mt-2 mb-0">{{ $peminjam->nama }}</h5>
                    <div class="text-muted small">{{ $peminjam->jabatan ?: 'Peminjam Arsip' }}</div>
                    <span class="badge {{ $peminjam->jenisBadgeClass() }} border rounded-pill px-3 py-1 mt-2">
                        {{ $peminjam->jenisLabel() }}
                    </span>
                </div>

                <hr class="text-muted my-3">

                <div class="small vstack gap-2">
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-person-vcard me-1"></i>NIP / NIK</span>
                        <span class="fw-semibold text-end">{{ $peminjam->nip_nik ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-building me-1"></i>Instansi / Unit</span>
                        <span class="fw-semibold text-end">{{ $peminjam->instansi_unit }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-telephone me-1"></i>No. HP / WA</span>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $peminjam->no_hp) }}" target="_blank" class="fw-semibold text-decoration-none text-success text-end">
                            <i class="bi bi-whatsapp me-1"></i>{{ $peminjam->no_hp }}
                        </a>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-envelope me-1"></i>Email</span>
                        @if ($peminjam->email)
                            <a href="mailto:{{ $peminjam->email }}" class="text-decoration-none text-end">{{ $peminjam->email }}</a>
                        @else
                            <span class="text-end">-</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-card-heading me-1"></i>Jaminan</span>
                        <span class="fw-semibold text-end">{{ $peminjam->jaminan ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-geo-alt me-1"></i>Alamat</span>
                        <span class="text-end">{{ $peminjam->alamat ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-calendar-event me-1"></i>Terdaftar</span>
                        <span class="text-muted text-end">{{ $peminjam->created_at?->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik & Riwayat Peminjaman -->
    <div class="col-12 col-lg-8">
        <!-- Kartu Ringkasan Statistik -->
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="stat-mini text-center">
                    <div class="fs-3 fw-bold text-primary">{{ $totalSurat }}</div>
                    <div class="small text-muted fw-semibold">Total Surat</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-mini text-center">
                    <div class="fs-3 fw-bold text-warning">{{ $suratAktif }}</div>
                    <div class="small text-muted fw-semibold">Sedang Dipinjam</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-mini text-center">
                    <div class="fs-3 fw-bold text-success">{{ $suratSelesai }}</div>
                    <div class="small text-muted fw-semibold">Telah Selesai</div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Surat Peminjaman -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Surat Peminjaman</h6>
                <span class="badge bg-light text-secondary border rounded-pill">{{ $totalSurat }} transaksi</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="ps-3">Nomor Surat</th>
                            <th>Bidang Pembuat Surat</th>
                            <th>Rentang &amp; Layanan</th>
                            <th>Berkas</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjam->suratPeminjaman as $surat)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('peminjaman.show', $surat) }}" class="fw-bold text-decoration-none text-primary">
                                        {{ $surat->nomor_surat }}
                                    </a>
                                    <div class="text-muted small">{{ $surat->tanggal_surat->translatedFormat('d F Y') }}</div>
                                </td>
                                <td class="small">
                                    <span class="badge bg-light text-dark border">{{ $surat->unitPengolah?->kode ?? '-' }}</span>
                                    <div class="text-muted small text-truncate" style="max-width: 140px;">{{ $surat->unitPengolah?->nama }}</div>
                                </td>
                                <td class="small">
                                    <div>{{ $surat->tanggal_mulai->translatedFormat('d M Y') }} s/d {{ $surat->tanggal_kembali->translatedFormat('d M Y') }}</div>
                                    <span class="badge bg-light text-secondary">{{ $surat->lama_pinjam_hari }} hari</span>
                                    <span class="badge {{ $surat->layananBadgeClass() }}">{{ $surat->bentuk_layanan ?: 'Fisik' }}</span>
                                </td>
                                <td class="small">
                                    <span class="fw-semibold">{{ $surat->items->count() }} berkas arsip</span>
                                    @if($surat->jaminan)
                                        <div class="text-muted small"><i class="bi bi-card-heading me-1"></i>{{ $surat->jaminan }}</div>
                                    @endif
                                    <div class="text-muted small text-truncate" style="max-width: 160px;">
                                        {{ $surat->items->first()?->arsip?->uraian_berkas }}
                                    </div>
                                </td>
                                <td>
                                    @php $stBadge = $surat->statusBadgeInfo(); @endphp
                                    <span class="badge {{ $stBadge['class'] }}">{{ $stBadge['label'] }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="action-btn-group">
                                        <a href="{{ route('peminjaman.show', $surat) }}" class="btn-action btn-action-primary" title="Detail Surat">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('peminjaman.pdf', $surat) }}" class="btn-action btn-action-danger" title="Cetak PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-journal-x display-6 d-block mb-1 text-secondary opacity-50"></i>
                                    Peminjam ini belum pernah meminjam berkas arsip.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
