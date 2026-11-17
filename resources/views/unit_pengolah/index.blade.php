@extends('layouts.app')

@section('title', 'Unit Pengolah (Bidang)')

@section('content')
<div class="sipa-page-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
    <div>
        <div class="sipa-kicker">MASTER ORGANISASI</div>
        <h3 class="fw-bold mb-1 text-slate-800">Unit Pengolah (Bidang)</h3>
        <p class="text-muted small mb-0">Kelola daftar bidang kerja, kepala bidang, dan batas maksimal durasi peminjaman.</p>
    </div>
    <button type="button" class="btn btn-navy btn-sm rounded-3 px-3 py-2 shadow-sm flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalTambahUnit">
        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Bidang Baru
    </button>
</div>

<!-- Tabel Unit Pengolah -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-3" style="width: 50px;">No</th>
                    <th>Kode Bidang</th>
                    <th>Nama Unit Pengolah</th>
                    <th>Kepala Bidang</th>
                    <th class="text-center">Durasi Pinjam</th>
                    <th class="text-center">Jumlah Arsip</th>
                    <th class="text-center">Petugas</th>
                    <th class="text-center">Status</th>
                    <th class="text-end pe-3" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $idx => $u)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $idx + 1 }}</td>
                        <td>
                            <span class="badge bg-primary fs-6 px-2 py-1 font-monospace">{{ $u->kode }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-slate-800">{{ $u->nama }}</div>
                        </td>
                        <td class="small">
                            <div class="fw-semibold">{{ $u->kepala_nama }}</div>
                            <div class="text-muted small">NIP. {{ $u->kepala_nip ?: '-' }}</div>
                        </td>
                        <td class="text-center small">
                            <span class="badge bg-light text-dark border">Standar {{ $u->lama_pinjam_standar }} Hari</span>
                            <div class="text-muted small">Maks. {{ $u->lama_pinjam_maks }} Hari</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-primary border rounded-pill px-2 py-1">
                                {{ $u->arsip_count }} berkas
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1">
                                {{ $u->users_count }} akun
                            </span>
                        </td>
                        <td class="text-center">
                            @if($u->aktif)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Aktif</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="action-btn-group">
                                <button type="button" class="btn-action btn-action-warning" data-bs-toggle="modal" data-bs-target="#modalEditUnit{{ $u->id }}" title="Sunting">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('unit-pengolah.destroy', $u) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus/menonaktifkan unit ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-danger" title="Hapus / Nonaktifkan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit Unit -->
                    <div class="modal fade" id="modalEditUnit{{ $u->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('unit-pengolah.update', $u) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold fs-6"><i class="bi bi-pencil-square text-warning me-2"></i>Sunting Unit Pengolah</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-4">
                                                <label class="form-label small fw-semibold">Kode <span class="text-danger">*</span></label>
                                                <input type="text" name="kode" class="form-control form-control-sm text-uppercase" value="{{ $u->kode }}" required maxlength="10">
                                            </div>
                                            <div class="col-8">
                                                <label class="form-label small fw-semibold">Nama Bidang <span class="text-danger">*</span></label>
                                                <input type="text" name="nama" class="form-control form-control-sm" value="{{ $u->nama }}" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nama Kepala Bidang <span class="text-danger">*</span></label>
                                            <input type="text" name="kepala_nama" class="form-control form-control-sm" value="{{ $u->kepala_nama }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">NIP Kepala Bidang</label>
                                            <input type="text" name="kepala_nip" class="form-control form-control-sm" value="{{ $u->kepala_nip }}">
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Lama Pinjam Standar (hari)</label>
                                                <input type="number" name="lama_pinjam_standar" class="form-control form-control-sm" value="{{ $u->lama_pinjam_standar }}" min="1" max="30" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Lama Pinjam Maksimal (hari)</label>
                                                <input type="number" name="lama_pinjam_maks" class="form-control form-control-sm" value="{{ $u->lama_pinjam_maks }}" min="1" max="30" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Status Operasional</label>
                                            <select name="aktif" class="form-select form-select-sm">
                                                <option value="1" {{ $u->aktif ? 'selected' : '' }}>Aktif</option>
                                                <option value="0" {{ !$u->aktif ? 'selected' : '' }}>Nonaktif</option>
                                            </select>
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
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">Belum ada unit pengolah.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Unit -->
<div class="modal fade" id="modalTambahUnit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('unit-pengolah.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Bidang Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control form-control-sm text-uppercase" placeholder="ARS" required maxlength="10">
                            <small class="text-muted" style="font-size: 0.7rem;">Contoh: ARS, SEK, PERP</small>
                        </div>
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Nama Bidang <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control form-control-sm" placeholder="Bidang Pengelolaan Arsip" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kepala Bidang <span class="text-danger">*</span></label>
                        <input type="text" name="kepala_nama" class="form-control form-control-sm" placeholder="Nama lengkap dan gelar" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">NIP Kepala Bidang</label>
                        <input type="text" name="kepala_nip" class="form-control form-control-sm" placeholder="1980xxxxxxxxxxxx">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Durasi Pinjam Standar</label>
                            <input type="number" name="lama_pinjam_standar" class="form-control form-control-sm" value="7" min="1" max="30" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Durasi Pinjam Maksimal</label>
                            <input type="number" name="lama_pinjam_maks" class="form-control form-control-sm" value="7" min="1" max="30" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill">Simpan Bidang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
