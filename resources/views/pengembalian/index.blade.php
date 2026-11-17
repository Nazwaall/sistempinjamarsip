@extends('layouts.app')

@section('title', 'Pengembalian Berkas Arsip')

@section('content')
<div class="row g-3">
    <!-- Hero Header Halaman -->
    <div class="col-12">
        <div class="sipa-page-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="sipa-kicker">VERIFIKASI PENGEMBALIAN BERKAS</div>
                <h3 class="fw-bold mb-1 text-slate-800">Pengembalian &amp; Penyelesaian Layanan</h3>
                <p class="text-muted small mb-0">Proses pengembalian berkas fisik (dengan deteksi kondisi CNN) dan konfirmasi selesai layanan non-fisik.</p>
            </div>
            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 py-2 flex-shrink-0">
                <i class="bi bi-journal-text me-1"></i> Lihat Semua Peminjaman
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian Cepat -->
    <div class="col-12">
        <div class="sipa-filter-card">
            <form action="{{ route('pengembalian.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Ketik nomor surat, nama peminjam, atau kode berkas arsip..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1" style="background-color: #1e3a8a;">
                        <i class="bi bi-search me-1"></i> Cari Surat
                    </button>
                    @if (request('q'))
                        <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Surat Aktif yang Menunggu Pengembalian -->
    <div class="col-12">
        @if ($peminjamanAktif->isEmpty())
            <div class="card border-0 rounded-3 shadow-sm p-5 text-center">
                <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                <h6 class="fw-semibold text-slate-800">Tidak Ada Peminjaman Aktif yang Menunggu Pengembalian</h6>
                <p class="text-muted small mb-0">Semua berkas arsip yang dipinjam telah kembali ke unit pengolah.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach ($peminjamanAktif as $p)
                    <div class="col-12">
                        <div class="card border-0 rounded-3 shadow-sm overflow-hidden">
                            <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                                <div>
                                    <span class="fw-bold text-primary fs-6">{{ $p->nomor_surat }}</span>
                                    <span class="badge {{ $p->layananBadgeClass() }} ms-2 rounded-pill">{{ $p->bentuk_layanan ?: 'Fisik' }}</span>
                                    <span class="badge bg-primary-subtle text-primary ms-1 rounded-pill">{{ $p->isFisik() ? 'Dipinjam Fisik' : 'Layanan Berjalan' }}</span>
                                    <div class="small text-muted mt-1">
                                        Bidang: <strong>{{ $p->unitPengolah?->nama ?? '-' }}</strong> &bull; Peminjam: <strong>{{ $p->peminjam->nama }}</strong> ({{ $p->peminjam->jenisLabel() }}) &bull; {{ $p->peminjam->no_hp }}
                                    </div>
                                </div>
                                <div class="text-sm-end">
                                    <div class="small text-muted">Jatuh Tempo:</div>
                                    @php
                                        $terlambat = \Carbon\Carbon::parse($p->tanggal_kembali)->startOfDay()->lt(\Carbon\Carbon::today());
                                    @endphp
                                    <span class="fw-bold {{ $terlambat ? 'text-danger' : 'text-slate-800' }}">
                                        {{ \Carbon\Carbon::parse($p->tanggal_kembali)->translatedFormat('d F Y') }}
                                        @if ($terlambat)
                                            <span class="badge bg-danger rounded-pill ms-1">Terlambat</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-3">
                                <form action="{{ route('pengembalian.proses', $p) }}" method="POST">
                                    @csrf
                                    <div class="small fw-bold text-secondary mb-2">
                                        {{ $p->isFisik() ? 'Daftar Berkas Fisik dalam Surat Ini:' : 'Daftar Arsip pada Layanan Non-Fisik Ini:' }}
                                    </div>
                                    @if (!$p->isFisik())
                                        <div class="alert alert-info py-2 px-3 small mb-2 border-0 rounded-3">
                                            <i class="bi bi-info-circle-fill me-1"></i>
                                            <strong>Layanan {{ $p->bentuk_layanan }}:</strong> Konfirmasi selesai akan menutup permohonan ini tanpa mengubah ketersediaan fisik arsip di depo (Scan CNN fisik tidak diperlukan).
                                        </div>
                                    @endif

                                    <div class="table-responsive bg-light rounded-3 border mb-3">
                                        <table class="table table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40px;" class="text-center">Kembalikan</th>
                                                    <th>Kode Berkas</th>
                                                    <th>Uraian Berkas</th>
                                                    <th>Kondisi Diserahkan</th>
                                                    <th style="width: 170px;">Cek Fisik (CNN)</th>
                                                    <th style="width: 155px;">Kondisi Kembali</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($p->items as $item)
                                                    @if ($item->tanggal_kembali_riil)
                                                        <!-- Sudah Kembali -->
                                                        <tr class="table-success text-muted">
                                                            <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i></td>
                                                            <td class="fw-bold">{{ $item->arsip->kode_internal }}</td>
                                                            <td>{{ $item->arsip->uraian_berkas }}</td>
                                                            <td>{{ $item->kondisi_keluar }}</td>
                                                            <td>
                                                                @if($item->skor_integritas !== null)
                                                                    <span class="badge {{ $item->skor_integritas >= 86 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis' }} rounded-pill">
                                                                        CNN: {{ $item->skor_integritas }}%
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-subtle text-success">{{ $item->kondisi_kembali }}</span>
                                                                <small class="d-block text-muted" style="font-size: 0.7rem;">Kembali: {{ \Carbon\Carbon::parse($item->tanggal_kembali_riil)->translatedFormat('d F Y') }}</small>
                                                            </td>
                                                            <td>{{ $item->catatan ?: '-' }}</td>
                                                        </tr>
                                                    @else
                                                        <!-- Belum Kembali -->
                                                        <tr>
                                                            <td class="text-center">
                                                                <input class="form-check-input" type="checkbox" name="item_ids[]" value="{{ $item->id }}" checked>
                                                            </td>
                                                            <td class="fw-bold text-primary">{{ $item->arsip->kode_internal }}</td>
                                                            <td>{{ $item->arsip->uraian_berkas }}</td>
                                                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $item->kondisi_keluar }}</span></td>
                                                            <td>
                                                                @if ($p->isFisik())
                                                                    <button type="button" class="btn-action-pill btn-action-primary btn-cnn-return"
                                                                            data-item-id="{{ $item->id }}"
                                                                            data-kode="{{ $item->arsip->kode_internal }}">
                                                                        <i class="bi bi-cpu"></i> Scan Fisik CNN
                                                                    </button>
                                                                    <div id="cnnBadge{{ $item->id }}" class="small mt-1"></div>
                                                                @else
                                                                    <span class="badge bg-light text-secondary border rounded-pill">Non-Fisik (Tanpa Scan)</span>
                                                                @endif
                                                                <input type="hidden" name="skor_integritas[{{ $item->id }}]" id="cnnSkor{{ $item->id }}">
                                                                <input type="hidden" name="ringkasan_kerusakan[{{ $item->id }}]" id="cnnRingkasan{{ $item->id }}">
                                                                <input type="hidden" name="detail_kerusakan_json[{{ $item->id }}]" id="cnnDetail{{ $item->id }}">
                                                                <input type="hidden" name="foto_kembali_data[{{ $item->id }}]" id="cnnFoto{{ $item->id }}">
                                                            </td>
                                                            <td>
                                                                <select name="kondisi_kembali[{{ $item->id }}]" id="selectKondisi{{ $item->id }}" class="form-select form-select-sm py-0">
                                                                    <option value="Baik" selected>Baik (Lengkap)</option>
                                                                    <option value="Rusak">Rusak</option>
                                                                    <option value="Hilang">Hilang</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="catatan[{{ $item->id }}]" id="inputCatatan{{ $item->id }}" class="form-control form-control-sm py-0" placeholder="Catatan fisik jika ada...">
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-semibold" onclick="return confirm('Konfirmasi pengembalian / penyelesaian item terpilih?')">
                                            <i class="bi bi-check2-circle me-1"></i> {{ $p->isFisik() ? 'Simpan Pengembalian Berkas' : 'Selesaikan Layanan Arsip' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $peminjamanAktif->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@include('partials.modal-cnn-damage')

@push('scripts')
<script>
    document.querySelectorAll('.btn-cnn-return').forEach((btn) => {
        btn.addEventListener('click', () => {
            const itemId = btn.dataset.itemId;
            const kode = btn.dataset.kode;
            window.openDamageAssessmentModal({
                subtitle: `Evaluasi fisik berkas ${kode} saat pengembalian`,
                applyLabel: 'Terapkan ke Form Pengembalian',
                onApply: (res) => {
                    document.getElementById(`cnnSkor${itemId}`).value = res.integrityScore;
                    document.getElementById(`cnnRingkasan${itemId}`).value = res.summaryText;
                    document.getElementById(`cnnDetail${itemId}`).value = JSON.stringify({
                        backend: res.backend,
                        probabilities: res.classProbabilities,
                        detections: res.detections,
                    });
                    document.getElementById(`cnnFoto${itemId}`).value = res.annotatedDataUrl;

                    const selectKondisi = document.getElementById(`selectKondisi${itemId}`);
                    if (selectKondisi) selectKondisi.value = res.kondisiSaran;

                    const inputCatatan = document.getElementById(`inputCatatan${itemId}`);
                    if (inputCatatan) inputCatatan.value = res.summaryText;

                    const badgeWrap = document.getElementById(`cnnBadge${itemId}`);
                    if (badgeWrap) {
                        const cls = res.integrityScore >= 86
                            ? 'bg-success-subtle text-success border-success-subtle'
                            : (res.integrityScore >= 65 ? 'bg-warning-subtle text-warning-emphasis border-warning-subtle' : 'bg-danger-subtle text-danger border-danger-subtle');
                        badgeWrap.innerHTML = `<span class="badge ${cls} border rounded-pill">Integritas: ${res.integrityScore}%</span>`;
                    }
                },
            });
        });
    });
</script>
@endpush
@endsection
