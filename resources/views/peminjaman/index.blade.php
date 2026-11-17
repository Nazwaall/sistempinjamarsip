@extends('layouts.app')

@section('title', 'Daftar Peminjaman Arsip')

@section('content')
<div class="row g-3">
    <!-- Hero Header & Tombol -->
    <div class="col-12">
        <div class="sipa-page-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="sipa-kicker">SIRKULASI &amp; PERMOHONAN LAYANAN</div>
                <h3 class="fw-bold mb-1 text-slate-800">Daftar Peminjaman Arsip</h3>
                <p class="text-muted small mb-0">Kelola dan pantau seluruh permohonan surat peminjaman fisik, salinan digital, serta layanan informasi.</p>
            </div>
            <a href="{{ route('peminjaman.create') }}" class="btn btn-navy btn-sm rounded-3 px-4 py-2 shadow-sm d-inline-flex align-items-center flex-shrink-0">
                <i class="bi bi-plus-lg me-1"></i> Buat Peminjaman Baru
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="col-12">
        <div class="sipa-filter-card">
            <form action="{{ route('peminjaman.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Cari nomor surat, nama peminjam, instansi..." value="{{ request('q') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="Menunggu Persetujuan" {{ request('status') == 'Menunggu Persetujuan' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif (Berjalan)</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="bentuk_layanan" class="form-select form-select-sm">
                        <option value="">Semua Bentuk Layanan</option>
                        @foreach (\App\Models\SuratPeminjaman::BENTUK_LAYANAN_LIST as $layanan)
                            <option value="{{ $layanan }}" {{ request('bentuk_layanan') == $layanan ? 'selected' : '' }}>{{ $layanan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->hasAny(['q', 'status', 'bentuk_layanan']))
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Peminjaman -->
    <div class="col-12">
        <div class="card border-0 rounded-3 shadow-sm overflow-hidden">
            @if ($peminjamanList->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-secondary mb-2 d-block"></i>
                    <h6 class="text-secondary fw-semibold">Belum Ada Catatan Peminjaman</h6>
                    <p class="text-muted small mb-3">Tidak ada data peminjaman yang cocok dengan filter.</p>
                    <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">Buat Peminjaman Sekarang</a>
                </div>
            @else
                <!-- Tabel Desktop -->
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Nomor Surat</th>
                                <th>Peminjam</th>
                                <th>Jaminan</th>
                                <th>Jumlah Arsip</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($peminjamanList as $p)
                                @php
                                    $isTerlambat = $p->isTerlambat();
                                    $stInfo = $p->statusBadgeInfo();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $p->nomor_surat }}</div>
                                        <small class="text-muted d-block">Bidang: {{ $p->unitPengolah->nama }}</small>
                                        <span class="badge {{ $p->layananBadgeClass() }} mt-1" style="font-size: 0.7rem;">{{ $p->bentuk_layanan ?: 'Fisik' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-slate-800">{{ $p->peminjam->nama }}</div>
                                        <small class="text-muted d-block">{{ $p->peminjam->instansi_unit }} &bull; {{ $p->peminjam->no_hp }}</small>
                                        <span class="badge {{ $p->peminjam->jenisBadgeClass() }}" style="font-size: 0.68rem;">{{ $p->peminjam->jenisLabel() }}</span>
                                    </td>
                                    <td>
                                        @php $jamTampil = $p->jaminan ?: $p->peminjam->jaminan; @endphp
                                        @if($jamTampil)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill">{{ $jamTampil }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $p->items->count() }} Berkas</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                                    <td>
                                        <span class="{{ $isTerlambat ? 'text-danger fw-bold' : '' }}">
                                            {{ \Carbon\Carbon::parse($p->tanggal_kembali)->translatedFormat('d F Y') }}
                                        </span>
                                        @if ($p->status === 'Janjian' && $p->rencana_ambil_pada)
                                            <div class="text-info-emphasis small fw-semibold" style="font-size: 0.73rem;">
                                                <i class="bi bi-calendar2-check me-1"></i>Ambil: {{ $p->rencana_ambil_pada->format('d/m H:i') }}
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
                                            <a href="{{ route('peminjaman.show', $p) }}" class="btn-action-pill btn-action-primary" title="Detail Surat Peminjaman">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                            <a href="{{ route('peminjaman.pdf', $p) }}" class="btn-action btn-action-danger" title="Unduh Surat PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Kartu Mobile (FR-40) -->
                <div class="d-lg-none p-3">
                    @foreach ($peminjamanList as $p)
                        @php $stInfo = $p->statusBadgeInfo(); @endphp
                        <div class="card border rounded-3 p-3 mb-2 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-primary small">{{ $p->nomor_surat }}</span>
                                <span class="badge {{ $stInfo['class'] }} rounded-pill">{{ $stInfo['label'] }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-1">
                                <span class="badge {{ $p->layananBadgeClass() }}" style="font-size: 0.7rem;">{{ $p->bentuk_layanan ?: 'Fisik' }}</span>
                                <span class="badge {{ $p->peminjam->jenisBadgeClass() }}" style="font-size: 0.7rem;">{{ $p->peminjam->jenisLabel() }}</span>
                            </div>
                            <div class="fw-semibold text-slate-800">{{ $p->peminjam->nama }}</div>
                            <div class="text-muted small mb-2">
                                Bidang: {{ $p->unitPengolah->nama }} &bull; {{ $p->peminjam->instansi_unit }} &bull; {{ $p->items->count() }} Berkas &bull; Kembali: {{ \Carbon\Carbon::parse($p->tanggal_kembali)->translatedFormat('d F Y') }}
                                @if ($p->status === 'Janjian' && $p->rencana_ambil_pada)
                                    <br><span class="text-info-emphasis fw-semibold">Jadwal Janjian: {{ $p->rencana_ambil_pada->translatedFormat('d M Y H:i') }}</span>
                                @endif
                                @if($p->jaminan ?: $p->peminjam->jaminan)
                                    <br><span class="text-info-emphasis">Jaminan: {{ $p->jaminan ?: $p->peminjam->jaminan }}</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                <a href="{{ route('peminjaman.show', $p) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                                <a href="{{ route('peminjaman.pdf', $p) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Paginasi -->
                <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Menampilkan {{ $peminjamanList->firstItem() }} - {{ $peminjamanList->lastItem() }} dari {{ $peminjamanList->total() }} surat
                    </small>
                    {{ $peminjamanList->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
