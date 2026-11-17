@extends('layouts.app')

@section('title', 'Sunting Berkas Arsip')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="sipa-page-hero rounded-0 border-0 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <div class="sipa-kicker">INVENTARIS ARSIP</div>
                    <h4 class="fw-bold mb-0 text-slate-800"><i class="bi bi-pencil-square text-primary me-2"></i>Sunting Berkas Arsip</h4>
                    <small class="text-primary fw-bold">{{ $arsip->kode_internal }}</small>
                </div>
                <a href="{{ route('arsip.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 bg-white">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('arsip.update', $arsip) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="unit_pengolah_asal" class="form-label fw-semibold small">Unit Pengolah / Pencipta Asal</label>
                            <input type="text" name="unit_pengolah_asal" id="unit_pengolah_asal" class="form-control @error('unit_pengolah_asal') is-invalid @enderror" placeholder="Contoh: Dinas Kesehatan / Kecamatan Sumber / UK" value="{{ old('unit_pengolah_asal', $arsip->unit_pengolah_asal) }}">
                            @error('unit_pengolah_asal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tingkat_perkembangan" class="form-label fw-semibold small">Tingkat Perkembangan</label>
                            <input type="text" name="tingkat_perkembangan" id="tingkat_perkembangan" class="form-control" value="{{ old('tingkat_perkembangan', $arsip->tingkat_perkembangan ?: 'Asli') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="uraian_berkas" class="form-label fw-semibold small">Uraian Berkas Arsip <span class="text-danger">*</span></label>
                        <input type="text" name="uraian_berkas" id="uraian_berkas" class="form-control @error('uraian_berkas') is-invalid @enderror" value="{{ old('uraian_berkas', $arsip->uraian_berkas) }}" required>
                        @error('uraian_berkas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="kode_klasifikasi" class="form-label fw-semibold small">Kode Klasifikasi</label>
                            <input type="text" name="kode_klasifikasi" id="kode_klasifikasi" class="form-control" value="{{ old('kode_klasifikasi', $arsip->kode_klasifikasi) }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="tahun" class="form-label fw-semibold small">Tahun Berkas <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" id="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', $arsip->tahun) }}" min="1900" max="2100" required>
                            @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="tanggal_surat" class="form-label fw-semibold small">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', $arsip->tanggal_surat?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="uraian_informasi" class="form-label fw-semibold small">Uraian Informasi Detail</label>
                        <textarea name="uraian_informasi" id="uraian_informasi" class="form-control" rows="3">{{ old('uraian_informasi', $arsip->uraian_informasi) }}</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="lokasi_simpan" class="form-label fw-semibold small">Lokasi Simpan / Depo</label>
                            <input type="text" name="lokasi_simpan" id="lokasi_simpan" class="form-control" placeholder="Depo A / UK / Rak B-02" value="{{ old('lokasi_simpan', $arsip->lokasi_simpan) }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="klasifikasi_keamanan" class="form-label fw-semibold small">Klasifikasi Keamanan</label>
                            <select name="klasifikasi_keamanan" id="klasifikasi_keamanan" class="form-select" required>
                                <option value="Biasa/Terbuka" {{ old('klasifikasi_keamanan', $arsip->klasifikasi_keamanan) === 'Biasa/Terbuka' ? 'selected' : '' }}>Biasa / Terbuka</option>
                                <option value="Terbatas" {{ old('klasifikasi_keamanan', $arsip->klasifikasi_keamanan) === 'Terbatas' ? 'selected' : '' }}>Terbatas</option>
                                <option value="Rahasia" {{ old('klasifikasi_keamanan', $arsip->klasifikasi_keamanan) === 'Rahasia' ? 'selected' : '' }}>Rahasia</option>
                                <option value="Sangat Rahasia" {{ old('klasifikasi_keamanan', $arsip->klasifikasi_keamanan) === 'Sangat Rahasia' ? 'selected' : '' }}>Sangat Rahasia</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="status_ketersediaan" class="form-label fw-semibold small">Status Ketersediaan</label>
                            <select name="status_ketersediaan" id="status_ketersediaan" class="form-select" required>
                                <option value="Tersedia" {{ old('status_ketersediaan', $arsip->status_ketersediaan) === 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="Dipinjam" {{ old('status_ketersediaan', $arsip->status_ketersediaan) === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="Rusak" {{ old('status_ketersediaan', $arsip->status_ketersediaan) === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="Hilang" {{ old('status_ketersediaan', $arsip->status_ketersediaan) === 'Hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('arsip.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                            <i class="bi bi-check-lg me-1"></i> Perbarui Arsip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
