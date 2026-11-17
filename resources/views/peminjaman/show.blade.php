@extends('layouts.app')

@section('title', 'Detail Surat Peminjaman - ' . $peminjaman->nomor_surat)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <!-- Header Aksi -->
        <div class="sipa-page-hero d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
            <div>
                <a href="{{ route('peminjaman.index') }}" class="text-decoration-none small text-muted">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Peminjaman
                </a>
                <h3 class="fw-bold mb-0 text-slate-800 mt-1">{{ $peminjaman->nomor_surat }}</h3>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if (in_array($peminjaman->status, ['Aktif', 'Terlambat']) && $peminjaman->diperpanjang_kali < 2)
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3 bg-white touch-target" data-bs-toggle="modal" data-bs-target="#modalPerpanjang">
                        <i class="bi bi-calendar-plus me-1"></i> Perpanjang
                    </button>
                @endif
                <a href="{{ route('peminjaman.pdf', $peminjaman) }}" class="btn btn-navy btn-sm rounded-3 px-3 touch-target">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh Surat PDF
                </a>
            </div>
        </div>

        <!-- Kartu Utama -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <!-- Status Bar -->
                @php $stInfo = $peminjaman->statusBadgeInfo(); @endphp
                <div class="p-3 rounded-3 mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2
                    @if ($peminjaman->status === 'Menunggu Persetujuan') bg-warning-subtle text-warning-emphasis border border-warning-subtle
                    @elseif ($peminjaman->status === 'Janjian') bg-info-subtle text-info-emphasis border border-info-subtle
                    @elseif ($peminjaman->status === 'Aktif' || $peminjaman->status === 'Terlambat') bg-primary-subtle text-primary-emphasis border border-primary-subtle
                    @elseif ($peminjaman->status === 'Selesai') bg-success-subtle text-success-emphasis border border-success-subtle
                    @elseif ($peminjaman->status === 'Ditolak') bg-danger-subtle text-danger-emphasis border border-danger-subtle
                    @else bg-secondary-subtle text-secondary border @endif">
                    <div class="d-flex align-items-center">
                        <i class="bi {{ $stInfo['icon'] }} fs-4 me-2"></i>
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <strong>Status Saat Ini: {{ $stInfo['label'] }}</strong>
                                <span class="badge {{ $peminjaman->layananBadgeClass() }}">{{ $peminjaman->bentuk_layanan ?: 'Fisik' }}</span>
                            </div>
                            <div class="small mt-1">
                                @if ($peminjaman->status === 'Menunggu Persetujuan')
                                    Surat menunggu verifikasi dan persetujuan <strong>Super Admin / Kepala Bidang (Kabid)</strong>.
                                    @if ($peminjaman->is_janjian && $peminjaman->rencana_ambil_pada)
                                        (Mode Janjian: {{ $peminjaman->rencana_ambil_pada->translatedFormat('d F Y, H:i') }} WIB)
                                    @endif
                                @elseif ($peminjaman->status === 'Janjian')
                                    Permohonan telah disetujui dan dijadwalkan untuk <strong>{{ $peminjaman->rencana_ambil_pada ? $peminjaman->rencana_ambil_pada->translatedFormat('d F Y, H:i') . ' WIB' : 'waktu yang disepakati' }}</strong>. Berkas fisik belum dikunci sebelum Serah Terima.
                                    @if ($peminjaman->catatan_janjian)
                                        <div class="fst-italic mt-1">Catatan: "{{ $peminjaman->catatan_janjian }}"</div>
                                    @endif
                                @elseif ($peminjaman->status === 'Aktif' || $peminjaman->status === 'Terlambat')
                                    {{ $peminjaman->isFisik() ? 'Arsip fisik resmi sedang dipinjam.' : 'Layanan non-fisik aktif (ketersediaan fisik di depo tetap Tersedia).' }} Jatuh tempo: <strong>{{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->translatedFormat('d F Y') }}</strong>.
                                @elseif ($peminjaman->status === 'Selesai')
                                    {{ $peminjaman->isFisik() ? 'Seluruh berkas arsip fisik telah dikembalikan dan diselesaikan.' : 'Layanan non-fisik telah selesai dicatat.' }}
                                @elseif ($peminjaman->status === 'Ditolak')
                                    Peminjaman ditolak: <em>{{ $peminjaman->alasan_penolakan }}</em>
                                @elseif ($peminjaman->status === 'Dibatalkan')
                                    Jadwal / permohonan dibatalkan: <em>{{ $peminjaman->alasan_penolakan ?: 'Dibatalkan sebelum serah terima.' }}</em>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Persetujuan Super Admin / Kabid & Aksi Janjian -->
                    <div class="d-flex flex-wrap gap-2">
                        @if ($peminjaman->status === 'Menunggu Persetujuan' && Auth::user()->isSuperAdmin())
                            <form action="{{ route('peminjaman.setujui', $peminjaman) }}" method="POST" onsubmit="return confirm('{{ $peminjaman->isFisik() ? 'Setujui peminjaman ini dan kunci berkas fisik sebagai Dipinjam?' : 'Setujui layanan non-fisik ini?' }}')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold">
                                    <i class="bi bi-check-lg me-1"></i> Setujui Peminjaman
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTolak">
                                <i class="bi bi-x-lg me-1"></i> Tolak
                            </button>
                        @endif

                        @if ($peminjaman->status === 'Janjian')
                            <form action="{{ route('peminjaman.serah-terima', $peminjaman) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa peminjam hadir dan berkas/layanan resmi diserahkan sekarang?')">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                                    <i class="bi bi-box-arrow-right me-1"></i> Serah Terimakan
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBatalJanjian">
                                <i class="bi bi-calendar-x me-1"></i> Batalkan Janjian
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Informasi Ringkasan Peminjaman -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3"><i class="bi bi-person-badge text-primary me-1"></i> Identitas Peminjam</h6>
                        <table class="table table-sm table-borderless small mb-0">
                            <tr>
                                <td class="text-muted" style="width: 140px;">Nama Lengkap</td>
                                <td class="fw-semibold">: {{ $peminjaman->peminjam->nama }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Peminjam</td>
                                <td>: <span class="badge {{ $peminjaman->peminjam->jenisBadgeClass() }}">{{ $peminjaman->peminjam->jenisLabel() }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Instansi / Unit Kerja</td>
                                <td>: {{ $peminjaman->peminjam->instansi_unit }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jabatan / NIP / NIK</td>
                                <td>: {{ $peminjaman->peminjam->jabatan ?: '-' }} ({{ $peminjaman->peminjam->nip_nik ?: '-' }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. WhatsApp / HP</td>
                                <td>: {{ $peminjaman->peminjam->no_hp }}</td>
                            </tr>
                            @if ($peminjaman->peminjam->alamat)
                                <tr>
                                    <td class="text-muted">Alamat</td>
                                    <td>: {{ $peminjaman->peminjam->alamat }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Jaminan</td>
                                <td>: <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $peminjaman->jaminan ?: ($peminjaman->peminjam->jaminan ?: '-') }}</span></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3"><i class="bi bi-calendar-range text-primary me-1"></i> Rentang Waktu & Keperluan</h6>
                        <table class="table table-sm table-borderless small mb-0">
                            <tr>
                                <td class="text-muted" style="width: 150px;">Bidang Pembuat Surat</td>
                                <td class="fw-semibold">: {{ $peminjaman->unitPengolah->nama }} ({{ $peminjaman->unitPengolah->kode }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Bentuk Layanan</td>
                                <td>: <span class="badge {{ $peminjaman->layananBadgeClass() }}">{{ $peminjaman->bentuk_layanan ?: 'Fisik' }}</span></td>
                            </tr>
                            @if ($peminjaman->is_janjian)
                                <tr>
                                    <td class="text-muted">Jadwal Janjian</td>
                                    <td class="fw-semibold text-info-emphasis">: {{ $peminjaman->rencana_ambil_pada ? $peminjaman->rencana_ambil_pada->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Tanggal Surat</td>
                                <td>: {{ \Carbon\Carbon::parse($peminjaman->tanggal_surat)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Masa Peminjaman</td>
                                <td class="fw-semibold">: {{ \Carbon\Carbon::parse($peminjaman->tanggal_mulai)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Durasi Pinjam</td>
                                <td>: <span class="badge bg-primary rounded-pill">{{ $peminjaman->lama_pinjam_hari }} Hari</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Petugas Layanan</td>
                                <td>
                                    : <strong>{{ $peminjaman->namaPetugasLayanan() }}</strong>
                                    @if ($peminjaman->nipPetugasLayanan())
                                        <span class="text-muted">(NIP. {{ $peminjaman->nipPetugasLayanan() }})</span>
                                    @endif
                                    @if ($peminjaman->bidangPetugasLayanan())
                                        <div class="text-muted small ps-2">{{ $peminjaman->bidangPetugasLayanan() }}</div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Disetujui Oleh (Kabid)</td>
                                <td>: {{ $peminjaman->disetujuiOleh ? $peminjaman->disetujuiOleh->name : '(Belum disetujui)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Maksud / Keperluan</td>
                                <td class="fst-italic">: "{{ $peminjaman->maksud_keperluan }}"</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Daftar Berkas yang Dilayani -->
                <div class="mb-4">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3"><i class="bi bi-folder-check text-primary me-1"></i> Daftar Berkas Arsip ({{ $peminjaman->items->count() }} Berkas)</h6>
                    <div class="table-responsive bg-light rounded-3 border">
                        <table class="table table-sm align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">No.</th>
                                    <th>Kode Berkas</th>
                                    <th>Uraian Berkas Arsip</th>
                                    <th>Bidang / Pencipta Asal</th>
                                    <th>Tahun</th>
                                    <th>Lokasi / Depo</th>
                                    <th>Kondisi Keluar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peminjaman->items as $idx => $item)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $item->arsip->kode_internal }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800">{{ $item->arsip->uraian_berkas }}</div>
                                            @if ($item->arsip->uraian_informasi)
                                                <small class="text-muted">{{ $item->arsip->uraian_informasi }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->arsip->unit_pengolah_asal ?: ($item->arsip->unitPengolah?->nama ?? '-') }}</td>
                                        <td>{{ $item->arsip->tahun }}</td>
                                        <td>{{ $item->arsip->lokasi_simpan ?: '-' }}</td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $item->kondisi_keluar }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat Perpanjangan jika ada -->
                @if ($peminjaman->perpanjangan->isNotEmpty())
                    <div class="mb-4">
                        <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3"><i class="bi bi-clock-history text-primary me-1"></i> Riwayat Perpanjangan Peminjaman</h6>
                        <div class="table-responsive bg-light rounded-3 border">
                            <table class="table table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Tgl Kembali Lama</th>
                                        <th>Tgl Kembali Baru</th>
                                        <th>Alasan Perpanjangan</th>
                                        <th>Disetujui Oleh</th>
                                        <th>Tanggal Diajukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($peminjaman->perpanjangan as $idx => $p)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $p->tanggal_kembali_lama->translatedFormat('d F Y') }}</td>
                                            <td class="fw-bold text-success">{{ $p->tanggal_kembali_baru->translatedFormat('d F Y') }}</td>
                                            <td>{{ $p->alasan }}</td>
                                            <td>{{ $p->disetujuiOleh?->name ?? '-' }}</td>
                                            <td class="text-muted">{{ $p->created_at?->translatedFormat('d F Y, H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Bukti Sah: Tanda Tangan & Foto Serah Terima -->
                <div class="row g-3 border-top pt-3">
                    <!-- Tanda Tangan -->
                    <div class="col-12 col-md-6">
                        <div class="small fw-bold text-secondary mb-2"><i class="bi bi-pen me-1"></i> Bukti Tanda Tangan Peminjam:</div>
                        @if ($peminjaman->metode_ttd === 'Digital' && $peminjaman->ttd_peminjam_path)
                            <div class="border rounded-3 p-3 bg-light text-center" style="max-width: 280px;">
                                <img src="{{ asset('storage/' . $peminjaman->ttd_peminjam_path) }}" alt="Tanda Tangan Digital" class="img-fluid" style="max-height: 100px;">
                                <div class="border-top pt-1 mt-2 small fw-semibold text-slate-800">{{ $peminjaman->peminjam->nama }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Ditandatangani digital di layar</small>
                            </div>
                        @elseif ($peminjaman->metode_ttd === 'Manual' && $peminjaman->surat_ttd_path)
                            <div class="border rounded-3 p-3 bg-light" style="max-width: 280px;">
                                <a href="{{ asset('storage/' . $peminjaman->surat_ttd_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark-check me-1"></i> Buka Foto Surat Bertanda Tangan
                                </a>
                            </div>
                        @else
                            <div class="p-3 bg-light border rounded text-muted small">Belum ada tanda tangan.</div>
                        @endif
                    </div>

                    <!-- Foto Bukti -->
                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small fw-bold text-secondary"><i class="bi bi-camera me-1"></i> Bukti Foto (KTP / Surat / Serah Terima):</div>
                            <a href="{{ route('pemindai.index', ['surat_id' => $peminjaman->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;">
                                <i class="bi bi-bounding-box-circles me-1"></i> Pindai / Tambah Bukti
                            </a>
                        </div>
                        @if ($peminjaman->buktiFoto->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($peminjaman->buktiFoto as $foto)
                                    <a href="{{ asset('storage/' . $foto->file_path) }}" target="_blank" class="border rounded-3 p-1 bg-light text-center text-decoration-none" style="width: 145px;" title="Klik untuk memperbesar">
                                        <img src="{{ asset('storage/' . $foto->file_path) }}" alt="Foto Bukti" class="img-fluid rounded" style="height: 100px; object-fit: cover;">
                                        <div class="small text-dark fw-semibold mt-1" style="font-size: 0.7rem;">{{ $foto->jenis }}</div>
                                        <div class="text-muted" style="font-size: 0.65rem;">{{ $foto->sumber }}</div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 bg-light border rounded text-muted small">Belum ada bukti foto KTP, surat, atau serah terima.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tolak Peminjaman -->
<div class="modal fade" id="modalTolak" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('peminjaman.tolak', $peminjaman) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tolak Peminjaman Arsip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_penolakan" class="form-control" rows="3" placeholder="Tuliskan alasan penolakan berkas arsip..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill fw-semibold">Tolak Permohonan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Batalkan Janjian -->
<div class="modal fade" id="modalBatalJanjian" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('peminjaman.batal-janjian', $peminjaman) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Batalkan Jadwal Janjian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alasan Pembatalan</label>
                        <textarea name="alasan_batal" class="form-control" rows="3" placeholder="Contoh: Peminjam tidak hadir atau permohonan dibatalkan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger rounded-pill fw-semibold">Batalkan Janjian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Perpanjang Peminjaman (FR-20) -->
<div class="modal fade" id="modalPerpanjang" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('peminjaman.perpanjang', $peminjaman) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6"><i class="bi bi-calendar-plus me-2"></i>Perpanjangan Masa Peminjaman</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Tanggal kembali saat ini: <strong>{{ $peminjaman->tanggal_kembali->translatedFormat('d F Y') }}</strong>.
                        Perpanjangan maksimal {{ $peminjaman->unitPengolah->lama_pinjam_maks ?: 7 }} hari dari tanggal tersebut.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal Kembali Baru <span class="text-danger">*</span></label>
                        @php
                            $minDate = $peminjaman->tanggal_kembali->copy()->addDay()->format('Y-m-d');
                            $maxDate = $peminjaman->tanggal_kembali->copy()->addDays($peminjaman->unitPengolah->lama_pinjam_maks ?: 7)->format('Y-m-d');
                        @endphp
                        <input type="date" name="tanggal_kembali_baru" min="{{ $minDate }}" max="{{ $maxDate }}" value="{{ $maxDate }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alasan Perpanjangan <span class="text-danger">*</span></label>
                        <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Proses verifikasi berkas oleh peminjam masih berlangsung..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold">Simpan Perpanjangan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
