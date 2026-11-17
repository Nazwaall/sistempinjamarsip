@extends('layouts.app')

@section('title', 'Data Berkas Arsip')

@section('content')
<div class="row g-3">
    <!-- Hero Header & Tombol Aksi -->
    <div class="col-12">
        <div class="sipa-page-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="sipa-kicker">INVENTARIS &amp; DAFTAR ARSIP</div>
                <h3 class="fw-bold mb-1 text-slate-800">Data Berkas Arsip</h3>
                <p class="text-muted small mb-0">Kelola daftar berkas fisik aktif unit pengolah, status ketersediaan, dan impor Excel dinas.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('arsip.import') }}" class="btn btn-outline-success btn-sm rounded-3 px-3 py-2 fw-semibold bg-white d-inline-flex align-items-center">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Impor Excel
                </a>
                <a href="{{ route('arsip.create') }}" class="btn btn-navy btn-sm rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Berkas
                </a>
            </div>
        </div>
    </div>

    <!-- Kotak Filter & Pencarian -->
    <div class="col-12">
        <div class="sipa-filter-card">
            <form action="{{ route('arsip.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Cari uraian, kode, pencipta, lokasi..." value="{{ request('q') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="tahun" class="form-select form-select-sm">
                        <option value="">Tahun</option>
                        @foreach ($tahunList as $th)
                            <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Status</option>
                        <option value="Tersedia" {{ request('status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="Dipinjam" {{ request('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="Rusak" {{ request('status') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                        <option value="Hilang" {{ request('status') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->hasAny(['q', 'tahun', 'bulan', 'status', 'unit_pengolah_id']))
                        <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Toolbar Aksi Massal (Batch Selection) -->
    @if (!$arsipList->isEmpty())
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check mb-0">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input" style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold" for="selectAllCheckbox" style="cursor: pointer;">
                                Pilih Semua di Halaman Ini
                            </label>
                        </div>
                        <span id="selectedCountBadge" class="badge bg-primary rounded-pill px-3 py-1 d-none" style="font-size: 0.78rem;">
                            0 berkas dipilih
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" id="btnHapusTerpilih" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm" disabled data-bs-toggle="modal" data-bs-target="#modalHapusTerpilih">
                            <i class="bi bi-check2-square me-1"></i> Hapus Berkas Terpilih
                        </button>
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalHapusSemua">
                            <i class="bi bi-trash3-fill me-1"></i> Kosongkan / Hapus Semua Arsip
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Daftar Berkas Arsip -->
    <div class="col-12">
        <div class="card border-0 rounded-3 shadow-sm overflow-hidden">
            @if ($arsipList->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-folder-x fs-1 text-secondary mb-2 d-block"></i>
                    <h6 class="text-secondary fw-semibold">Tidak Ada Data Berkas Arsip</h6>
                    <p class="text-muted small mb-3">Belum ada berkas arsip yang cocok dengan pencarian atau filter.</p>
                    <a href="{{ route('arsip.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">Tambah Arsip Sekarang</a>
                </div>
            @else
                <!-- Tabel Desktop -->
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="ps-3">
                                    <input type="checkbox" id="selectAllHeader" class="form-check-input" style="cursor: pointer;" title="Pilih Semua">
                                </th>
                                <th style="width: 50px;">No.</th>
                                <th>Kode Berkas</th>
                                <th>Uraian Berkas</th>
                                <th>Tahun/Bulan</th>
                                <th>Lokasi Simpan</th>
                                <th>Keamanan</th>
                                <th style="width: 90px;">Status</th>
                                <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($arsipList as $index => $arsip)
                                <tr>
                                    <td class="ps-3">
                                        <input type="checkbox" class="form-check-input arsip-checkbox" value="{{ $arsip->id }}" data-kode="{{ $arsip->kode_internal }}" style="cursor: pointer;">
                                    </td>
                                    <td class="text-muted">{{ $arsipList->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $arsip->kode_internal }}</div>
                                        <small class="text-muted d-block">{{ $arsip->kode_klasifikasi ?: '-' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('arsip.show', $arsip) }}" class="fw-semibold text-slate-800 text-decoration-none hover-primary d-block">
                                            {{ $arsip->uraian_berkas }}
                                        </a>
                                        <small class="text-muted d-block" style="font-size: 0.74rem;">
                                            <i class="bi bi-building me-1"></i>Pencipta: {{ $arsip->unit_pengolah_asal ?: ($arsip->unitPengolah?->nama ?? '-') }}
                                        </small>
                                        @if ($arsip->uraian_informasi)
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;">
                                                {{ $arsip->uraian_informasi }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $arsip->tahun }}</span>
                                        @if ($arsip->bulan)
                                            <small class="text-muted d-block">Bulan {{ $arsip->bulan }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $arsip->lokasi_simpan ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $arsip->klasifikasi_keamanan === 'Biasa/Terbuka' ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger' }} rounded-pill">
                                            {{ $arsip->klasifikasi_keamanan }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($arsip->status_ketersediaan === 'Tersedia')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Tersedia
                                            </span>
                                        @elseif ($arsip->status_ketersediaan === 'Dipinjam')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                                <i class="bi bi-clock-history me-1"></i> Dipinjam
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                {{ $arsip->status_ketersediaan }}
                                            </span>
                                        @endif
                                        @if ($arsip->skor_integritas_terakhir !== null)
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">
                                                    <i class="bi bi-cpu text-primary me-1"></i>Fisik: {{ $arsip->skor_integritas_terakhir }}%
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="action-btn-group">
                                            <a href="{{ route('arsip.show', $arsip) }}" class="btn-action btn-action-info" title="Detail &amp; Riwayat">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('arsip.edit', $arsip) }}" class="btn-action btn-action-warning" title="Sunting Berkas">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('arsip.destroy', $arsip) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus arsip {{ $arsip->kode_internal }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-action-danger" title="Hapus Berkas">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Kartu Mobile (FR-40) -->
                <div class="d-lg-none p-3">
                    @foreach ($arsipList as $arsip)
                        <div class="card border rounded-3 p-3 mb-2 shadow-sm position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input arsip-checkbox" value="{{ $arsip->id }}" data-kode="{{ $arsip->kode_internal }}" style="cursor: pointer;">
                                    <span class="fw-bold text-primary small">{{ $arsip->kode_internal }}</span>
                                </div>
                                @if ($arsip->status_ketersediaan === 'Tersedia')
                                    <span class="badge bg-success-subtle text-success rounded-pill">Tersedia</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $arsip->status_ketersediaan }}</span>
                                @endif
                            </div>
                            <h6 class="fw-semibold mb-1 text-slate-800">{{ $arsip->uraian_berkas }}</h6>
                            <div class="text-muted small mb-2">
                                <i class="bi bi-building me-1"></i> {{ $arsip->unit_pengolah_asal ?: ($arsip->unitPengolah?->nama ?? '-') }} &bull;
                                <i class="bi bi-geo-alt me-1"></i> {{ $arsip->lokasi_simpan ?: 'Lokasi belum diisi' }} &bull;
                                <i class="bi bi-calendar3 me-1"></i> Tahun {{ $arsip->tahun }}
                            </div>
                            <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                <a href="{{ route('arsip.show', $arsip) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                                <a href="{{ route('arsip.edit', $arsip) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-pencil me-1"></i> Sunting
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Paginasi -->
                <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Menampilkan {{ $arsipList->firstItem() }} - {{ $arsipList->lastItem() }} dari total {{ $arsipList->total() }} berkas
                    </small>
                    {{ $arsipList->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Terpilih -->
<div class="modal fade" id="modalHapusTerpilih" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('arsip.bulk-destroy') }}" method="POST" id="formHapusTerpilih">
                @csrf
                <input type="hidden" name="aksi" value="pilihan">
                <div id="hiddenSelectedInputs"></div>

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold fs-6"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Berkas Terpilih</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-2">Anda akan menghapus <strong id="modalSelectedCountText">0 berkas</strong> yang dipilih.</p>
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Berkas yang belum pernah dipinjam akan <strong>dihapus permanen</strong> dari basis data Supabase. Berkas yang sedang dipinjam tidak akan dihapus.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-semibold">Ya, Hapus Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Semua Arsip -->
<div class="modal fade" id="modalHapusSemua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('arsip.bulk-destroy') }}" method="POST" id="formHapusSemua">
                @csrf
                <input type="hidden" name="aksi" value="semua">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold fs-6"><i class="bi bi-shield-slash-fill text-danger me-2"></i>Kosongkan Seluruh Data Arsip</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger py-2 small mb-3">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> <strong>Peringatan Kritis:</strong> Tindakan ini akan menghapus <strong>seluruh data arsip</strong> di unit Anda secara permanen dari basis data Supabase.
                    </div>
                    <p class="small text-muted mb-3">
                        Ketik kata <strong class="text-danger">HAPUS</strong> pada kotak di bawah untuk mengaktifkan tombol konfirmasi:
                    </p>
                    <input type="text" id="inputKonfirmasiSemua" class="form-control text-center fw-bold" placeholder="Ketik HAPUS di sini..." autocomplete="off">
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnConfirmSubmitSemua" class="btn btn-danger btn-sm rounded-pill px-4 fw-semibold" disabled>
                        <i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Semua Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectAllHeader = document.getElementById('selectAllHeader');
    const rowCheckboxes = document.querySelectorAll('.arsip-checkbox');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const btnHapusTerpilih = document.getElementById('btnHapusTerpilih');
    const modalSelectedCountText = document.getElementById('modalSelectedCountText');
    const hiddenSelectedInputs = document.getElementById('hiddenSelectedInputs');

    function updateSelectionState() {
        const checkedList = document.querySelectorAll('.arsip-checkbox:checked');
        const count = checkedList.length;

        if (count > 0) {
            selectedCountBadge.textContent = count + ' berkas dipilih';
            selectedCountBadge.classList.remove('d-none');
            btnHapusTerpilih.removeAttribute('disabled');
        } else {
            selectedCountBadge.classList.add('d-none');
            btnHapusTerpilih.setAttribute('disabled', 'disabled');
        }

        modalSelectedCountText.textContent = count + ' berkas';

        // Update hidden inputs for form submit
        hiddenSelectedInputs.innerHTML = '';
        checkedList.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            hiddenSelectedInputs.appendChild(input);
        });

        const allChecked = count > 0 && count === rowCheckboxes.length;
        if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
        if (selectAllHeader) selectAllHeader.checked = allChecked;
    }

    function toggleAll(checked) {
        rowCheckboxes.forEach(cb => {
            cb.checked = checked;
        });
        updateSelectionState();
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            toggleAll(this.checked);
        });
    }

    if (selectAllHeader) {
        selectAllHeader.addEventListener('change', function () {
            toggleAll(this.checked);
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectionState);
    });

    // Validasi pengetikan konfirmasi HAPUS pada modal hapus semua
    const inputKonfirmasi = document.getElementById('inputKonfirmasiSemua');
    const btnConfirmSubmitSemua = document.getElementById('btnConfirmSubmitSemua');

    if (inputKonfirmasi && btnConfirmSubmitSemua) {
        inputKonfirmasi.addEventListener('input', function () {
            if (this.value.trim().toUpperCase() === 'HAPUS') {
                btnConfirmSubmitSemua.removeAttribute('disabled');
            } else {
                btnConfirmSubmitSemua.setAttribute('disabled', 'disabled');
            }
        });
    }
});
</script>
@endpush
