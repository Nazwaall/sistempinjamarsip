@extends('layouts.app')

@section('title', 'Edit Surat Peminjaman - ' . $peminjaman->nomor_surat)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
            <!-- Header Hero -->
            <div class="sipa-page-hero rounded-0 border-0 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <div class="sipa-kicker">SIRKULASI ARSIP &bull; EDIT DATA</div>
                    <h4 class="fw-bold mb-1 text-slate-800 d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-warning"></i>
                        <span>Edit Surat Peminjaman</span>
                        <span class="badge bg-primary fs-6">{{ $peminjaman->nomor_surat }}</span>
                    </h4>
                    <small class="text-muted">Perbarui parameter peminjaman, data peminjam, berkas arsip, atau status sirkulasi.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3 bg-white">
                        <i class="bi bi-eye me-1"></i> Lihat Detail
                    </a>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 bg-white">
                        <i class="bi bi-arrow-left me-1"></i> Daftar
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-4">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat data yang belum sesuai:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('peminjaman.update', $peminjaman) }}" method="POST" id="formEditPeminjaman">
                    @csrf
                    @method('PUT')

                    <!-- 1. IDENTITAS PEMINJAM & BIDANG -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <h6 class="fw-bold text-slate-800 mb-3 d-flex align-items-center">
                            <i class="bi bi-person-badge text-primary me-2"></i> 1. Peminjam &amp; Bidang Pembuat Surat
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="peminjam_id" class="form-label small fw-semibold text-secondary">
                                    Pilih Nama Peminjam <span class="text-danger">*</span>
                                </label>
                                <select name="peminjam_id" id="peminjam_id" class="form-select @error('peminjam_id') is-invalid @enderror" required>
                                    @foreach ($peminjamList as $pm)
                                        <option value="{{ $pm->id }}" {{ (int) old('peminjam_id', $peminjaman->peminjam_id) === $pm->id ? 'selected' : '' }}>
                                            {{ $pm->nama }} ({{ $pm->jenisLabel() }}) — {{ $pm->instansi_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="unit_pengolah_id" class="form-label small fw-semibold text-secondary">
                                    Bidang / Unit Pembuat Surat <span class="text-danger">*</span>
                                </label>
                                <select name="unit_pengolah_id" id="unit_pengolah_id" class="form-select @error('unit_pengolah_id') is-invalid @enderror" required>
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}" {{ (int) old('unit_pengolah_id', $peminjaman->unit_pengolah_id) === $u->id ? 'selected' : '' }}>
                                            {{ $u->nama }} ({{ $u->kode }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="bentuk_layanan" class="form-label small fw-semibold text-secondary">
                                    Bentuk Layanan <span class="text-danger">*</span>
                                </label>
                                <select name="bentuk_layanan" id="bentuk_layanan" class="form-select" required>
                                    @foreach (\App\Models\SuratPeminjaman::BENTUK_LAYANAN_LIST as $bl)
                                        <option value="{{ $bl }}" {{ old('bentuk_layanan', $peminjaman->bentuk_layanan) === $bl ? 'selected' : '' }}>
                                            {{ $bl }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="jaminan" class="form-label small fw-semibold text-secondary">
                                    Barang / Dokumen Jaminan
                                </label>
                                <input type="text" name="jaminan" id="jaminan" class="form-control" value="{{ old('jaminan', $peminjaman->jaminan) }}" placeholder="Contoh: KTP Asli / SIM">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="status" class="form-label small fw-semibold text-secondary">
                                    Status Surat Peminjaman <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="status" class="form-select fw-semibold" onchange="toggleJanjianFields(this.value)" required>
                                    @php
                                        $statuses = [
                                            'Menunggu Persetujuan' => 'Menunggu Persetujuan',
                                            'Janjian' => 'Janjian Pengambilan',
                                            'Aktif' => 'Aktif (Sedang Dipinjam)',
                                            'Selesai' => 'Selesai (Sudah Kembali)',
                                            'Ditolak' => 'Ditolak',
                                            'Dibatalkan' => 'Dibatalkan',
                                        ];
                                    @endphp
                                    @foreach ($statuses as $val => $label)
                                        <option value="{{ $val }}" {{ old('status', $peminjaman->status) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Opsi Janjian jika status Janjian -->
                            <div class="col-12 row g-3 mt-1 {{ old('status', $peminjaman->status) === 'Janjian' ? '' : 'd-none' }}" id="janjianBox">
                                <div class="col-12 col-md-6">
                                    <label for="rencana_ambil_pada" class="form-label small fw-semibold text-info-emphasis">
                                        Rencana Waktu Ambil Berkas
                                    </label>
                                    <input type="datetime-local" name="rencana_ambil_pada" id="rencana_ambil_pada" class="form-control" value="{{ old('rencana_ambil_pada', $peminjaman->rencana_ambil_pada ? $peminjaman->rencana_ambil_pada->format('Y-m-d\TH:i') : '') }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="catatan_janjian" class="form-label small fw-semibold text-info-emphasis">
                                        Catatan / Tempat Janjian
                                    </label>
                                    <input type="text" name="catatan_janjian" id="catatan_janjian" class="form-control" value="{{ old('catatan_janjian', $peminjaman->catatan_janjian) }}" placeholder="Contoh: Janjian di Ruang Depo Lantai 2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. WAKTU & MAKSUD KEPERLUAN -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <h6 class="fw-bold text-slate-800 mb-3 d-flex align-items-center">
                            <i class="bi bi-calendar-range text-primary me-2"></i> 2. Periode Waktu &amp; Keperluan Peminjaman
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="tanggal_mulai" class="form-label small fw-semibold text-secondary">
                                    Tanggal Mulai Peminjaman <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', $peminjaman->tanggal_mulai ? $peminjaman->tanggal_mulai->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tanggal_kembali" class="form-label small fw-semibold text-secondary">
                                    Tanggal Batas Kembali (Jatuh Tempo) <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control @error('tanggal_kembali') is-invalid @enderror" value="{{ old('tanggal_kembali', $peminjaman->tanggal_kembali ? $peminjaman->tanggal_kembali->format('Y-m-d') : date('Y-m-d', strtotime('+7 days'))) }}" required>
                            </div>

                            <div class="col-12">
                                <label for="maksud_keperluan" class="form-label small fw-semibold text-secondary">
                                    Maksud / Keperluan Peminjaman Arsip <span class="text-danger">*</span>
                                </label>
                                <textarea name="maksud_keperluan" id="maksud_keperluan" rows="3" class="form-control @error('maksud_keperluan') is-invalid @enderror" required placeholder="Tuliskan keperluan dinas / penelitian peminjaman arsip">{{ old('maksud_keperluan', $peminjaman->maksud_keperluan) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 3. PILIH BERKAS ARSIP -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center">
                                    <i class="bi bi-folder2-open text-primary me-2"></i> 3. Berkas Arsip yang Dipinjam <span class="text-danger ms-1">*</span>
                                </h6>
                                <small class="text-muted">Centang berkas arsip yang disertakan dalam surat ini.</small>
                            </div>
                            <div style="max-width: 260px;">
                                <input type="text" id="cariBerkasInput" class="form-control form-control-sm" placeholder="Cari kode/uraian arsip..." onkeyup="filterArsipList()">
                            </div>
                        </div>

                        <div class="table-responsive border rounded-3 bg-white" style="max-height: 320px; overflow-y: auto;">
                            <table class="table table-hover table-sm align-middle mb-0" id="tabelPilihArsip" style="font-size: 0.83rem;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">Pilih</th>
                                        <th>Kode Internal / QR</th>
                                        <th>Uraian Berkas</th>
                                        <th>Bidang Asal</th>
                                        <th>Tahun</th>
                                        <th>Status Arsip</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $checkedIds = old('arsip_ids', $currentArsipIds); @endphp
                                    @forelse ($arsipTersedia as $ar)
                                        @php $isDipilih = in_array($ar->id, $checkedIds); @endphp
                                        <tr class="item-arsip-row {{ $isDipilih ? 'table-primary' : '' }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="arsip_ids[]" value="{{ $ar->id }}" class="form-check-input arsip-checkbox" {{ $isDipilih ? 'checked' : '' }} onchange="highlightRow(this)">
                                            </td>
                                            <td class="fw-bold text-slate-800 text-nowrap">
                                                {{ $ar->kode_internal }}
                                                @if($ar->kode_qr)
                                                    <span class="badge bg-light text-secondary border ms-1 font-monospace">{{ $ar->kode_qr }}</span>
                                                @endif
                                            </td>
                                            <td class="uraian-cell">
                                                <div class="fw-semibold text-slate-800">{{ $ar->uraian_berkas }}</div>
                                                @if($ar->uraian_informasi)
                                                    <div class="text-muted small text-truncate" style="max-width: 320px;">{{ $ar->uraian_informasi }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $ar->unitPengolah->nama ?? $ar->unit_pengolah_asal }}</td>
                                            <td>{{ $ar->tahun }}</td>
                                            <td>
                                                <span class="badge {{ $ar->status_ketersediaan === 'Tersedia' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis' }} border rounded-pill">
                                                    {{ $ar->status_ketersediaan }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                Tidak ada berkas arsip yang dapat dipilih.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="small text-muted mt-2 d-flex justify-content-between align-items-center">
                            <span>Total terpilih: <strong id="totalTerpilihBadge">{{ count($checkedIds) }}</strong> berkas</span>
                            <span class="text-secondary" style="font-size: 0.75rem;">Arsip fisik yang aktif akan otomatis terkunci sebagai 'Dipinjam'</span>
                        </div>
                    </div>

                    <!-- 4. IDENTITAS PETUGAS LAYANAN -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <h6 class="fw-bold text-slate-800 mb-3 d-flex align-items-center">
                            <i class="bi bi-person-workspace text-primary me-2"></i> 4. Petugas Layanan Sirkulasi
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="petugas_layanan_nama" class="form-label small fw-semibold text-secondary">Nama Petugas Layanan</label>
                                <input type="text" name="petugas_layanan_nama" id="petugas_layanan_nama" class="form-control form-control-sm" value="{{ old('petugas_layanan_nama', $peminjaman->petugas_layanan_nama) }}" placeholder="Nama Petugas">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="petugas_layanan_nip" class="form-label small fw-semibold text-secondary">NIP Petugas (Bila Ada)</label>
                                <input type="text" name="petugas_layanan_nip" id="petugas_layanan_nip" class="form-control form-control-sm" value="{{ old('petugas_layanan_nip', $peminjaman->petugas_layanan_nip) }}" placeholder="NIP Petugas">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="petugas_layanan_bidang" class="form-label small fw-semibold text-secondary">Bidang Petugas Layanan</label>
                                <input type="text" name="petugas_layanan_bidang" id="petugas_layanan_bidang" class="form-control form-control-sm" value="{{ old('petugas_layanan_bidang', $peminjaman->petugas_layanan_bidang) }}" placeholder="Bidang Pengelolaan Arsip">
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL SIMPAN & BATAL -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                        <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="bi bi-x-circle me-1"></i> Batal Perubahan
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Surat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleJanjianFields(status) {
        const box = document.getElementById('janjianBox');
        if (!box) return;
        if (status === 'Janjian') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }

    function highlightRow(chk) {
        const tr = chk.closest('tr');
        if (chk.checked) {
            tr.classList.add('table-primary');
        } else {
            tr.classList.remove('table-primary');
        }
        updateCount();
    }

    function updateCount() {
        const count = document.querySelectorAll('.arsip-checkbox:checked').length;
        const badge = document.getElementById('totalTerpilihBadge');
        if (badge) badge.textContent = count;
    }

    function filterArsipList() {
        const q = (document.getElementById('cariBerkasInput').value || '').toLowerCase();
        const rows = document.querySelectorAll('#tabelPilihArsip tbody tr.item-arsip-row');
        rows.forEach(tr => {
            const text = tr.textContent.toLowerCase();
            if (text.includes(q)) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
