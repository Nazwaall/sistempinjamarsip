@extends('layouts.app')

@section('title', 'Impor Data Arsip dari Excel')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
            <div class="sipa-page-hero rounded-0 border-0 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <div class="sipa-kicker">IMPOR MASSAL</div>
                    <h4 class="fw-bold mb-0 text-slate-800"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Impor Data Arsip Excel</h4>
                </div>
                <a href="{{ route('arsip.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 bg-white">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('arsip.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="file_arsip" class="form-label fw-semibold">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file_arsip" id="file_arsip" class="form-control form-control-lg @error('file_arsip') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Mendukung format .xlsx, .xls, dan .csv (Ukuran maksimal 10 MB).</div>
                        @error('file_arsip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Penanganan Data</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="duplikat" id="duplikat_semua" value="semua" checked>
                            <label class="form-check-label" for="duplikat_semua">
                                <strong>Simpan Semua Baris (Direkomendasikan)</strong>
                                <div class="text-muted small">Seluruh baris arsip di Excel akan disimpan tanpa ada yang dilewati (cocok jika ada beberapa berkas dengan judul sama namun beda nomor/boks).</div>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="duplikat" id="duplikat_lewati" value="lewati">
                            <label class="form-check-label" for="duplikat_lewati">
                                <strong>Lewati Baris Duplikat</strong>
                                <div class="text-muted small">Lewati baris jika judul dan uraian berkas sudah pernah tersimpan di sistem.</div>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="duplikat" id="duplikat_perbarui" value="perbarui">
                            <label class="form-check-label" for="duplikat_perbarui">
                                <strong>Perbarui Data Lama</strong>
                                <div class="text-muted small">Timpa data lokasi simpan & detail arsip lama dengan data terbaru dari Excel.</div>
                            </label>
                        </div>
                    </div>

                    <div class="border-top pt-3 d-flex justify-content-end gap-2">
                        <a href="{{ route('arsip.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Mulai Unggah & Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panduan Kolom Format Disarpus Kab. Cirebon -->
        <div class="card border-0 bg-light rounded-3 p-4">
            <h6 class="fw-bold text-slate-800 mb-2"><i class="bi bi-info-circle-fill text-primary me-1"></i> Format Kolom Excel yang Didukung:</h6>
            <p class="small text-muted mb-3">
                Sistem otomatis mengenali kolom berkas Anda (baris judul seperti "DAFTAR ARSIP DINAS..." akan otomatis dilewati).
            </p>
            <div class="table-responsive bg-white rounded-3 border">
                <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kolom di Excel</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Uraian Berkas</td>
                            <td><span class="badge bg-danger">Wajib</span></td>
                            <td>Judul berkas / nama kegiatan arsip</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tahun / Tanggal Surat</td>
                            <td><span class="badge bg-danger">Wajib</span></td>
                            <td>Tahun arsip (misal 2024 atau 12/03/2024)</td>
                        </tr>
                        <tr>
                            <td>Kode Klasifikasi</td>
                            <td><span class="badge bg-secondary">Opsional</span></td>
                            <td>Nomor klasifikasi surat/berkas</td>
                        </tr>
                        <tr>
                            <td>Uraian Informasi Arsip</td>
                            <td><span class="badge bg-secondary">Opsional</span></td>
                            <td>Rincian isi / surat dalam berkas</td>
                        </tr>
                        <tr>
                            <td>Lokasi Simpan</td>
                            <td><span class="badge bg-secondary">Opsional</span></td>
                            <td>Depo A / UK / Rak / Boks penyimpanan</td>
                        </tr>
                        <tr>
                            <td>Unit Pengolah / Pencipta Asal</td>
                            <td><span class="badge bg-secondary">Opsional</span></td>
                            <td>SKPD / Kecamatan / UK pengirim berkas arsip</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
