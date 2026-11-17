@extends('layouts.app')

@section('title', 'Detail Arsip: ' . $arsip->kode_internal)

@section('content')
<div class="sipa-page-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
    <div>
        <a href="{{ route('arsip.index') }}" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Arsip
        </a>
        <h3 class="fw-bold mb-0 mt-1 text-slate-800"><i class="bi bi-folder-fill text-primary me-2"></i>{{ $arsip->kode_internal }}</h3>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3 bg-white shadow-sm" id="btnNilaiKondisiCnn">
            <i class="bi bi-cpu me-1"></i> Nilai Kondisi &amp; Retensi (CNN)
        </button>
        @if($arsip->status_ketersediaan === 'Tersedia')
            <a href="{{ route('peminjaman.create') }}?arsip_id={{ $arsip->id }}" class="btn btn-navy btn-sm rounded-3 px-3 shadow-sm">
                <i class="bi bi-pencil-square me-1"></i> Pinjam Berkas Ini
            </a>
        @endif
        <a href="{{ route('arsip.edit', $arsip) }}" class="btn btn-outline-warning btn-sm rounded-3 px-3 bg-white">
            <i class="bi bi-pencil me-1"></i> Sunting Data
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- Informasi Utama Arsip -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-info-circle-fill text-secondary me-2"></i>Informasi Berkas Arsip</h6>
                @if($arsip->status_ketersediaan === 'Tersedia')
                    <span class="badge bg-success rounded-pill px-3 py-1">Tersedia</span>
                @elseif($arsip->status_ketersediaan === 'Dipinjam')
                    <span class="badge bg-danger rounded-pill px-3 py-1">Sedang Dipinjam</span>
                @elseif($arsip->status_ketersediaan === 'Rusak')
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Kondisi Rusak</span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3 py-1">{{ $arsip->status_ketersediaan }}</span>
                @endif
            </div>
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-2">{{ $arsip->uraian_berkas }}</h5>
                @if($arsip->uraian_informasi)
                    <p class="text-muted small mb-3">{{ $arsip->uraian_informasi }}</p>
                @endif

                <div class="table-responsive small">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 145px;">Bidang Pengelola</td>
                                <td class="fw-semibold">: {{ $arsip->unitPengolah?->nama }} ({{ $arsip->unitPengolah?->kode }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Pencipta / Asal</td>
                                <td class="fw-semibold">: {{ $arsip->unit_pengolah_asal ?: ($arsip->unitPengolah?->nama ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kode Klasifikasi</td>
                                <td class="fw-semibold">: {{ $arsip->kode_klasifikasi ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tahun & Bulan</td>
                                <td class="fw-semibold">: {{ $arsip->tahun }} {{ $arsip->bulan ? '(' . date('F', mktime(0, 0, 0, $arsip->bulan, 10)) . ')' : '' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Surat</td>
                                <td>: {{ $arsip->tanggal_surat ? $arsip->tanggal_surat->translatedFormat('d F Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Lokasi Simpan</td>
                                <td class="fw-semibold text-primary">: <i class="bi bi-geo-alt me-1"></i>{{ $arsip->lokasi_simpan ?: 'Belum ditentukan' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Keaslian</td>
                                <td>: {{ $arsip->tingkat_perkembangan ?: 'Asli' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Klasifikasi Keamanan</td>
                                <td>:
                                    <span class="badge {{ $arsip->klasifikasi_keamanan === 'Biasa/Terbuka' ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger' }}">
                                        {{ $arsip->klasifikasi_keamanan }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jadwal Retensi (JRA)</td>
                                <td>: Aktif {{ $arsip->jra_aktif ?? 0 }} th, Inaktif {{ $arsip->jra_inaktif ?? 0 }} th ({{ $arsip->jra_keterangan ?: 'Permanen' }})</td>
                            </tr>
                            @if($arsip->teks_label)
                                <tr>
                                    <td class="text-muted">Kata Kunci Label</td>
                                    <td>: <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $arsip->teks_label }}</span></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Evaluasi Kondisi Fisik & Retensi (CNN) -->
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-cpu text-primary me-2"></i>Kondisi Fisik &amp; Penilaian Retensi (CNN)</h6>
                @if($arsip->skor_integritas_terakhir !== null)
                    <span class="badge {{ $arsip->skor_integritas_terakhir >= 86 ? 'bg-success' : ($arsip->skor_integritas_terakhir >= 65 ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill px-3">
                        Integritas {{ $arsip->skor_integritas_terakhir }}%
                    </span>
                @endif
            </div>
            <div class="card-body small">
                @if($arsip->skor_integritas_terakhir !== null)
                    <div class="fw-semibold text-slate-800 mb-1">{{ $arsip->catatan_kondisi_terakhir }}</div>
                    <div class="text-muted mb-2">
                        Terakhir dinilai: {{ $arsip->terakhir_diperiksa_pada?->translatedFormat('d F Y, H:i') ?: '-' }}
                    </div>
                    <div class="alert {{ $arsip->skor_integritas_terakhir >= 86 ? 'alert-success' : 'alert-warning' }} py-2 px-3 mb-0">
                        @if($arsip->skor_integritas_terakhir >= 86)
                            Rekomendasi Retensi: Fisik baik, tetap pertahankan jadwal retensi ({{ $arsip->jra_keterangan ?: 'Permanen' }}).
                        @else
                            Rekomendasi Retensi: Segera lakukan konservasi/restorasi fisik dan alih media digital agar isi arsip tidak hilang.
                        @endif
                    </div>
                @else
                    <p class="text-muted mb-2">Belum pernah dilakukan penilaian kerusakan fisik berbasis CNN untuk berkas ini.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Riwayat Peminjaman Berkas Ini (FR-25) -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Peminjaman Berkas Fisik</h6>
                <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1">
                    {{ $arsip->suratPeminjamanItems->count() }} Kali Dipinjam
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="ps-3">Nomor Surat</th>
                            <th>Peminjam</th>
                            <th>Rentang Pinjam</th>
                            <th>Kondisi Pengembalian</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arsip->suratPeminjamanItems as $item)
                            @php $surat = $item->surat; @endphp
                            <tr>
                                <td class="ps-3">
                                    @if($surat)
                                        <a href="{{ route('peminjaman.show', $surat) }}" class="fw-bold text-decoration-none text-primary">
                                            {{ $surat->nomor_surat }}
                                        </a>
                                        <div class="text-muted small">{{ $surat->tanggal_surat?->translatedFormat('d F Y') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="small">
                                    <div class="fw-semibold">{{ $surat?->peminjam?->nama ?? '-' }}</div>
                                    <div class="text-muted small">{{ $surat?->peminjam?->instansi_unit }}</div>
                                </td>
                                <td class="small">
                                    <div>{{ $surat?->tanggal_mulai?->translatedFormat('d F Y') }} s/d {{ $surat?->tanggal_kembali?->translatedFormat('d F Y') }}</div>
                                    @if($surat?->status === 'Aktif')
                                        <span class="badge bg-warning text-dark">Sedang Berlangsung</span>
                                    @elseif($surat?->status === 'Selesai')
                                        <span class="badge bg-success">Selesai</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $surat?->status }}</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($item->tanggal_kembali_riil)
                                        <div class="fw-semibold text-success">
                                            <i class="bi bi-check-circle me-1"></i>{{ $item->kondisi_kembali ?: 'Baik' }}
                                            @if($item->skor_integritas !== null)
                                                <span class="badge {{ $item->skor_integritas >= 86 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis' }} ms-1">
                                                    {{ $item->skor_integritas }}%
                                                </span>
                                            @endif
                                        </div>
                                        </div>
                                        <div class="text-muted small">{{ $item->tanggal_kembali_riil->translatedFormat('d F Y, H:i') }}</div>
                                        @if($item->catatan)
                                            <div class="text-muted small fst-italic">"{{ $item->catatan }}"</div>
                                        @endif
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Belum Kembali</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if($surat)
                                        <div class="action-btn-group">
                                            <a href="{{ route('peminjaman.show', $surat) }}" class="btn-action btn-action-primary" title="Detail Surat">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-archive display-6 d-block mb-2 text-secondary"></i>
                                    Berkas arsip ini belum pernah dipinjam.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form id="formNilaiKondisiCnn" action="{{ route('arsip.nilai-kondisi', $arsip) }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="skor_integritas" id="arsipCnnSkor">
    <input type="hidden" name="ringkasan_kerusakan" id="arsipCnnRingkasan">
    <input type="hidden" name="kondisi_disarankan" id="arsipCnnKondisi">
    <input type="hidden" name="foto_kondisi_data" id="arsipCnnFoto">
</form>

@include('partials.modal-cnn-damage')

@push('scripts')
<script>
    document.getElementById('btnNilaiKondisiCnn')?.addEventListener('click', () => {
        window.openDamageAssessmentModal({
            subtitle: 'Penilaian degradasi fisik & kelayakan retensi untuk {{ $arsip->kode_internal }}',
            applyLabel: 'Simpan Penilaian Retensi (CNN)',
            onApply: (res) => {
                document.getElementById('arsipCnnSkor').value = res.integrityScore;
                document.getElementById('arsipCnnRingkasan').value = res.summaryText;
                document.getElementById('arsipCnnKondisi').value = res.kondisiSaran === 'Baik' ? 'Tersedia' : 'Rusak';
                document.getElementById('arsipCnnFoto').value = res.annotatedDataUrl;
                document.getElementById('formNilaiKondisiCnn').submit();
            },
        });
    });
</script>
@endpush
@endsection
