@extends('layouts.app')

@section('title', 'Data Peminjam')

@push('styles')
<style>
    .peminjam-stat-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        padding: 0.95rem 1.1rem;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }
    .peminjam-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(22, 38, 96, 0.08);
        border-color: #93c5fd;
    }
    .peminjam-stat-card.is-active {
        border-color: #162660;
        background: #f1f5ff;
        box-shadow: 0 4px 12px rgba(22, 38, 96, 0.1);
    }
    .peminjam-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .avatar-initials {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.82rem;
        flex-shrink: 0;
        letter-spacing: 0.03em;
    }
    .chip-filter {
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .chip-filter:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .chip-filter.active {
        background: #162660;
        color: #ffffff;
        border-color: #162660;
    }
</style>
@endpush

@section('content')
@php
    $opsiStandarJaminan = ['KTP', 'Surat Tugas / Pengantar', 'Kartu Pegawai / ID Card', 'SIM', 'Kartu Mahasiswa / Pelajar', 'Tanpa Jaminan'];
@endphp

<!-- Hero Header -->
<div class="sipa-page-hero d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="sipa-kicker mb-0">DIREKTORI PEMINJAM ARSIP</span>
            <span class="badge bg-white text-dark border rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.72rem;">
                <i class="bi bi-people-fill text-primary me-1"></i> {{ number_format($statsPeminjam['total']) }} Terdaftar
            </span>
            @if ($statsPeminjam['aktif'] > 0)
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.72rem;">
                    <i class="bi bi-clock-history me-1"></i> {{ $statsPeminjam['aktif'] }} Sedang Meminjam
                </span>
            @endif
        </div>
        <h3 class="fw-bold mb-1 text-slate-800">Direktori Data Peminjam</h3>
        <p class="mb-0 text-muted small">Kelola identitas peminjam Internal, SKPD, Kecamatan / Kelurahan, serta Eksternal beserta riwayat layanannya.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('peminjam.pdf', request()->only(['q', 'jenis'])) }}" class="btn btn-outline-danger btn-sm rounded-3 px-3 py-2 fw-semibold bg-white d-inline-flex align-items-center">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ekspor PDF
        </a>
        <button type="button" class="btn btn-navy btn-sm rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambahPeminjam">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Peminjam
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan & Filter Cepat -->
<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3">
        <a href="{{ route('peminjam.index', request()->only('q')) }}" class="peminjam-stat-card {{ !request()->filled('jenis') ? 'is-active' : '' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">Semua Kategori</div>
                    <div class="fs-4 fw-bold text-slate-800">{{ number_format($statsPeminjam['total']) }}</div>
                </div>
                <span class="peminjam-stat-icon bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></span>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('peminjam.index', array_merge(request()->only('q'), ['jenis' => 'Internal'])) }}" class="peminjam-stat-card {{ request('jenis') === 'Internal' ? 'is-active' : '' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">Internal Dinas</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($statsPeminjam['internal']) }}</div>
                </div>
                <span class="peminjam-stat-icon bg-primary-subtle text-primary"><i class="bi bi-building-check"></i></span>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('peminjam.index', array_merge(request()->only('q'), ['jenis' => 'SKPD'])) }}" class="peminjam-stat-card {{ request('jenis') === 'SKPD' ? 'is-active' : '' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">SKPD / Dinas Lain</div>
                    <div class="fs-4 fw-bold text-info">{{ number_format($statsPeminjam['skpd']) }}</div>
                </div>
                <span class="peminjam-stat-icon bg-info-subtle text-info"><i class="bi bi-bank2"></i></span>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('peminjam.index', array_merge(request()->only('q'), ['jenis' => 'Eksternal'])) }}" class="peminjam-stat-card {{ request('jenis') === 'Eksternal' ? 'is-active' : '' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">Eksternal &amp; Kecamatan</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($statsPeminjam['eksternal'] + $statsPeminjam['kecamatan']) }}</div>
                </div>
                <span class="peminjam-stat-icon bg-success-subtle text-success"><i class="bi bi-globe2"></i></span>
            </div>
        </a>
    </div>
</div>

<!-- Filter dan Pencarian -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <form action="{{ route('peminjam.index') }}" method="GET" class="row g-2 align-items-center mb-2">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, NIP/NIK, instansi, HP, atau jaminan...">
                </div>
            </div>
            <div class="col-7 col-md-4 col-lg-3">
                <select name="jenis" class="form-select form-select-sm">
                    <option value="">-- Semua Jenis Peminjam --</option>
                    @foreach (\App\Models\Peminjam::JENIS_LABELS as $val => $lbl)
                        <option value="{{ $val }}" {{ request('jenis') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-5 col-md-2 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-funnel-fill me-1"></i> Cari
                </button>
                @if(request()->hasAny(['q', 'jenis']) && (request('q') || request('jenis')))
                    <a href="{{ route('peminjam.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Chip Kategori Cepat -->
        <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
            <span class="text-muted small fw-semibold me-1">Kategori:</span>
            <a href="{{ route('peminjam.index', request()->only('q')) }}" class="chip-filter {{ !request()->filled('jenis') ? 'active' : '' }}">Semua</a>
            @foreach (\App\Models\Peminjam::JENIS_LABELS as $val => $lbl)
                <a href="{{ route('peminjam.index', array_merge(request()->only('q'), ['jenis' => $val])) }}" class="chip-filter {{ request('jenis') === $val ? 'active' : '' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Tabel Data Peminjam -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-3" style="width: 48px;">No</th>
                    <th>Nama Peminjam</th>
                    <th>Kategori</th>
                    <th>NIP / NIK</th>
                    <th>Instansi / Unit Kerja</th>
                    <th>Kontak (WA / Email)</th>
                    <th>Jaminan</th>
                    <th class="text-center">Riwayat Pinjam</th>
                    <th class="text-end pe-3" style="width: 165px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjamList as $idx => $p)
                    @php
                        $parts = preg_split('/\s+/', trim($p->nama));
                        $initials = strtoupper(mb_substr($parts[0] ?? 'P', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
                        $avatarBg = match ($p->jenis) {
                            'Internal' => 'bg-primary-subtle text-primary',
                            'SKPD' => 'bg-info-subtle text-info-emphasis',
                            'Kecamatan / Kelurahan' => 'bg-warning-subtle text-warning-emphasis',
                            default => 'bg-success-subtle text-success',
                        };
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted small">{{ $peminjamList->firstItem() + $idx }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-initials {{ $avatarBg }}">{{ $initials }}</span>
                                <div>
                                    <a href="{{ route('peminjam.show', $p) }}" class="fw-bold text-decoration-none text-dark d-block">
                                        {{ $p->nama }}
                                    </a>
                                    <div class="text-muted small">{{ $p->jabatan ?: '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $p->jenisBadgeClass() }} border rounded-pill px-2 py-1">
                                {{ $p->jenisLabel() }}
                            </span>
                        </td>
                        <td class="small text-secondary">{{ $p->nip_nik ?: '-' }}</td>
                        <td class="small">
                            <span class="fw-semibold text-dark">{{ $p->instansi_unit }}</span>
                            @if($p->alamat)
                                <div class="text-muted small text-truncate" style="max-width: 200px;" title="{{ $p->alamat }}">{{ $p->alamat }}</div>
                            @endif
                        </td>
                        <td class="small">
                            <div>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->no_hp) }}" target="_blank" class="text-decoration-none text-dark" title="Hubungi via WhatsApp">
                                    <i class="bi bi-whatsapp text-success me-1"></i>{{ $p->no_hp }}
                                </a>
                            </div>
                            @if($p->email)
                                <div class="text-muted">
                                    <a href="mailto:{{ $p->email }}" class="text-decoration-none text-muted">
                                        <i class="bi bi-envelope text-muted me-1"></i>{{ $p->email }}
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td class="small">
                            @if($p->jaminan)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-card-heading me-1"></i> {{ $p->jaminan }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                {{ $p->surat_peminjaman_count }} surat
                            </span>
                            @if ($p->pinjaman_aktif_count > 0)
                                <div class="mt-1">
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill" style="font-size: 0.68rem;">
                                        {{ $p->pinjaman_aktif_count }} aktif
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="action-btn-group">
                                <a href="{{ route('peminjam.show', $p) }}" class="btn-action btn-action-info" title="Profil &amp; Riwayat">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <a href="{{ route('peminjaman.create', ['peminjam_id' => $p->id]) }}" class="btn-action btn-action-primary" title="Buat Peminjaman Baru">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                                <button type="button" class="btn-action btn-action-warning" data-bs-toggle="modal" data-bs-target="#modalEditPeminjam{{ $p->id }}" title="Sunting Data">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('peminjam.destroy', $p) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data peminjam ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-danger" title="Hapus Peminjam">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-people display-6 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada data peminjam yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($peminjamList->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $peminjamList->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Modal Edit Peminjam (di luar table) -->
@foreach ($peminjamList as $p)
    @php
        $isCustomJaminan = $p->jaminan && !in_array($p->jaminan, $opsiStandarJaminan);
    @endphp
    <div class="modal fade modal-peminjam-edit" id="modalEditPeminjam{{ $p->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('peminjam.update', $p) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold fs-6"><i class="bi bi-pencil-square text-warning me-2"></i>Sunting Peminjam</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jenis Peminjam <span class="text-danger">*</span></label>
                            <select name="jenis" class="form-select form-select-sm select-jenis-peminjam" required>
                                @foreach (\App\Models\Peminjam::JENIS_LABELS as $val => $lbl)
                                    <option value="{{ $val }}" {{ $p->jenis === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control form-control-sm" value="{{ $p->nama }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">NIP / NIK</label>
                                <input type="text" name="nip_nik" class="form-control form-control-sm" value="{{ $p->nip_nik }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Jabatan</label>
                                <input type="text" name="jabatan" class="form-control form-control-sm" value="{{ $p->jabatan }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Instansi / Unit Kerja <span class="text-danger">*</span></label>
                            <input type="text" name="instansi_unit" class="form-control form-control-sm" value="{{ $p->instansi_unit }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="no_hp" class="form-control form-control-sm" value="{{ $p->no_hp }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="nama@gmail.com" value="{{ $p->email }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jaminan (KTP / Surat / Lainnya)</label>
                            <select name="jaminan_opsi" class="form-select form-select-sm select-jaminan-opsi">
                                <option value="">-- Pilih Jaminan --</option>
                                @foreach($opsiStandarJaminan as $opsi)
                                    <option value="{{ $opsi }}" {{ $p->jaminan === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                @endforeach
                                <option value="Lainnya" {{ $isCustomJaminan ? 'selected' : '' }}>Lainnya (Sebutkan)</option>
                            </select>
                            <input type="text" name="jaminan_lainnya" class="form-control form-control-sm mt-2 input-jaminan-lainnya {{ $isCustomJaminan ? '' : 'd-none' }}" placeholder="Tulis keterangan jaminan..." value="{{ $isCustomJaminan ? $p->jaminan : '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alamat Lengkap <span class="text-danger d-none alamat-wajib-star">*</span></label>
                            <textarea name="alamat" rows="2" class="form-control form-control-sm input-alamat-peminjam">{{ $p->alamat }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Modal Tambah Peminjam -->
<div class="modal fade" id="modalTambahPeminjam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('peminjam.store') }}" method="POST" id="formTambahPeminjam" autocomplete="off">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6"><i class="bi bi-person-plus-fill me-2"></i>Tambah Peminjam Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jenis Peminjam <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select form-select-sm select-jenis-peminjam" required>
                            @foreach (\App\Models\Peminjam::JENIS_LABELS as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control form-control-sm" placeholder="Contoh: Budi Santoso, S.AP" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">NIP / NIK</label>
                            <input type="text" name="nip_nik" class="form-control form-control-sm" placeholder="Nomor identitas">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control form-control-sm" placeholder="Contoh: Arsiparis Ahli Muda">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Instansi / Unit Kerja <span class="text-danger">*</span></label>
                        <input type="text" name="instansi_unit" class="form-control form-control-sm" placeholder="Contoh: Bidang Pengelolaan Arsip" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Nomor HP / WA <span class="text-danger">*</span></label>
                            <input type="text" name="no_hp" class="form-control form-control-sm" placeholder="0812xxxxxxxx" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="nama@gmail.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jaminan (KTP / Surat / Lainnya)</label>
                        <select name="jaminan_opsi" class="form-select form-select-sm select-jaminan-opsi">
                            <option value="">-- Pilih Jaminan --</option>
                            @foreach($opsiStandarJaminan as $opsi)
                                <option value="{{ $opsi }}">{{ $opsi }}</option>
                            @endforeach
                            <option value="Lainnya">Lainnya (Sebutkan)</option>
                        </select>
                        <input type="text" name="jaminan_lainnya" class="form-control form-control-sm mt-2 d-none input-jaminan-lainnya" placeholder="Tulis jaminan yang ditinggalkan...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat Lengkap <span class="text-danger d-none alamat-wajib-star">*</span></label>
                        <textarea name="alamat" rows="2" class="form-control form-control-sm input-alamat-peminjam" placeholder="Opsional untuk Internal/SKPD/Kecamatan, wajib jika kategori Eksternal..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill">Simpan Peminjam</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function syncJaminanInput(selectEl) {
        const wrapper = selectEl.closest('form');
        const customInput = wrapper ? wrapper.querySelector('.input-jaminan-lainnya') : null;
        if (!customInput) return;
        const isCustom = selectEl.value === 'Lainnya';
        customInput.classList.toggle('d-none', !isCustom);
        if (!isCustom) customInput.value = '';
    }

    document.querySelectorAll('.select-jaminan-opsi').forEach((sel) => {
        sel.addEventListener('change', () => syncJaminanInput(sel));
    });

    function syncAlamatRequirement(selectEl) {
        const wrapper = selectEl.closest('form');
        if (!wrapper) return;
        const alamat = wrapper.querySelector('[name="alamat"]');
        const star = wrapper.querySelector('.alamat-wajib-star');
        const isEks = selectEl.value === 'Eksternal';
        if (alamat) alamat.required = isEks;
        if (star) star.classList.toggle('d-none', !isEks);
    }

    document.querySelectorAll('.select-jenis-peminjam').forEach((sel) => {
        syncAlamatRequirement(sel);
        sel.addEventListener('change', () => syncAlamatRequirement(sel));
    });

    const modalTambah = document.getElementById('modalTambahPeminjam');
    if (modalTambah) {
        modalTambah.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('formTambahPeminjam');
            if (form) {
                form.reset();
                const sel = form.querySelector('.select-jaminan-opsi');
                if (sel) syncJaminanInput(sel);
                const selJenis = form.querySelector('.select-jenis-peminjam');
                if (selJenis) syncAlamatRequirement(selJenis);
            }
        });
    }

    document.querySelectorAll('.modal-peminjam-edit').forEach((modalEl) => {
        modalEl.addEventListener('hidden.bs.modal', () => {
            const form = modalEl.querySelector('form');
            if (form) {
                form.reset();
                const sel = form.querySelector('.select-jaminan-opsi');
                if (sel) syncJaminanInput(sel);
                const selJenis = form.querySelector('.select-jenis-peminjam');
                if (selJenis) syncAlamatRequirement(selJenis);
            }
        });
    });
</script>
@endpush
