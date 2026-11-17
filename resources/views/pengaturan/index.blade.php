@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Kop Surat')

@section('content')
<div class="sipa-page-hero mb-3">
    <div class="sipa-kicker">KONFIGURASI SISTEM</div>
    <h3 class="fw-bold mb-1 text-slate-800">Pengaturan Sistem &amp; Kop Surat</h3>
    <p class="text-muted small mb-0">Sesuaikan profil instansi, format kop resmi surat peminjaman, penanggung jawab bidang, dan parameter pinjam.</p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-sliders text-primary me-2"></i>Parameter Operasional</h6>
            </div>
            <div class="card-body p-4 pt-0">
                <form action="{{ route('pengaturan.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold text-secondary small text-uppercase mb-3 border-bottom pb-2">1. Format Kop Surat Resmi (PDF)</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Kop Baris 1 (Pemerintah Daerah) <span class="text-danger">*</span></label>
                            <input type="text" name="kop_baris_1" class="form-control" value="{{ old('kop_baris_1', $settings['kop_baris_1'] ?? 'PEMERINTAH KABUPATEN CIREBON') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Kop Baris 2 (Nama Dinas) <span class="text-danger">*</span></label>
                            <input type="text" name="kop_baris_2" class="form-control" value="{{ old('kop_baris_2', $settings['kop_baris_2'] ?? 'DINAS KEARSIPAN DAN PERPUSTAKAAN') }}" required>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary small text-uppercase mb-3 mt-4 border-bottom pb-2">2. Identitas & Kontak Dinas</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Resmi Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_dinas" class="form-control" value="{{ old('nama_dinas', $settings['nama_dinas'] ?? 'Dinas Kearsipan dan Perpustakaan Kabupaten Cirebon') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat Lengkap Kantor <span class="text-danger">*</span></label>
                        <textarea name="alamat_dinas" class="form-control" rows="2" required>{{ old('alamat_dinas', $settings['alamat_dinas'] ?? 'Jl. Sunan Drajat No. 10, Sumber, Kabupaten Cirebon, Jawa Barat 45611') }}</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="text" name="telepon_dinas" class="form-control" value="{{ old('telepon_dinas', $settings['telepon_dinas'] ?? '(0231) 321156') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Email Resmi</label>
                            <input type="email" name="email_dinas" class="form-control" value="{{ old('email_dinas', $settings['email_dinas'] ?? 'disarpus@cirebonkab.go.id') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Website Resmi</label>
                            <input type="text" name="website_dinas" class="form-control" value="{{ old('website_dinas', $settings['website_dinas'] ?? 'https://disarpus.cirebonkab.go.id') }}">
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary small text-uppercase mb-3 mt-4 border-bottom pb-2">3. Batas & Kebijakan Peminjaman</h6>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Durasi Default Peminjaman (Hari) <span class="text-danger">*</span></label>
                        <input type="number" name="lama_pinjam_default" class="form-control" style="max-width: 150px;" value="{{ old('lama_pinjam_default', $settings['lama_pinjam_default'] ?? 7) }}" min="1" max="30" required>
                        <small class="text-muted">Batas standar waktu peminjaman berkas arsip fisik (SOP 7 hari kerja).</small>
                    </div>

                    @if(isset($units) && $units->isNotEmpty())
                        <h6 class="fw-bold text-secondary small text-uppercase mb-3 mt-4 border-bottom pb-2">4. Penanggung Jawab Bidang & Batas Pinjam (PDF & Penomoran)</h6>
                        <div class="d-flex flex-column gap-3 mb-4">
                            @foreach($units as $u)
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-bold text-slate-800 small">{{ $u->nama }}</span>
                                        <span class="badge bg-primary font-monospace">{{ $u->kode }}</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small fw-semibold mb-1">Nama Kepala Bidang <span class="text-danger">*</span></label>
                                            <input type="text" name="units[{{ $u->id }}][kepala_nama]" class="form-control form-control-sm" value="{{ old("units.{$u->id}.kepala_nama", $u->kepala_nama) }}" required>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-semibold mb-1">NIP Kepala</label>
                                            <input type="text" name="units[{{ $u->id }}][kepala_nip]" class="form-control form-control-sm" value="{{ old("units.{$u->id}.kepala_nip", $u->kepala_nip) }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold mb-1">Standar (Hr)</label>
                                            <input type="number" name="units[{{ $u->id }}][lama_pinjam_standar]" class="form-control form-control-sm" min="1" max="30" value="{{ old("units.{$u->id}.lama_pinjam_standar", $u->lama_pinjam_standar) }}" required>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold mb-1">Maks (Hr)</label>
                                            <input type="number" name="units[{{ $u->id }}][lama_pinjam_maks]" class="form-control form-control-sm" min="1" max="30" value="{{ old("units.{$u->id}.lama_pinjam_maks", $u->lama_pinjam_maks) }}" required>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="border-top pt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pratinjau Kop Surat -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-light border-0 py-3">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-file-earmark-text text-secondary me-2"></i>Pratinjau Kop Surat PDF</h6>
            </div>
            <div class="card-body p-4 bg-white text-center">
                <div class="p-3 border rounded-2 bg-light">
                    <div class="fw-bold text-slate-900 small text-uppercase" style="letter-spacing: 0.5px;">
                        {{ $settings['kop_baris_1'] ?? 'PEMERINTAH KABUPATEN CIREBON' }}
                    </div>
                    <div class="fw-bold text-primary small text-uppercase fs-6">
                        {{ $settings['kop_baris_2'] ?? 'DINAS KEARSIPAN DAN PERPUSTAKAAN' }}
                    </div>
                    <div class="text-muted" style="font-size: 0.72rem; line-height: 1.3;">
                        {{ $settings['alamat_dinas'] ?? 'Jl. Sunan Drajat No. 10, Sumber, Kab. Cirebon' }}<br>
                        Telp: {{ $settings['telepon_dinas'] ?? '(0231) 321156' }} &bull; Email: {{ $settings['email_dinas'] ?? 'disarpus@cirebonkab.go.id' }}
                    </div>
                    <hr class="my-2 border-dark border-2 opacity-100">
                    <div class="fw-bold mt-2 small">SURAT PEMINJAMAN ARSIP</div>
                    <div class="text-muted" style="font-size: 0.7rem;">Nomor: 001/SPA/ARS/XI/2026</div>
                </div>
                <small class="text-muted d-block mt-3">Format ini diterapkan otomatis pada cetak dokumen PDF surat peminjaman arsip fisik.</small>
            </div>
        </div>
    </div>
</div>
@endsection
