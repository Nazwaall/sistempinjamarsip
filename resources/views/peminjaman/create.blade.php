@extends('layouts.app')

@section('title', 'Peminjaman Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
            <div class="sipa-page-hero rounded-0 border-0 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <div class="sipa-kicker">SIRKULASI ARSIP</div>
                    <h4 class="fw-bold mb-1 text-slate-800"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Formulir Peminjaman Baru</h4>
                    <small class="text-muted">Bidang Pembuat Surat: <strong id="headerUnitLabel">{{ $unit->nama }} ({{ $unit->kode }})</strong> &bull; Lama Pinjam Maksimal: <span id="headerMaxHari">{{ $unit->lama_pinjam_maks }}</span> Hari</small>
                </div>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 bg-white flex-shrink-0">
                    <i class="bi bi-arrow-left me-1"></i> Batal
                </a>
            </div>

            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm border-0 bg-danger-subtle text-danger-emphasis" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-octagon-fill fs-5 mt-0.5"></i>
                            <div>
                                <strong class="d-block mb-1">Peminjaman belum dapat disimpan karena ada isian yang belum sesuai:</strong>
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('peminjaman.store') }}" method="POST" id="formPeminjaman" enctype="multipart/form-data">
                    @csrf

                    <!-- KONFIGURASI BIDANG PEMBUAT SURAT & BENTUK LAYANAN -->
                    <div class="mb-4 p-3 bg-white rounded-3 border border-primary-subtle shadow-sm">
                        <div class="row g-3 align-items-start">
                            <div class="col-12 col-md-5">
                                <label for="unit_pengolah_id" class="form-label small fw-bold text-slate-800">
                                    <i class="bi bi-building text-primary me-1"></i> Bidang / Unit Pembuat Surat <span class="text-danger">*</span>
                                </label>
                                <select name="unit_pengolah_id" id="unit_pengolah_id" class="form-select @error('unit_pengolah_id') is-invalid @enderror" required>
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}"
                                            data-nama="{{ $u->nama }}"
                                            data-kode="{{ $u->kode }}"
                                            data-max-hari="{{ $u->lama_pinjam_maks }}"
                                            {{ (int) old('unit_pengolah_id', $unit->id) === $u->id ? 'selected' : '' }}>
                                            {{ $u->nama }} ({{ $u->kode }}) — Maks. {{ $u->lama_pinjam_maks }} Hari
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text" style="font-size: 0.74rem;">Menentukan kode penerbit nomor surat &amp; batas durasi layanan bidang.</div>
                                @error('unit_pengolah_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-7">
                                <label class="form-label small fw-bold text-slate-800 d-block">
                                    <i class="bi bi-layers text-primary me-1"></i> Bentuk Layanan Arsip <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    @foreach (\App\Models\SuratPeminjaman::BENTUK_LAYANAN_LIST as $idx => $layanan)
                                        @php
                                            $isSelectedLayanan = old('bentuk_layanan', 'Fisik') === $layanan;
                                            $descLayanan = match($layanan) {
                                                'Fisik' => 'Berkas fisik keluar rak/depo',
                                                'Digital (PDF)' => 'Kirim salinan / file scan PDF',
                                                default => 'Baca di tempat / penelusuran arsip',
                                            };
                                        @endphp
                                        <div class="col-12 col-sm-4">
                                            <input class="btn-check input-bentuk-layanan" type="radio" name="bentuk_layanan" id="layanan_{{ $idx }}" value="{{ $layanan }}" {{ $isSelectedLayanan ? 'checked' : '' }} required>
                                            <label class="btn btn-outline-primary w-100 text-start p-2 h-100 rounded-3" for="layanan_{{ $idx }}" style="font-size: 0.78rem;">
                                                <div class="fw-bold">{{ $layanan }}</div>
                                                <div class="opacity-75" style="font-size: 0.69rem; line-height: 1.2;">{{ $descLayanan }}</div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="noteBentukLayanan" class="small text-muted mt-2" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Layanan <strong>Fisik</strong> mengunci ketersediaan berkas menjadi <em>Dipinjam</em> saat aktif; layanan <strong>Digital (PDF)</strong> &amp; <strong>Layanan Informasi</strong> membiarkan stok fisik tetap <em>Tersedia</em>.
                                </div>
                                @error('bentuk_layanan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- LANGKAH 1: PILIH BERKAS ARSIP -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-slate-800 mb-0">
                                <span class="badge bg-primary rounded-circle me-1">1</span> Pilih Berkas Arsip
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalKatalogArsip">
                                <i class="bi bi-collection me-1"></i> Cari di Katalog Lengkap
                            </button>
                        </div>
                        <p class="text-muted small mb-3">Pilih satu atau beberapa berkas arsip menggunakan pencarian cepat, daftar dropdown, atau katalog. Klik <strong>Detail</strong> untuk melihat isi berkas sebelum memilih.</p>

                        <!-- Pilihan Berkas: Pencarian Cepat, Dropdown Rapi & Katalog -->
                        <div class="row g-2 align-items-center position-relative">
                            <div class="col-12 col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" id="cariArsipInput" class="form-control" placeholder="Ketik kode / perihal / isi / pencipta..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <select class="form-select" id="selectArsipPicker" dir="ltr" style="text-align: left;">
                                    <option value="">-- Pilih Berkas Arsip --</option>
                                    @foreach ($arsipTersedia as $a)
                                        <option value="{{ $a->id }}"
                                            data-kode="{{ $a->kode_internal }}"
                                            data-uraian="{{ $a->uraian_berkas }}"
                                            data-tahun="{{ $a->tahun }}"
                                            data-lokasi="{{ $a->lokasi_simpan ?: '-' }}"
                                            data-asal="{{ $a->unit_pengolah_asal ?: ($a->unitPengolah?->nama ?? '-') }}"
                                            data-status="{{ $a->status_ketersediaan }}">
                                            {{ $a->kode_internal }} &bull; {{ \Illuminate\Support\Str::limit($a->uraian_berkas, 42) }} ({{ $a->tahun }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-info text-nowrap" id="btnLihatDetailPicker" disabled title="Lihat Detail Isi Berkas">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </button>
                                    <button type="button" class="btn btn-primary flex-fill text-nowrap" id="btnTambahArsipManual" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                                        <i class="bi bi-plus-lg me-1"></i> Masukkan
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalKatalogArsip" title="Buka Katalog & Pilih Banyak Sekaligus">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Search Suggestion Box -->
                            <div id="hasilCariArsipLive" class="list-group shadow-lg position-absolute mt-1 border rounded-3 d-none" style="top: 100%; left: 0; right: 0; z-index: 1060; max-height: 300px; overflow-y: auto;">
                            </div>
                        </div>

                        <!-- Pratinjau Detail Berkas Terpilih di Dropdown -->
                        <div id="previewArsipPicker" class="mt-3 p-3 bg-white rounded-3 border border-info-subtle shadow-sm d-none">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary-subtle text-primary font-monospace fw-bold" id="prevKode"></span>
                                        <span class="badge bg-light text-dark border" id="prevTahun"></span>
                                        <span class="badge" id="prevStatus"></span>
                                        <span class="badge bg-secondary-subtle text-secondary" id="prevKeamanan"></span>
                                    </div>
                                    <div class="fw-bold text-slate-800 mb-1" id="prevUraian"></div>
                                    <div class="small text-secondary mb-2" id="prevInformasi"></div>
                                    <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.76rem;">
                                        <span><i class="bi bi-building me-1"></i>Asal: <strong id="prevAsal"></strong></span>
                                        <span><i class="bi bi-tag me-1"></i>Klasifikasi: <strong id="prevKlasifikasi"></strong></span>
                                        <span><i class="bi bi-calendar3 me-1"></i>Tgl Surat: <strong id="prevTanggal"></strong></span>
                                        <span><i class="bi bi-geo-alt me-1"></i>Lokasi: <strong id="prevLokasi"></strong></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" id="btnPreviewDetailModal">
                                        <i class="bi bi-eye me-1"></i> Detail Lengkap
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnPreviewTambah" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                                        <i class="bi bi-plus-lg me-1"></i> Masukkan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Berkas Terpilih (Keranjang) -->
                        <div class="mt-3">
                            <div class="small fw-bold text-secondary mb-1">Berkas Arsip dalam Surat Ini:</div>
                            <div class="table-responsive bg-white rounded-3 border">
                                <table class="table table-sm align-middle mb-0" id="tabelArsipTerpilih" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode Berkas</th>
                                            <th>Uraian Berkas &amp; Asal</th>
                                            <th>Tahun</th>
                                            <th>Lokasi / Depo</th>
                                            <th class="text-end" style="width: 110px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyArsipTerpilih">
                                        <tr id="emptyRowArsip">
                                            <td colspan="5" class="text-center text-muted py-3">Belum ada berkas arsip yang dimasukkan ke surat.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @error('arsip_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- LANGKAH 2: DATA PEMINJAM -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-slate-800 mb-2">
                            <span class="badge bg-primary rounded-circle me-1">2</span> Identitas Peminjam
                        </h6>

                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipe_peminjam" id="tipe_terdaftar" value="terdaftar" {{ old('tipe_peminjam', 'terdaftar') !== 'baru' ? 'checked' : '' }} onchange="togglePeminjamForm()">
                                <label class="form-check-label" for="tipe_terdaftar">Peminjam yang Pernah Terdaftar</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipe_peminjam" id="tipe_baru" value="baru" {{ old('tipe_peminjam') === 'baru' ? 'checked' : '' }} onchange="togglePeminjamForm()">
                                <label class="form-check-label" for="tipe_baru">Catat Peminjam Baru</label>
                            </div>
                        </div>

                        <!-- Peminjam Terdaftar -->
                        <div id="sectionPeminjamTerdaftar" class="mb-3 {{ old('tipe_peminjam') === 'baru' ? 'd-none' : '' }}">
                            <select name="peminjam_id" id="peminjam_id" class="form-select @error('peminjam_id') is-invalid @enderror" dir="ltr" style="text-align: left;">
                                <option value="">-- Pilih Peminjam Terdaftar --</option>
                                @foreach ($peminjamList as $p)
                                    <option value="{{ $p->id }}" data-jaminan="{{ $p->jaminan }}" {{ old('peminjam_id', request('peminjam_id')) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }} &bull; {{ \Illuminate\Support\Str::limit($p->instansi_unit, 36) }} ({{ $p->jenisLabel() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('peminjam_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Form Peminjam Baru -->
                        <div id="sectionPeminjamBaru" class="row g-3 {{ old('tipe_peminjam') === 'baru' ? '' : 'd-none' }}">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama_peminjam" class="form-control" placeholder="Nama peminjam" value="{{ old('nama_peminjam') }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-semibold">Jenis Peminjam <span class="text-danger">*</span></label>
                                <select name="jenis_peminjam" id="jenis_peminjam_baru" class="form-select">
                                    @foreach (\App\Models\Peminjam::JENIS_LABELS as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('jenis_peminjam') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-semibold">NIP / NIK</label>
                                <input type="text" name="nip_nik" class="form-control" placeholder="NIP (Internal) / NIK (Eksternal)" value="{{ old('nip_nik') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Instansi / Unit Kerja <span class="text-danger">*</span></label>
                                <input type="text" name="instansi_unit" class="form-control" placeholder="Contoh: Bappelitbangda / Bagian Umum" value="{{ old('instansi_unit') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Jabatan</label>
                                <input type="text" name="jabatan_peminjam" class="form-control" placeholder="Jabatan peminjam" value="{{ old('jabatan_peminjam') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" value="{{ old('no_hp') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Alamat Lengkap <span class="text-danger d-none" id="alamatBaruWajib">*</span><span class="text-muted fw-normal" id="alamatBaruHint">(Opsional untuk Internal/SKPD/Kecamatan, wajib untuk Eksternal)</span></label>
                                <input type="text" name="alamat" id="alamat_baru" class="form-control" placeholder="Alamat instansi / domisili peminjam" value="{{ old('alamat') }}">
                            </div>
                        </div>

                        <!-- Kolom Jaminan (Berlaku untuk Peminjam Baru maupun Terdaftar) -->
                        <div class="row g-2 mt-2 pt-2 border-top">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Jaminan Peminjaman (KTP / Surat / Lainnya)</label>
                                <select name="jaminan_opsi" id="jaminan_opsi" class="form-select">
                                    <option value="">-- Pilih Jaminan --</option>
                                    <option value="KTP" {{ old('jaminan_opsi') === 'KTP' ? 'selected' : '' }}>KTP</option>
                                    <option value="Surat Tugas / Pengantar" {{ old('jaminan_opsi') === 'Surat Tugas / Pengantar' ? 'selected' : '' }}>Surat Tugas / Pengantar</option>
                                    <option value="Kartu Pegawai / ID Card" {{ old('jaminan_opsi') === 'Kartu Pegawai / ID Card' ? 'selected' : '' }}>Kartu Pegawai / ID Card</option>
                                    <option value="SIM" {{ old('jaminan_opsi') === 'SIM' ? 'selected' : '' }}>SIM</option>
                                    <option value="Kartu Mahasiswa / Pelajar" {{ old('jaminan_opsi') === 'Kartu Mahasiswa / Pelajar' ? 'selected' : '' }}>Kartu Mahasiswa / Pelajar</option>
                                    <option value="Tanpa Jaminan" {{ old('jaminan_opsi') === 'Tanpa Jaminan' ? 'selected' : '' }}>Tanpa Jaminan</option>
                                    <option value="Lainnya" {{ old('jaminan_opsi') === 'Lainnya' ? 'selected' : '' }}>Lainnya (Sebutkan)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 {{ old('jaminan_opsi') === 'Lainnya' ? '' : 'd-none' }}" id="wrapJaminanLainnya">
                                <label class="form-label small fw-semibold">Keterangan Jaminan Lainnya</label>
                                <input type="text" name="jaminan_lainnya" id="jaminan_lainnya" class="form-control" placeholder="Contoh: Paspor / Surat Kuasa..." value="{{ old('jaminan_lainnya') }}">
                            </div>
                        </div>
                    </div>

                    <!-- LANGKAH 3: RENTANG WAKTU & MAKSUD PINJAM -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-slate-800 mb-2">
                            <span class="badge bg-primary rounded-circle me-1">3</span> Rentang Waktu & Maksud Peminjaman
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Tanggal Mulai Pinjam <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required onchange="hitungLamaPinjam()">
                                @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Tanggal Kembali (Jatuh Tempo) <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control @error('tanggal_kembali') is-invalid @enderror" value="{{ old('tanggal_kembali', date('Y-m-d', strtotime('+' . ($unit->lama_pinjam_standar ?: 7) . ' days'))) }}" required onchange="hitungLamaPinjam()">
                                @error('tanggal_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Durasi Peminjaman</label>
                                <div class="input-group">
                                    <input type="text" id="lamaPinjamTampil" class="form-control bg-white fw-bold text-primary" readonly value="{{ $unit->lama_pinjam_standar ?: 7 }} Hari">
                                    <span class="input-group-text bg-white small text-muted" id="badgeMaxHariText">Maks. {{ $unit->lama_pinjam_maks }} Hari</span>
                                </div>
                                <div id="pesanValidasiDurasi" class="text-danger small mt-1 d-none">Lama pinjam melebihi batas {{ $unit->lama_pinjam_maks }} hari!</div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Maksud / Keperluan Peminjaman <span class="text-danger">*</span></label>
                            <textarea name="maksud_keperluan" class="form-control @error('maksud_keperluan') is-invalid @enderror" rows="2" placeholder="Contoh: Keperluan pemeriksaan BPK / bahan kajian penyusunan laporan tahunan..." required>{{ old('maksud_keperluan') }}</textarea>
                            @error('maksud_keperluan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- LANGKAH 4: TANDA TANGAN & FOTO BUKTI -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-slate-800 mb-2">
                            <span class="badge bg-primary rounded-circle me-1">4</span> Bukti Sah: Tanda Tangan & Foto Serah Terima
                        </h6>

                        <!-- Pilihan Metode Tanda Tangan -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6 border-end-md">
                                <div class="fw-semibold small mb-2 text-slate-800"><i class="bi bi-pen me-1"></i> Tanda Tangan Peminjam:</div>
                                <div class="form-check form-check-inline mb-2">
                                    <input class="form-check-input" type="radio" name="metode_ttd" id="ttd_digital" value="Digital" checked onchange="toggleTtdMetode()">
                                    <label class="form-check-label small" for="ttd_digital">Tanda Tangan Digital (Di Layar HP/Komputer)</label>
                                </div>
                                <div class="form-check form-check-inline mb-2">
                                    <input class="form-check-input" type="radio" name="metode_ttd" id="ttd_manual" value="Manual" onchange="toggleTtdMetode()">
                                    <label class="form-check-label small" for="ttd_manual">Tanda Tangan Manual (Cetak & Unggah Surat)</label>
                                </div>

                                <!-- Kanvas TTD Digital -->
                                <div id="panelTtdDigital" class="mt-2">
                                    <div class="border rounded-3 bg-white position-relative" style="height: 160px; touch-action: none;">
                                        <canvas id="padTtd" class="w-100 h-100"></canvas>
                                        <small class="text-muted position-absolute bottom-0 start-50 translate-middle-x mb-1 pointer-events-none" style="font-size: 0.65rem;">
                                            Gunakan jari atau mouse untuk bertanda tangan di sini
                                        </small>
                                    </div>
                                    <div class="mt-1 d-flex justify-content-between">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnClearTtd" style="font-size: 0.75rem;">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Hapus & Ulangi TTD
                                        </button>
                                        <span class="text-success small d-none" id="statusTtdOk"><i class="bi bi-check-circle-fill"></i> Tanda tangan terekam</span>
                                    </div>
                                    <input type="hidden" name="ttd_digital_data" id="ttd_digital_data">
                                </div>

                                <!-- Unggah Surat Manual -->
                                <div id="panelTtdManual" class="mt-2 d-none">
                                    <label class="form-label small text-muted">Unggah Foto Surat Fisik yang Sudah Bertanda Tangan:</label>
                                    <input type="file" name="surat_ttd_file" class="form-control form-control-sm" accept="image/*,.pdf">
                                </div>
                            </div>

                            <!-- Pemindai Dokumen Cerdas (OpenCV.js Perspective Correction) -->
                            <div class="col-12 col-md-6" id="buktiSection">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                    <div class="fw-semibold small text-slate-800">
                                        <i class="bi bi-bounding-box-circles text-primary me-1"></i> Scan Bukti Foto (KTP / Surat / Serah Terima):
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="badgeBuktiCv" class="badge bg-light text-secondary border" style="font-size: 0.65rem;">Auto-Crop &amp; Luruskan</span>
                                        <a href="{{ route('pemindai.index') }}" class="btn btn-outline-primary btn-sm py-0 px-2 rounded-pill" style="font-size: 0.68rem;" title="Buka di halaman Pemindai Layar Penuh">
                                            <i class="bi bi-arrows-fullscreen me-1"></i>Pemindai Penuh
                                        </a>
                                    </div>
                                </div>

                                <div class="d-flex flex-column align-items-center bg-white border rounded-3 p-2">
                                    <div class="w-100 mb-2">
                                        <select name="foto_bukti_jenis" id="foto_bukti_jenis" class="form-select form-select-sm">
                                            <option value="Serah Terima" {{ old('foto_bukti_jenis') === 'Serah Terima' ? 'selected' : '' }}>Jenis: Bukti Foto Serah Terima Berkas</option>
                                            <option value="Identitas" {{ old('foto_bukti_jenis') === 'Identitas' ? 'selected' : '' }}>Jenis: Foto KTP / Identitas Jaminan</option>
                                            <option value="Surat Bertanda Tangan" {{ old('foto_bukti_jenis') === 'Surat Bertanda Tangan' ? 'selected' : '' }}>Jenis: Foto Surat Pengantar / Bertanda Tangan</option>
                                        </select>
                                    </div>
                                    <div class="position-relative bg-dark rounded-3 overflow-hidden text-center mb-2" style="width: 100%; max-width: 300px; height: 190px;">
                                        <video id="videoBukti" autoplay playsinline muted class="w-100 h-100" style="object-fit: contain; display: none;"></video>
                                        <canvas id="overlayBuktiRealtime" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; object-fit: contain; display: none;"></canvas>
                                        <img id="previewFotoBukti" class="w-100 h-100" style="object-fit: contain; display: none;" alt="Hasil Scan Dokumen">
                                        <div id="placeholderBukti" class="d-flex flex-column align-items-center justify-content-center h-100 text-white-50 p-2">
                                            <i class="bi bi-bounding-box-circles fs-2 text-info mb-1"></i>
                                            <small style="font-size: 0.7rem;">Kamera otomatis mendeteksi 4 sudut kertas &amp; meluruskan foto miring</small>
                                        </div>
                                        <span id="badgeLiveBukti" class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-1 d-none" style="font-size: 0.65rem;">Mendeteksi tepi...</span>
                                    </div>

                                    <!-- Filter Hasil Scan -->
                                    <div id="panelFilterBukti" class="d-none mb-2 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="buktiFilter" id="bFilterEnhance" value="enhance" checked>
                                            <label class="btn btn-outline-primary py-0 px-2" style="font-size: 0.72rem;" for="bFilterEnhance">Scanner Cerah</label>

                                            <input type="radio" class="btn-check" name="buktiFilter" id="bFilterOriginal" value="original">
                                            <label class="btn btn-outline-primary py-0 px-2" style="font-size: 0.72rem;" for="bFilterOriginal">Warna Asli</label>

                                            <input type="radio" class="btn-check" name="buktiFilter" id="bFilterBw" value="bw">
                                            <label class="btn btn-outline-primary py-0 px-2" style="font-size: 0.72rem;" for="bFilterBw">Hitam-Putih</label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1" id="btnToggleSudutBukti" style="font-size: 0.72rem;">
                                            <i class="bi bi-arrows-move"></i> Atur 4 Sudut
                                        </button>
                                    </div>

                                    <!-- Editor Geser 4 Sudut Manual (Jika Diperlukan) -->
                                    <div id="panelEditSudutBukti" class="d-none w-100 mb-2 bg-dark rounded-2 p-1 text-center">
                                        <canvas id="editorSudutBukti" class="w-100" style="max-height: 220px; object-fit: contain; touch-action: none; cursor: crosshair;"></canvas>
                                        <small class="text-white-50 d-block" style="font-size: 0.65rem;">Geser 4 titik biru pada sudut kertas untuk menyesuaikan</small>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnMulaiKameraBukti">
                                            <i class="bi bi-webcam me-1"></i> Pindai Kamera
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 d-none" id="btnSnapBukti">
                                            <i class="bi bi-camera-fill me-1"></i> Jepret &amp; Luruskan
                                        </button>
                                        <label class="btn btn-sm btn-outline-secondary rounded-pill px-3 mb-0" style="cursor: pointer;">
                                            <i class="bi bi-upload me-1"></i> Unggah Foto
                                            <input type="file" id="inputFileBukti" accept="image/*" style="display: none;">
                                        </label>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-none" id="btnHapusFotoBukti">
                                            <i class="bi bi-arrow-repeat me-1"></i> Ambil Gambar Ulang
                                        </button>
                                    </div>
                                    <input type="hidden" name="foto_bukti_data" id="foto_bukti_data">
                                    <input type="hidden" name="foto_bukti_sumber" id="foto_bukti_sumber" value="Kamera CV">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- IDENTITAS PETUGAS LAYANAN (PENANDA TANGAN SURAT) -->
                    @php
                        $authUser = Auth::user();
                        $defaultPetugasNama = old('petugas_layanan_nama', $authUser->name);
                        $defaultPetugasNip = old('petugas_layanan_nip', $authUser->nip ?: '-');
                        $defaultPetugasBidang = old('petugas_layanan_bidang', $authUser->unitPengolah?->nama ?: ($units->first()->nama ?? 'Bidang Kearsipan'));
                    @endphp
                    <div class="mb-4 p-3 bg-white rounded-3 border border-primary-subtle shadow-sm">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-slate-800 mb-0">
                                    <i class="bi bi-person-badge-fill text-primary me-1"></i> Petugas Layanan (Tanda Tangan Surat)
                                </h6>
                                <small class="text-muted">Nama, NIP, dan Bidang petugas yang melayani akan tercantum pada kolom TTD Petugas Layanan di Surat PDF.</small>
                            </div>
                            @if (isset($petugasList) && $petugasList->isNotEmpty())
                                <div style="min-width: 240px;">
                                    <select id="selectPetugasPreset" class="form-select form-select-sm">
                                        <option value="">-- Pilih Cepat dari Akun Petugas --</option>
                                        @foreach ($petugasList as $ptg)
                                            <option value="{{ $ptg->id }}"
                                                data-nama="{{ $ptg->name }}"
                                                data-nip="{{ $ptg->nip ?? '' }}"
                                                data-bidang="{{ $ptg->unitPengolah?->nama ?? '' }}">
                                                {{ $ptg->name }}{{ $ptg->unitPengolah ? ' (' . $ptg->unitPengolah->kode . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <label for="petugas_layanan_nama" class="form-label small fw-semibold">Nama Petugas yang Melayani</label>
                                <input type="text" name="petugas_layanan_nama" id="petugas_layanan_nama"
                                    class="form-control @error('petugas_layanan_nama') is-invalid @enderror"
                                    placeholder="Masukkan nama petugas layanan"
                                    value="{{ $defaultPetugasNama }}">
                                @error('petugas_layanan_nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="petugas_layanan_nip" class="form-label small fw-semibold">NIP Petugas</label>
                                <input type="text" name="petugas_layanan_nip" id="petugas_layanan_nip"
                                    class="form-control @error('petugas_layanan_nip') is-invalid @enderror"
                                    placeholder="Contoh: 198805202010012003 atau -"
                                    value="{{ $defaultPetugasNip }}">
                                <div class="form-text" style="font-size: 0.72rem;">Isi <code>-</code> jika non-ASN.</div>
                                @error('petugas_layanan_nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="petugas_layanan_bidang" class="form-label small fw-semibold">Dari Bidang</label>
                                <select name="petugas_layanan_bidang" id="petugas_layanan_bidang" class="form-select @error('petugas_layanan_bidang') is-invalid @enderror">
                                    <option value="">-- Pilih Bidang Petugas --</option>
                                    @foreach ($units as $u)
                                        <option value="{{ $u->nama }}" {{ $defaultPetugasBidang === $u->nama ? 'selected' : '' }}>
                                            {{ $u->nama }} ({{ $u->kode }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('petugas_layanan_bidang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL SIMPAN & AJUKAN -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <small class="text-muted"><i class="bi bi-shield-lock me-1"></i> Surat akan diajukan ke Super Admin / Kepala Bidang (Kabid) untuk persetujuan resmi.</small>
                        <div class="d-flex gap-2">
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnSubmitSurat" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                                <i class="bi bi-check2-circle me-1"></i> Simpan & Terbitkan Surat
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KATALOG ARSIP (PILIH BANYAK SEKALIGUS DENGAN FILTER) -->
<div class="modal fade" id="modalKatalogArsip" tabindex="-1" aria-labelledby="modalKatalogArsipLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-white py-3 border-bottom">
                <div>
                    <h5 class="modal-title fw-bold text-slate-800" id="modalKatalogArsipLabel">
                        <i class="bi bi-collection-fill text-primary me-2"></i>Katalog Berkas Arsip Tersedia
                    </h5>
                    <small class="text-muted">Pilih satu atau centang beberapa berkas arsip sekaligus untuk dimasukkan ke surat peminjaman.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Filter Cepat Modal -->
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="filterTeksModal" class="form-control border-start-0" placeholder="Ketik kode internal, uraian berkas, atau lokasi simpan...">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select id="filterTahunModal" class="form-select">
                            <option value="">Semua Tahun Berkas</option>
                            @foreach ($tahunList as $thn)
                                <option value="{{ $thn }}">Tahun {{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tabel Berkas di Modal -->
                <div class="table-responsive bg-white rounded-3 border">
                    <table class="table table-hover align-middle mb-0" id="tabelKatalogModal" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 44px;" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="checkAllModal" title="Pilih Semua di Halaman">
                                </th>
                                <th style="width: 170px;">Kode Berkas</th>
                                <th>Uraian Berkas &amp; Detail Isi</th>
                                <th style="width: 85px;" class="text-center">Tahun</th>
                                <th style="width: 155px;">Lokasi / Depo</th>
                                <th style="width: 90px;" class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody id="bodyKatalogModal">
                            @foreach ($arsipTersedia as $a)
                                <tr class="baris-katalog"
                                    data-id="{{ $a->id }}"
                                    data-kode="{{ $a->kode_internal }}"
                                    data-uraian="{{ $a->uraian_berkas }}"
                                    data-informasi="{{ $a->uraian_informasi ?? '' }}"
                                    data-tahun="{{ $a->tahun }}"
                                    data-lokasi="{{ $a->lokasi_simpan ?: '-' }}"
                                    data-asal="{{ $a->unit_pengolah_asal ?: ($a->unitPengolah?->nama ?? '-') }}"
                                    data-status="{{ $a->status_ketersediaan }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input check-item-modal" value="{{ $a->id }}">
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary fw-bold font-monospace px-2 py-1 d-inline-block">{{ $a->kode_internal }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-slate-800">{{ $a->uraian_berkas }}</div>
                                        @if ($a->uraian_informasi)
                                            <div class="text-secondary small" style="font-size: 0.76rem;">{{ \Illuminate\Support\Str::limit($a->uraian_informasi, 110) }}</div>
                                        @endif
                                        <small class="text-muted d-block">
                                            Asal: {{ $a->unit_pengolah_asal ?: ($a->unitPengolah?->nama ?? '-') }}
                                            @if ($a->kode_klasifikasi)
                                                &bull; Klas: {{ $a->kode_klasifikasi }}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $a->tahun }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><i class="bi bi-geo-alt me-1 text-secondary"></i>{{ $a->lokasi_simpan ?: '-' }}</span>
                                        @if ($a->status_ketersediaan !== 'Tersedia')
                                            <div class="mt-1"><span class="badge bg-warning text-dark" style="font-size: 0.66rem;">{{ $a->status_ketersediaan }} (Non-Fisik saja)</span></div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-0 btn-detail-katalog" data-id="{{ $a->id }}" title="Lihat Detail Isi Berkas">
                                            <i class="bi bi-eye me-1"></i>Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white d-flex justify-content-between py-2">
                <div>
                    <span class="badge bg-primary rounded-pill px-3 py-2" id="labelTerpilihModal">0 Berkas Terpilih</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnTerapkanModal" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                        <i class="bi bi-plus-circle me-1"></i> Masukkan ke Surat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL BERKAS ARSIP SEBELUM MEMILIH -->
<div class="modal fade" id="modalDetailArsipPinjam" tabindex="-1" aria-labelledby="modalDetailArsipPinjamLabel" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light py-3 border-bottom">
                <div>
                    <h5 class="modal-title fw-bold text-slate-800" id="modalDetailArsipPinjamLabel">
                        <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Detail Berkas Arsip
                    </h5>
                    <small class="text-muted">Periksa rincian isi surat/berkas sebelum memasukkannya ke peminjaman.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-primary font-monospace fs-6 px-3 py-1" id="detArsipKode"></span>
                    <span class="badge rounded-pill px-3 py-1" id="detArsipStatus"></span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1" id="detArsipKeamanan"></span>
                </div>

                <h5 class="fw-bold text-slate-800 mb-2" id="detArsipUraian"></h5>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="small fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.72rem;">Tentang / Uraian Informasi Isi Berkas</div>
                    <div class="text-slate-800 small" id="detArsipInformasi" style="white-space: pre-line;"></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless small mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 170px;">Bidang Pengelola SIPA</td>
                                <td class="fw-semibold" id="detArsipBidangPengelola"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Pencipta / Asal Berkas</td>
                                <td class="fw-semibold" id="detArsipAsal"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kode Klasifikasi</td>
                                <td class="fw-semibold font-monospace" id="detArsipKlasifikasi"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tahun &amp; Tanggal Surat</td>
                                <td id="detArsipWaktu"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Lokasi Simpan / Depo</td>
                                <td class="fw-semibold text-primary" id="detArsipLokasi"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tingkat Perkembangan</td>
                                <td id="detArsipPerkembangan"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jadwal Retensi (JRA)</td>
                                <td id="detArsipJra"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold" id="btnTambahDariDetail" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    <i class="bi bi-plus-circle me-1"></i> Masukkan ke Surat
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Modul OpenCV.js Pemindai Dokumen Cerdas (Deteksi Tepi + Koreksi Perspektif) -->
<script src="{{ asset('js/opencv-doc-scanner.js') }}"></script>
<!-- Signature Pad untuk TTD Digital -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

@php
    $arsipDetailMap = $arsipTersedia->mapWithKeys(function ($a) {
        $jraParts = [];
        if ($a->jra_aktif !== null) $jraParts[] = "Aktif {$a->jra_aktif} th";
        if ($a->jra_inaktif !== null) $jraParts[] = "Inaktif {$a->jra_inaktif} th";
        $jraLabel = !empty($jraParts)
            ? implode(', ', $jraParts) . ($a->jra_keterangan ? " ({$a->jra_keterangan})" : '')
            : ($a->jra_keterangan ?: '-');

        return [
            (string) $a->id => [
                'id' => (string) $a->id,
                'kode' => $a->kode_internal,
                'uraian' => $a->uraian_berkas,
                'informasi' => $a->uraian_informasi ?: '',
                'klasifikasi' => $a->kode_klasifikasi ?: '-',
                'tanggal_surat' => $a->tanggal_surat ? $a->tanggal_surat->translatedFormat('d F Y') : '-',
                'tahun' => (string) $a->tahun,
                'bulan' => $a->bulan ? \Carbon\Carbon::create()->month((int) $a->bulan)->translatedFormat('F') : null,
                'lokasi' => $a->lokasi_simpan ?: '-',
                'asal' => $a->unit_pengolah_asal ?: ($a->unitPengolah?->nama ?? '-'),
                'bidang_pengelola' => $a->unitPengolah ? "{$a->unitPengolah->nama} ({$a->unitPengolah->kode})" : '-',
                'perkembangan' => $a->tingkat_perkembangan ?: 'Asli',
                'keamanan' => $a->klasifikasi_keamanan ?: 'Biasa/Terbuka',
                'jra' => $jraLabel,
                'status' => $a->status_ketersediaan ?: 'Tersedia',
            ],
        ];
    });
@endphp

<script>
    const arsipDetailMap = @json($arsipDetailMap);

    // --- 1. INISIALISASI KERANJANG ARSIP ---
    const listArsipTerpilih = [];
    const selectPicker = document.getElementById('selectArsipPicker');
    const bodyTabel = document.getElementById('bodyArsipTerpilih');
    const emptyRow = document.getElementById('emptyRowArsip');
    const unitSelect = document.getElementById('unit_pengolah_id');

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[m]));
    }

    function getSelectedBentukLayanan() {
        const checked = document.querySelector('input[name="bentuk_layanan"]:checked');
        return checked ? checked.value : 'Fisik';
    }

    function getSelectedMaxHari() {
        if (!unitSelect) return {{ $unit->lama_pinjam_maks ?? 7 }};
        const opt = unitSelect.options[unitSelect.selectedIndex];
        return parseInt(opt?.getAttribute('data-max-hari') || '7', 10) || 7;
    }

    function syncUnitHeaderDanBatas() {
        if (!unitSelect) return;
        const opt = unitSelect.options[unitSelect.selectedIndex];
        const nama = opt?.getAttribute('data-nama') || '';
        const kode = opt?.getAttribute('data-kode') || '';
        const maxHari = getSelectedMaxHari();

        const hdrUnit = document.getElementById('headerUnitLabel');
        const hdrMax = document.getElementById('headerMaxHari');
        const badgeMax = document.getElementById('badgeMaxHariText');
        if (hdrUnit) hdrUnit.textContent = `${nama} (${kode})`;
        if (hdrMax) hdrMax.textContent = maxHari;
        if (badgeMax) badgeMax.textContent = `Maks. ${maxHari} Hari`;
        hitungLamaPinjam();
    }

    if (unitSelect) {
        unitSelect.addEventListener('change', syncUnitHeaderDanBatas);
    }

    function evaluasiKesesuaianLayanan() {
        const layanan = getSelectedBentukLayanan();
        const noteEl = document.getElementById('noteBentukLayanan');

        if (noteEl) {
            if (layanan === 'Fisik') {
                noteEl.innerHTML = '<i class="bi bi-info-circle text-primary me-1"></i> Layanan <strong>Fisik</strong> mengunci ketersediaan berkas menjadi <em>Dipinjam</em> saat aktif sampai dikembalikan.';
            } else if (layanan === 'Digital (PDF)') {
                noteEl.innerHTML = '<i class="bi bi-file-earmark-pdf text-info me-1"></i> Layanan <strong>Digital (PDF)</strong> memberikan salinan file digital tanpa mengunci berkas fisik di rak.';
            } else {
                noteEl.innerHTML = '<i class="bi bi-journal-bookmark text-warning me-1"></i> <strong>Layanan Informasi</strong> digunakan untuk konsultasi/baca di tempat (berkas fisik tetap <em>Tersedia</em>).';
            }
        }
    }

    document.querySelectorAll('.input-bentuk-layanan').forEach(r => {
        r.addEventListener('change', evaluasiKesesuaianLayanan);
    });

    function getArsipDetail(idVal) {
        return arsipDetailMap[String(idVal)] || null;
    }

    function buildArsipObjFromEl(el, idVal) {
        const resolvedId = String(idVal ?? el.getAttribute('data-id') ?? '');
        const detail = getArsipDetail(resolvedId);
        if (detail) return { ...detail };

        return {
            id: resolvedId,
            kode: el.getAttribute('data-kode') || '',
            uraian: el.getAttribute('data-uraian') || '',
            informasi: el.getAttribute('data-informasi') || '',
            tahun: el.getAttribute('data-tahun') || '',
            lokasi: el.getAttribute('data-lokasi') || '-',
            asal: el.getAttribute('data-asal') || '-',
            status: el.getAttribute('data-status') || 'Tersedia',
        };
    }

    // --- 1.A PRATINJAU & MODAL DETAIL ARSIP SEBELUM MEMILIH ---
    const btnLihatDetailPicker = document.getElementById('btnLihatDetailPicker');
    const previewArsipPicker = document.getElementById('previewArsipPicker');
    let activeDetailArsipId = null;

    function syncPreviewPicker() {
        const selectedId = selectPicker?.value || '';
        if (btnLihatDetailPicker) btnLihatDetailPicker.disabled = !selectedId;

        if (!previewArsipPicker) return;
        const detail = selectedId ? getArsipDetail(selectedId) : null;
        if (!detail) {
            previewArsipPicker.classList.add('d-none');
            return;
        }

        document.getElementById('prevKode').textContent = detail.kode;
        document.getElementById('prevTahun').textContent = `Tahun ${detail.tahun}`;
        const prevStatus = document.getElementById('prevStatus');
        prevStatus.textContent = detail.status;
        prevStatus.className = detail.status === 'Tersedia'
            ? 'badge bg-success-subtle text-success border border-success-subtle'
            : 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        document.getElementById('prevKeamanan').textContent = detail.keamanan;
        document.getElementById('prevUraian').textContent = detail.uraian;
        const prevInfo = document.getElementById('prevInformasi');
        prevInfo.textContent = detail.informasi
            ? detail.informasi
            : 'Tidak ada catatan uraian informasi tambahan.';
        prevInfo.classList.toggle('fst-italic', !detail.informasi);
        document.getElementById('prevAsal').textContent = detail.asal;
        document.getElementById('prevKlasifikasi').textContent = detail.klasifikasi;
        document.getElementById('prevTanggal').textContent = detail.tanggal_surat;
        document.getElementById('prevLokasi').textContent = detail.lokasi;

        previewArsipPicker.classList.remove('d-none');
    }

    function bukaDetailArsip(idVal) {
        const detail = getArsipDetail(idVal);
        if (!detail) return;

        activeDetailArsipId = detail.id;
        document.getElementById('detArsipKode').textContent = detail.kode;
        const statusEl = document.getElementById('detArsipStatus');
        statusEl.textContent = detail.status;
        statusEl.className = detail.status === 'Tersedia'
            ? 'badge rounded-pill px-3 py-1 bg-success-subtle text-success border border-success-subtle'
            : 'badge rounded-pill px-3 py-1 bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        document.getElementById('detArsipKeamanan').textContent = detail.keamanan;
        document.getElementById('detArsipUraian').textContent = detail.uraian;
        const infoEl = document.getElementById('detArsipInformasi');
        infoEl.textContent = detail.informasi || 'Belum ada uraian informasi detail untuk berkas ini.';
        infoEl.classList.toggle('text-muted', !detail.informasi);
        infoEl.classList.toggle('fst-italic', !detail.informasi);
        document.getElementById('detArsipBidangPengelola').textContent = ': ' + detail.bidang_pengelola;
        document.getElementById('detArsipAsal').textContent = ': ' + detail.asal;
        document.getElementById('detArsipKlasifikasi').textContent = ': ' + detail.klasifikasi;
        const waktuParts = [detail.tahun + (detail.bulan ? ` (${detail.bulan})` : '')];
        if (detail.tanggal_surat && detail.tanggal_surat !== '-') waktuParts.push('Tgl Surat: ' + detail.tanggal_surat);
        document.getElementById('detArsipWaktu').textContent = ': ' + waktuParts.join(' • ');
        document.getElementById('detArsipLokasi').textContent = ': ' + detail.lokasi;
        document.getElementById('detArsipPerkembangan').textContent = ': ' + detail.perkembangan;
        document.getElementById('detArsipJra').textContent = ': ' + detail.jra;

        const btnTambah = document.getElementById('btnTambahDariDetail');
        const sudahAda = listArsipTerpilih.some(a => String(a.id) === String(detail.id));
        if (btnTambah) {
            btnTambah.disabled = sudahAda;
            btnTambah.innerHTML = sudahAda
                ? '<i class="bi bi-check2 me-1"></i> Sudah Ada di Surat'
                : '<i class="bi bi-plus-circle me-1"></i> Masukkan ke Surat';
        }

        const modalDetailEl = document.getElementById('modalDetailArsipPinjam');
        bootstrap.Modal.getOrCreateInstance(modalDetailEl).show();
    }

    if (selectPicker) {
        selectPicker.addEventListener('change', syncPreviewPicker);
    }
    if (btnLihatDetailPicker) {
        btnLihatDetailPicker.addEventListener('click', () => {
            if (selectPicker.value) bukaDetailArsip(selectPicker.value);
        });
    }
    document.getElementById('btnPreviewDetailModal')?.addEventListener('click', () => {
        if (selectPicker.value) bukaDetailArsip(selectPicker.value);
    });
    document.getElementById('btnPreviewTambah')?.addEventListener('click', () => {
        if (!selectPicker.value) return;
        const opt = selectPicker.options[selectPicker.selectedIndex];
        tambahKeKeranjang(buildArsipObjFromEl(opt, selectPicker.value));
        selectPicker.value = '';
        syncPreviewPicker();
    });
    document.getElementById('btnTambahDariDetail')?.addEventListener('click', () => {
        if (!activeDetailArsipId) return;
        const detail = getArsipDetail(activeDetailArsipId);
        if (detail) {
            tambahKeKeranjang({ ...detail });
            bootstrap.Modal.getInstance(document.getElementById('modalDetailArsipPinjam'))?.hide();
        }
    });
    document.getElementById('modalDetailArsipPinjam')?.addEventListener('hidden.bs.modal', () => {
        if (document.getElementById('modalKatalogArsip')?.classList.contains('show')) {
            document.body.classList.add('modal-open');
        }
    });
    document.querySelectorAll('.btn-detail-katalog').forEach(btn => {
        btn.addEventListener('click', function () {
            bukaDetailArsip(this.getAttribute('data-id'));
        });
    });

    document.getElementById('btnTambahArsipManual').addEventListener('click', function () {
        const selectedId = selectPicker.value;
        if (!selectedId) return;

        const opt = selectPicker.options[selectPicker.selectedIndex];
        tambahKeKeranjang(buildArsipObjFromEl(opt, selectedId));
        selectPicker.value = '';
        syncPreviewPicker();
    });

    // --- 1.B FITUR LIVE SEARCH CEPAT BERKAS ---
    const cariArsipInput = document.getElementById('cariArsipInput');
    const hasilCariArsipLive = document.getElementById('hasilCariArsipLive');

    const masterArsipList = [];
    for (let i = 1; i < selectPicker.options.length; i++) {
        const o = selectPicker.options[i];
        masterArsipList.push(buildArsipObjFromEl(o, o.value));
    }

    if (cariArsipInput && hasilCariArsipLive) {
        cariArsipInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            if (!query) {
                hasilCariArsipLive.classList.add('d-none');
                hasilCariArsipLive.innerHTML = '';
                return;
            }

            const matches = masterArsipList.filter(a =>
                a.kode.toLowerCase().includes(query) ||
                a.uraian.toLowerCase().includes(query) ||
                (a.informasi || '').toLowerCase().includes(query) ||
                a.asal.toLowerCase().includes(query) ||
                a.tahun.toString().includes(query) ||
                a.lokasi.toLowerCase().includes(query)
            ).slice(0, 8);

            if (matches.length === 0) {
                hasilCariArsipLive.innerHTML = '<div class="p-3 text-muted small text-center bg-white">Tidak ditemukan berkas yang cocok dengan kata kunci.</div>';
                hasilCariArsipLive.classList.remove('d-none');
                return;
            }

            hasilCariArsipLive.innerHTML = '';
            matches.forEach(a => {
                const row = document.createElement('div');
                row.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3 text-start bg-white gap-2';
                const infoSnippet = a.informasi
                    ? `<div class="text-secondary text-truncate" style="font-size: 0.74rem; max-width: 480px;">${escapeHtml(a.informasi)}</div>`
                    : '';
                row.innerHTML = `
                    <div class="me-2 overflow-hidden flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                            <span class="badge bg-primary-subtle text-primary font-monospace fw-bold">${escapeHtml(a.kode)}</span>
                            <span class="badge bg-light text-secondary border">Tahun ${escapeHtml(a.tahun)}</span>
                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(a.lokasi)}</small>
                        </div>
                        <div class="small fw-semibold text-slate-800 text-truncate" style="max-width: 480px;">${escapeHtml(a.uraian)}</div>
                        ${infoSnippet}
                        <div class="text-muted" style="font-size: 0.72rem;">Asal: ${escapeHtml(a.asal)}</div>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2 text-nowrap btn-live-detail"><i class="bi bi-eye"></i> Detail</button>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 text-nowrap btn-live-pilih"><i class="bi bi-plus"></i> Pilih</button>
                    </div>
                `;
                row.querySelector('.btn-live-detail').addEventListener('click', function (e) {
                    e.stopPropagation();
                    bukaDetailArsip(a.id);
                });
                row.querySelector('.btn-live-pilih').addEventListener('click', function () {
                    tambahKeKeranjang(a);
                    cariArsipInput.value = '';
                    hasilCariArsipLive.classList.add('d-none');
                    hasilCariArsipLive.innerHTML = '';
                });
                hasilCariArsipLive.appendChild(row);
            });
            hasilCariArsipLive.classList.remove('d-none');
        });

        document.addEventListener('click', function(e) {
            if (!cariArsipInput.contains(e.target) && !hasilCariArsipLive.contains(e.target)) {
                hasilCariArsipLive.classList.add('d-none');
            }
        });
    }

    // --- 1.C FITUR MODAL KATALOG BERKAS (FILTER & PILIH BANYAK) ---
    const filterTeksModal = document.getElementById('filterTeksModal');
    const filterTahunModal = document.getElementById('filterTahunModal');
    const checkAllModal = document.getElementById('checkAllModal');
    const barisKatalog = document.querySelectorAll('.baris-katalog');
    const labelTerpilihModal = document.getElementById('labelTerpilihModal');
    const btnTerapkanModal = document.getElementById('btnTerapkanModal');

    function filterTabelModal() {
        const teks = filterTeksModal ? filterTeksModal.value.toLowerCase().trim() : '';
        const tahun = filterTahunModal ? filterTahunModal.value : '';

        barisKatalog.forEach(row => {
            const kode = (row.getAttribute('data-kode') || '').toLowerCase();
            const uraian = (row.getAttribute('data-uraian') || '').toLowerCase();
            const informasi = (row.getAttribute('data-informasi') || '').toLowerCase();
            const lokasi = (row.getAttribute('data-lokasi') || '').toLowerCase();
            const rowTahun = row.getAttribute('data-tahun') || '';

            const cocokTeks = !teks || kode.includes(teks) || uraian.includes(teks) || informasi.includes(teks) || lokasi.includes(teks);
            const cocokTahun = !tahun || rowTahun === tahun;

            row.style.display = (cocokTeks && cocokTahun) ? '' : 'none';
        });
        updateCountModal();
    }

    if (filterTeksModal) filterTeksModal.addEventListener('input', filterTabelModal);
    if (filterTahunModal) filterTahunModal.addEventListener('change', filterTabelModal);

    function updateCountModal() {
        const checkedCount = document.querySelectorAll('.check-item-modal:checked').length;
        if (labelTerpilihModal) {
            labelTerpilihModal.textContent = `${checkedCount} Berkas Terpilih`;
        }
    }

    if (checkAllModal) {
        checkAllModal.addEventListener('change', function () {
            const isChecked = this.checked;
            barisKatalog.forEach(row => {
                if (row.style.display !== 'none') {
                    const cb = row.querySelector('.check-item-modal');
                    if (cb) cb.checked = isChecked;
                }
            });
            updateCountModal();
        });
    }

    document.querySelectorAll('.check-item-modal').forEach(cb => {
        cb.addEventListener('change', updateCountModal);
    });

    if (btnTerapkanModal) {
        btnTerapkanModal.addEventListener('click', function () {
            const checkedBoxes = document.querySelectorAll('.check-item-modal:checked');
            if (checkedBoxes.length === 0) {
                alert('Silakan centang minimal satu berkas arsip.');
                return;
            }

            checkedBoxes.forEach(cb => {
                const row = cb.closest('tr');
                if (row) {
                    const obj = buildArsipObjFromEl(row, row.getAttribute('data-id'));
                    tambahKeKeranjang(obj);
                    cb.checked = false;
                }
            });

            if (checkAllModal) checkAllModal.checked = false;
            updateCountModal();

            const modalEl = document.getElementById('modalKatalogArsip');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        });
    }

    function tambahKeKeranjang(arsip) {
        if (listArsipTerpilih.some(a => a.id == arsip.id)) {
            alert('Berkas arsip ini sudah ada di dalam surat.');
            return;
        }

        const layanan = getSelectedBentukLayanan();
        if (layanan === 'Fisik' && arsip.status && arsip.status !== 'Tersedia') {
            alert(`Arsip ${arsip.kode} saat ini berstatus "${arsip.status}" sehingga tidak dapat dipinjam secara fisik.`);
            return;
        }

        listArsipTerpilih.push(arsip);
        renderKeranjang();
    }

    function hapusDariKeranjang(id) {
        const idx = listArsipTerpilih.findIndex(a => a.id == id);
        if (idx !== -1) {
            listArsipTerpilih.splice(idx, 1);
            renderKeranjang();
        }
    }

    function renderKeranjang() {
        bodyTabel.innerHTML = '';
        if (listArsipTerpilih.length === 0) {
            bodyTabel.appendChild(emptyRow);
            evaluasiKesesuaianLayanan();
            return;
        }

        listArsipTerpilih.forEach(a => {
            const tr = document.createElement('tr');
            const infoPreview = a.informasi
                ? `<div class="text-secondary small" style="font-size: 0.74rem;">${escapeHtml(a.informasi.length > 110 ? a.informasi.slice(0, 110) + '...' : a.informasi)}</div>`
                : '';
            tr.innerHTML = `
                <td>
                    <div class="fw-bold text-primary font-monospace">${escapeHtml(a.kode)}</div>
                    <input type="hidden" name="arsip_ids[]" value="${escapeHtml(a.id)}">
                </td>
                <td>
                    <div class="fw-semibold text-slate-800">${escapeHtml(a.uraian)}</div>
                    ${infoPreview}
                    <small class="text-muted">Asal: ${escapeHtml(a.asal)}</small>
                </td>
                <td><span class="badge bg-light text-dark border">${escapeHtml(a.tahun)}</span></td>
                <td>${escapeHtml(a.lokasi)}</td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 rounded-pill me-1" onclick="bukaDetailArsip(${a.id})" title="Lihat Detail Berkas">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill" onclick="hapusDariKeranjang(${a.id})" title="Hapus dari Surat">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;
            bodyTabel.appendChild(tr);
        });
        evaluasiKesesuaianLayanan();
    }

    // --- 2. TOGGLE FORM PEMINJAM & JAMINAN ---
    const jaminanSelect = document.getElementById('jaminan_opsi');
    const wrapJaminanLainnya = document.getElementById('wrapJaminanLainnya');
    const jaminanLainnyaInput = document.getElementById('jaminan_lainnya');

    function syncJaminanCreate(val) {
        const isLainnya = val === 'Lainnya';
        wrapJaminanLainnya.classList.toggle('d-none', !isLainnya);
        if (!isLainnya) jaminanLainnyaInput.value = '';
    }

    if (jaminanSelect) {
        jaminanSelect.addEventListener('change', () => syncJaminanCreate(jaminanSelect.value));
    }

    const peminjamSelect = document.getElementById('peminjam_id');
    if (peminjamSelect) {
        peminjamSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const jam = opt ? (opt.getAttribute('data-jaminan') || '') : '';
            if (!jam) {
                jaminanSelect.value = '';
                syncJaminanCreate('');
                return;
            }
            const adaDiOpsi = Array.from(jaminanSelect.options).some(o => o.value === jam);
            if (adaDiOpsi) {
                jaminanSelect.value = jam;
                syncJaminanCreate(jam);
            } else {
                jaminanSelect.value = 'Lainnya';
                syncJaminanCreate('Lainnya');
                jaminanLainnyaInput.value = jam;
            }
        });
        if (peminjamSelect.value && !jaminanSelect.value) {
            peminjamSelect.dispatchEvent(new Event('change'));
        }
    }

    function togglePeminjamForm() {
        const isBaru = document.getElementById('tipe_baru').checked;
        document.getElementById('sectionPeminjamTerdaftar').classList.toggle('d-none', isBaru);
        const sectionBaru = document.getElementById('sectionPeminjamBaru');
        sectionBaru.classList.toggle('d-none', !isBaru);

        // Jika batal menambah peminjam baru (kembali ke Terdaftar), kosongkan seluruh input yang sempat diketik
        if (!isBaru) {
            sectionBaru.querySelectorAll('input').forEach(inp => { inp.value = ''; });
            sectionBaru.querySelectorAll('select').forEach(sel => { sel.selectedIndex = 0; });
            if (jaminanSelect) {
                jaminanSelect.value = '';
                syncJaminanCreate('');
            }
        }
        syncAlamatBaruRequirement();
    }

    // Auto-fill Petugas Layanan dari Akun Petugas Terdaftar
    const selectPetugasPreset = document.getElementById('selectPetugasPreset');
    if (selectPetugasPreset) {
        selectPetugasPreset.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) return;
            const nama = opt.getAttribute('data-nama') || '';
            const nip = opt.getAttribute('data-nip') || '';
            const bidang = opt.getAttribute('data-bidang') || '';

            const inpNama = document.getElementById('petugas_layanan_nama');
            const inpNip = document.getElementById('petugas_layanan_nip');
            const selBidang = document.getElementById('petugas_layanan_bidang');

            if (inpNama && nama) inpNama.value = nama;
            if (inpNip) inpNip.value = nip || '-';
            if (selBidang && bidang) selBidang.value = bidang;
        });
    }

    const jenisBaruSelect = document.getElementById('jenis_peminjam_baru');
    function syncAlamatBaruRequirement() {
        if (!jenisBaruSelect) return;
        const isBaru = document.getElementById('tipe_baru')?.checked;
        const isEksternal = jenisBaruSelect.value === 'Eksternal';
        const alamatBaru = document.getElementById('alamat_baru');
        const star = document.getElementById('alamatBaruWajib');
        if (alamatBaru) alamatBaru.required = !!(isBaru && isEksternal);
        if (star) star.classList.toggle('d-none', !isEksternal);
    }
    if (jenisBaruSelect) {
        jenisBaruSelect.addEventListener('change', syncAlamatBaruRequirement);
        syncAlamatBaruRequirement();
    }

    // --- 4. PERHITUNGAN LAMA PINJAM ---
    function hitungLamaPinjam() {
        const mulaiInput = document.getElementById('tanggal_mulai');
        const kembaliInput = document.getElementById('tanggal_kembali');
        if (!mulaiInput || !kembaliInput || !mulaiInput.value || !kembaliInput.value) return;

        const mulai = new Date(mulaiInput.value);
        const kembali = new Date(kembaliInput.value);
        const maxHari = getSelectedMaxHari();
        if (kembali >= mulai) {
            const diffTime = Math.abs(kembali - mulai);
            const diffDays = Math.max(1, Math.round(diffTime / (1000 * 60 * 60 * 24)));
            document.getElementById('lamaPinjamTampil').value = diffDays + ' Hari';

            const pesan = document.getElementById('pesanValidasiDurasi');
            if (pesan) {
                pesan.textContent = `Lama pinjam (${diffDays} hari) melebihi batas maksimal bidang (${maxHari} hari)!`;
                pesan.classList.toggle('d-none', diffDays <= maxHari);
            }
        }
    }

    // --- 5. TANDA TANGAN DIGITAL (SIGNATURE PAD & NATIVE FALLBACK) ---
    const canvasTtd = document.getElementById('padTtd');
    let signaturePad = null;
    let nativeDrawing = false;
    let hasNativeDrawn = false;

    function initSignaturePad() {
        if (!canvasTtd) return;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvasTtd.getBoundingClientRect();
        const width = rect.width || canvasTtd.offsetWidth || 400;
        const height = rect.height || canvasTtd.offsetHeight || 160;

        canvasTtd.width = width * ratio;
        canvasTtd.height = height * ratio;
        const ctx = canvasTtd.getContext("2d");
        ctx.scale(ratio, ratio);

        if (typeof SignaturePad !== 'undefined') {
            signaturePad = new SignaturePad(canvasTtd, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 1.5,
                maxWidth: 3.5
            });

            signaturePad.addEventListener('endStroke', function () {
                if (!signaturePad.isEmpty()) {
                    document.getElementById('ttd_digital_data').value = signaturePad.toDataURL('image/png');
                    document.getElementById('statusTtdOk').classList.remove('d-none');
                }
            });
        } else {
            // Fallback native canvas drawing jika CDN SignaturePad tidak terhubung
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            function getCanvasPos(e) {
                const r = canvasTtd.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - r.left,
                    y: clientY - r.top
                };
            }

            canvasTtd.addEventListener('mousedown', function (e) {
                nativeDrawing = true;
                hasNativeDrawn = true;
                const pos = getCanvasPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            });

            canvasTtd.addEventListener('mousemove', function (e) {
                if (!nativeDrawing) return;
                const pos = getCanvasPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            });

            window.addEventListener('mouseup', function () {
                if (nativeDrawing) {
                    nativeDrawing = false;
                    document.getElementById('ttd_digital_data').value = canvasTtd.toDataURL('image/png');
                    document.getElementById('statusTtdOk').classList.remove('d-none');
                }
            });

            canvasTtd.addEventListener('touchstart', function (e) {
                nativeDrawing = true;
                hasNativeDrawn = true;
                const pos = getCanvasPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                e.preventDefault();
            }, { passive: false });

            canvasTtd.addEventListener('touchmove', function (e) {
                if (!nativeDrawing) return;
                const pos = getCanvasPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                e.preventDefault();
            }, { passive: false });

            window.addEventListener('touchend', function () {
                if (nativeDrawing) {
                    nativeDrawing = false;
                    document.getElementById('ttd_digital_data').value = canvasTtd.toDataURL('image/png');
                    document.getElementById('statusTtdOk').classList.remove('d-none');
                }
            });
        }
    }

    setTimeout(initSignaturePad, 100);

    document.getElementById('btnClearTtd').addEventListener('click', function () {
        if (signaturePad) {
            signaturePad.clear();
        } else if (canvasTtd) {
            const ctx = canvasTtd.getContext('2d');
            ctx.clearRect(0, 0, canvasTtd.width, canvasTtd.height);
            hasNativeDrawn = false;
        }
        document.getElementById('ttd_digital_data').value = '';
        document.getElementById('statusTtdOk').classList.add('d-none');
    });

    function toggleTtdMetode() {
        const isDigital = document.getElementById('ttd_digital').checked;
        document.getElementById('panelTtdDigital').classList.toggle('d-none', !isDigital);
        document.getElementById('panelTtdManual').classList.toggle('d-none', isDigital);
    }

    // --- 5. PEMINDAI DOKUMEN & BUKTI SERAH TERIMA (OPENCV.JS AUTO PERSPECTIVE CORRECTION) ---
    const videoBukti = document.getElementById('videoBukti');
    const overlayBuktiRealtime = document.getElementById('overlayBuktiRealtime');
    const badgeLiveBukti = document.getElementById('badgeLiveBukti');
    const previewBukti = document.getElementById('previewFotoBukti');
    const placeholderBukti = document.getElementById('placeholderBukti');
    const badgeBuktiCv = document.getElementById('badgeBuktiCv');
    const panelFilterBukti = document.getElementById('panelFilterBukti');
    const panelEditSudutBukti = document.getElementById('panelEditSudutBukti');
    const editorSudutBukti = document.getElementById('editorSudutBukti');
    const btnMulai = document.getElementById('btnMulaiKameraBukti');
    const btnSnap = document.getElementById('btnSnapBukti');

    const rawBuktiCanvas = document.createElement('canvas');
    const liveFrameCanvas = document.createElement('canvas');
    const warpedBuktiCanvas = document.createElement('canvas');

    let buktiStream = null;
    let buktiLiveTimer = null;
    let buktiFilterMode = 'enhance';

    const buktiCornerEditor = new SipaDocScanner.CornerEditor(editorSudutBukti, (newCorners) => {
        terapkanWarpBukti(newCorners);
    });

    function hentikanKameraBukti() {
        if (buktiLiveTimer) {
            clearInterval(buktiLiveTimer);
            buktiLiveTimer = null;
        }
        if (buktiStream) {
            buktiStream.getTracks().forEach(t => t.stop());
            buktiStream = null;
        }
        videoBukti.style.display = 'none';
        overlayBuktiRealtime.style.display = 'none';
        badgeLiveBukti.classList.add('d-none');
    }

    function prosesGambarKeScanner(sumberInput) {
        SipaDocScanner.ensureLoaded().then(() => {
            const detectRes = SipaDocScanner.detectDocument(rawBuktiCanvas);
            buktiCornerEditor.setImageAndCorners(rawBuktiCanvas, detectRes.corners);

            badgeBuktiCv.textContent = detectRes.detected ? '4 Sudut Terdeteksi Otomatis' : 'Auto-Warp (Klik Atur Sudut jika perlu)';
            badgeBuktiCv.className = detectRes.detected
                ? 'badge bg-success-subtle text-success border border-success-subtle'
                : 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';

            document.getElementById('foto_bukti_sumber').value = sumberInput;
            panelFilterBukti.classList.remove('d-none');
            terapkanWarpBukti(detectRes.corners);
        });
    }

    function terapkanWarpBukti(corners) {
        if (rawBuktiCanvas.width === 0) return;
        const activeCorners = corners || buktiCornerEditor.getCorners();
        SipaDocScanner.warpDocument(rawBuktiCanvas, activeCorners, buktiFilterMode, warpedBuktiCanvas, 1400);
        const dataUrl = SipaDocScanner.toCompressedJpeg(warpedBuktiCanvas, 500);

        document.getElementById('foto_bukti_data').value = dataUrl;
        previewBukti.src = dataUrl;
        previewBukti.style.display = 'block';
        placeholderBukti.style.display = 'none';
        videoBukti.style.display = 'none';
        overlayBuktiRealtime.style.display = 'none';
        document.getElementById('btnHapusFotoBukti')?.classList.remove('d-none');
    }

    btnMulai.addEventListener('click', function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Peramban tidak mendukung kamera. Gunakan Unggah Foto.');
            return;
        }
        SipaDocScanner.ensureLoaded();
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } })
            .then(s => {
                buktiStream = s;
                videoBukti.srcObject = s;
                videoBukti.style.display = 'block';
                overlayBuktiRealtime.style.display = 'block';
                badgeLiveBukti.classList.remove('d-none');
                previewBukti.style.display = 'none';
                placeholderBukti.style.display = 'none';
                panelEditSudutBukti.classList.add('d-none');
                btnMulai.classList.add('d-none');
                btnSnap.classList.remove('d-none');

                buktiLiveTimer = setInterval(() => {
                    if (!videoBukti.videoWidth) return;
                    const w = videoBukti.videoWidth;
                    const h = videoBukti.videoHeight;
                    if (overlayBuktiRealtime.width !== w || overlayBuktiRealtime.height !== h) {
                        overlayBuktiRealtime.width = w;
                        overlayBuktiRealtime.height = h;
                    }
                    liveFrameCanvas.width = w;
                    liveFrameCanvas.height = h;
                    liveFrameCanvas.getContext('2d').drawImage(videoBukti, 0, 0, w, h);

                    const liveRes = SipaDocScanner.detectDocument(liveFrameCanvas);
                    SipaDocScanner.drawRealtimeOverlay(overlayBuktiRealtime, liveRes.corners, liveRes.detected);
                    badgeLiveBukti.textContent = liveRes.detected ? '4 Sudut Terdeteksi!' : 'Arahkan ke dokumen...';
                    badgeLiveBukti.className = liveRes.detected
                        ? 'badge bg-success text-white position-absolute top-0 start-0 m-1'
                        : 'badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-1';
                }, 250);
            })
            .catch(err => {
                alert('Tidak dapat menyalakan kamera: ' + err.message);
            });
    });

    btnSnap.addEventListener('click', function () {
        if (!videoBukti.videoWidth) return;
        rawBuktiCanvas.width = videoBukti.videoWidth || 640;
        rawBuktiCanvas.height = videoBukti.videoHeight || 480;
        rawBuktiCanvas.getContext('2d').drawImage(videoBukti, 0, 0);

        hentikanKameraBukti();
        btnSnap.classList.add('d-none');
        btnMulai.classList.remove('d-none');
        btnMulai.innerHTML = '<i class="bi bi-webcam me-1"></i> Pindai Ulang';

        prosesGambarKeScanner('Kamera CV');
    });

    document.getElementById('inputFileBukti').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        hentikanKameraBukti();
        btnSnap.classList.add('d-none');
        btnMulai.classList.remove('d-none');

        const img = new Image();
        img.onload = function () {
            const maxDim = 1600;
            const scale = Math.min(1, maxDim / Math.max(img.naturalWidth, img.naturalHeight));
            rawBuktiCanvas.width = Math.round(img.naturalWidth * scale);
            rawBuktiCanvas.height = Math.round(img.naturalHeight * scale);
            rawBuktiCanvas.getContext('2d').drawImage(img, 0, 0, rawBuktiCanvas.width, rawBuktiCanvas.height);
            URL.revokeObjectURL(img.src);
            prosesGambarKeScanner('Unggah CV');
        };
        img.src = URL.createObjectURL(file);
    });

    document.querySelectorAll('input[name="buktiFilter"]').forEach((radio) => {
        radio.addEventListener('change', (e) => {
            buktiFilterMode = e.target.value;
            terapkanWarpBukti(buktiCornerEditor.getCorners());
        });
    });

    document.getElementById('btnToggleSudutBukti').addEventListener('click', function () {
        panelEditSudutBukti.classList.toggle('d-none');
        if (!panelEditSudutBukti.classList.contains('d-none')) {
            buktiCornerEditor.draw();
        }
    });

    document.getElementById('btnHapusFotoBukti')?.addEventListener('click', function () {
        hentikanKameraBukti();
        rawBuktiCanvas.width = 0;
        rawBuktiCanvas.height = 0;
        document.getElementById('foto_bukti_data').value = '';
        document.getElementById('inputFileBukti').value = '';
        previewBukti.src = '';
        previewBukti.style.display = 'none';
        placeholderBukti.style.display = 'flex';
        panelFilterBukti.classList.add('d-none');
        panelEditSudutBukti.classList.add('d-none');
        this.classList.add('d-none');
        btnMulai.innerHTML = '<i class="bi bi-webcam me-1"></i> Pindai Kamera';
        badgeBuktiCv.textContent = 'Auto-Crop & Luruskan';
        badgeBuktiCv.className = 'badge bg-light text-secondary border';
    });

    // Terima hasil scan dari halaman Pemindai Dokumen (/pemindai) jika ada
    try {
        const preScanned = sessionStorage.getItem('sipa_scanned_dokumen');
        const preSumber = sessionStorage.getItem('sipa_scanned_sumber') || 'Kamera CV';
        const preJenis = sessionStorage.getItem('sipa_scanned_jenis') || '';
        if (preScanned) {
            document.getElementById('foto_bukti_data').value = preScanned;
            document.getElementById('foto_bukti_sumber').value = preSumber;
            if (preJenis && document.getElementById('foto_bukti_jenis')) {
                document.getElementById('foto_bukti_jenis').value = preJenis;
            }
            previewBukti.src = preScanned;
            previewBukti.style.display = 'block';
            placeholderBukti.style.display = 'none';
            badgeBuktiCv.textContent = 'Hasil Scan Dokumen (OpenCV)';
            badgeBuktiCv.className = 'badge bg-success-subtle text-success border border-success-subtle';
            document.getElementById('btnHapusFotoBukti')?.classList.remove('d-none');
            sessionStorage.removeItem('sipa_scanned_dokumen');
            sessionStorage.removeItem('sipa_scanned_sumber');
            sessionStorage.removeItem('sipa_scanned_jenis');
        }
    } catch (_) {}

    // Pre-pilih arsip dari query ?arsip_id=... atau old('arsip_ids')
    const initialArsipIds = @json(old('arsip_ids', request()->filled('arsip_id') ? [request('arsip_id')] : []));
    if (Array.isArray(initialArsipIds) && initialArsipIds.length > 0) {
        initialArsipIds.forEach(reqId => {
            const found = masterArsipList.find(a => String(a.id) === String(reqId));
            if (found && !listArsipTerpilih.some(x => String(x.id) === String(found.id))) {
                listArsipTerpilih.push(found);
            }
        });
        renderKeranjang();
    }

    // Validasi Sebelum Submit
    document.getElementById('formPeminjaman').addEventListener('submit', function (e) {
        if (listArsipTerpilih.length === 0) {
            e.preventDefault();
            alert('Harap pilih minimal satu berkas arsip yang akan dimasukkan ke surat peminjaman.');
            document.getElementById('cariArsipInput')?.focus();
            return false;
        }

        const tipePeminjam = document.querySelector('input[name="tipe_peminjam"]:checked')?.value || 'terdaftar';
        if (tipePeminjam === 'terdaftar') {
            const peminjamVal = document.getElementById('peminjam_id')?.value;
            if (!peminjamVal) {
                e.preventDefault();
                alert('Harap pilih nama peminjam terdaftar terlebih dahulu, atau ubah opsi ke "Catat Peminjam Baru".');
                document.getElementById('peminjam_id')?.focus();
                return false;
            }
        } else {
            const namaBaru = document.querySelector('input[name="nama_peminjam"]')?.value.trim();
            const hpBaru = document.querySelector('input[name="no_hp"]')?.value.trim();
            const instansiBaru = document.querySelector('input[name="instansi_unit"]')?.value.trim();
            if (!namaBaru || !hpBaru || !instansiBaru) {
                e.preventDefault();
                alert('Harap lengkapi Identitas Peminjam Baru (Nama Lengkap, Nomor HP, dan Instansi/Unit Kerja).');
                return false;
            }
        }

        const mulaiVal = document.getElementById('tanggal_mulai')?.value;
        const kembaliVal = document.getElementById('tanggal_kembali')?.value;
        if (!mulaiVal || !kembaliVal) {
            e.preventDefault();
            alert('Tanggal mulai dan tanggal kembali (jatuh tempo) wajib diisi.');
            return false;
        }

        const tglMulai = new Date(mulaiVal);
        const tglKembali = new Date(kembaliVal);
        if (tglKembali < tglMulai) {
            e.preventDefault();
            alert('Tanggal pengembalian (jatuh tempo) tidak boleh mendahului tanggal mulai pinjam.');
            return false;
        }

        const maxHari = getSelectedMaxHari();
        const diffDays = Math.max(1, Math.round(Math.abs(tglKembali - tglMulai) / (1000 * 60 * 60 * 24)));
        if (diffDays > maxHari) {
            e.preventDefault();
            alert(`Lama pinjam (${diffDays} hari) melebihi batas maksimal bidang ini (${maxHari} hari). Harap sesuaikan tanggal pengembalian.`);
            return false;
        }

        // Cek tanda tangan digital: jika digoreskan di layar, rekam ke data input.
        const ttdInput = document.getElementById('ttd_digital_data');
        if (signaturePad && !signaturePad.isEmpty()) {
            ttdInput.value = signaturePad.toDataURL('image/png');
        } else if (!signaturePad && hasNativeDrawn && canvasTtd) {
            ttdInput.value = canvasTtd.toDataURL('image/png');
        }

        // Jika tidak diisi di layar pendaftaran, otomatis beralih ke tanda tangan manual (tanda tangan basah)
        if (!ttdInput.value) {
            const ttdManualRadio = document.getElementById('ttd_manual');
            if (ttdManualRadio) ttdManualRadio.checked = true;
        }

        // Berikan visual loading pada tombol submit agar tidak terjadi double submit
        const btnSubmit = document.getElementById('btnSubmitSurat');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan Surat...';
        }
    });

    hitungLamaPinjam();
</script>
@endpush
