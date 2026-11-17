@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Petugas')

@section('content')
<div class="sipa-page-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
    <div>
        <div class="sipa-kicker">MASTER DATA AKUN</div>
        <h3 class="fw-bold mb-1 text-slate-800">Manajemen Pengguna &amp; Petugas</h3>
        <p class="text-muted small mb-0">Kelola akun Super Admin / Kepala Bidang (Kabid), Operator Bidang, serta permohonan reset kata sandi.</p>
    </div>
    <button type="button" class="btn btn-navy btn-sm rounded-3 px-3 py-2 shadow-sm flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Akun Baru
    </button>
</div>

@if(isset($resetRequests) && $resetRequests->isNotEmpty())
    <div class="card border-warning shadow-sm rounded-3 mb-3">
        <div class="card-header bg-warning-subtle text-warning-emphasis border-0 py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold small"><i class="bi bi-key-fill me-1"></i> Permohonan Lupa Password ({{ $resetRequests->count() }})</span>
            <small class="text-muted">Segera setel password baru untuk petugas yang mengajukan reset</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-3">Akun</th>
                        <th>Catatan Pemohon</th>
                        <th>Waktu</th>
                        <th class="text-end pe-3">Tindakan Super Admin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resetRequests as $req)
                        <tr>
                            <td class="ps-3 small">
                                <div class="fw-bold text-slate-800">{{ $req->user->name }}</div>
                                <span class="badge bg-light text-dark border font-monospace">{{ $req->user->username }}</span>
                            </td>
                            <td class="small text-muted">{{ $req->catatan_pemohon ?: '-' }}</td>
                            <td class="small text-muted">{{ $req->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="text-end pe-3">
                                <div class="action-btn-group">
                                    <form action="{{ route('pengguna.reset-request.process', $req) }}" method="POST" class="d-inline-flex align-items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="aksi" value="reset">
                                        <input type="password" name="password_baru" class="form-control form-control-sm" style="width: 165px;" placeholder="Password baru (min 6)..." minlength="6" required>
                                        <button type="submit" class="btn-action-pill btn-action-success" title="Tetapkan Password Baru">
                                            <i class="bi bi-check-lg"></i> Reset
                                        </button>
                                    </form>
                                    <form action="{{ route('pengguna.reset-request.process', $req) }}" method="POST" onsubmit="return confirm('Tolak permohonan reset password ini?')">
                                        @csrf
                                        <input type="hidden" name="aksi" value="tolak">
                                        <button type="submit" class="btn-action btn-action-danger" title="Tolak">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Filter dan Pencarian -->
<div class="sipa-filter-card mb-3">
    <div class="p-0">
        <form action="{{ route('pengguna.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, username, NIP, atau email...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="peran" class="form-select form-select-sm">
                    <option value="">-- Semua Peran --</option>
                    <option value="SuperAdmin" {{ request('peran') === 'SuperAdmin' ? 'selected' : '' }}>Super Admin / Kabid</option>
                    <option value="Operator" {{ request('peran') === 'Operator' ? 'selected' : '' }}>Operator Bidang</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="unit_pengolah_id" class="form-select form-select-sm">
                    <option value="">-- Semua Bidang --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_pengolah_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->kode }} - {{ $u->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm rounded-pill w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @if(request()->hasAny(['q', 'peran', 'unit_pengolah_id']))
                    <a href="{{ route('pengguna.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset">
                        <i class="bi bi-x"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Pengguna -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-3" style="width: 50px;">No</th>
                    <th>Nama & NIP</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Peran (Role)</th>
                    <th>Unit Pengolah</th>
                    <th class="text-center">Status</th>
                    <th class="text-end pe-3" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $idx => $u)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $users->firstItem() + $idx }}</td>
                        <td>
                            <div class="fw-bold text-slate-800">{{ $u->name }}</div>
                            <div class="text-muted small">NIP. {{ $u->nip ?: '-' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark font-monospace border">{{ $u->username }}</span>
                        </td>
                        <td class="small text-muted">{{ $u->email ?: '-' }}</td>
                        <td>
                            @if($u->peran === 'SuperAdmin')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-shield-lock-fill me-1"></i> Super Admin / Kabid
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-person-workspace me-1"></i> Operator Bidang
                                </span>
                            @endif
                        </td>
                        <td class="small">
                            @if($u->unitPengolah)
                                <span class="badge bg-light text-dark border">{{ $u->unitPengolah->kode }}</span>
                                <span class="text-muted small">{{ $u->unitPengolah->nama }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Seluruh Dinas</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($u->aktif)
                                <span class="badge bg-success-subtle text-success rounded-pill">Aktif</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="action-btn-group">
                                <button type="button" class="btn-action btn-action-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser{{ $u->id }}" title="Sunting Akun">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @if($u->id !== Auth::id())
                                    <form action="{{ route('pengguna.destroy', $u) }}" method="POST" onsubmit="return confirm('Yakin ingin menonaktifkan akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-danger" title="Nonaktifkan Akun">
                                            <i class="bi bi-person-x"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit Pengguna -->
                    <div class="modal fade" id="modalEditUser{{ $u->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('pengguna.update', $u) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold fs-6"><i class="bi bi-pencil-square text-warning me-2"></i>Sunting Akun Pengguna</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $u->name }}" required>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                                                <input type="text" name="username" class="form-control form-control-sm" value="{{ $u->username }}" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">NIP</label>
                                                <input type="text" name="nip" class="form-control form-control-sm" value="{{ $u->nip }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Email</label>
                                            <input type="email" name="email" class="form-control form-control-sm" placeholder="petugas@gmail.com" value="{{ $u->email }}">
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Peran (Role) <span class="text-danger">*</span></label>
                                                <select name="peran" class="form-select form-select-sm" required>
                                                    <option value="SuperAdmin" {{ $u->peran === 'SuperAdmin' ? 'selected' : '' }}>Super Admin / Kabid</option>
                                                    <option value="Operator" {{ $u->peran === 'Operator' ? 'selected' : '' }}>Operator Bidang</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Unit Pengolah</label>
                                                <select name="unit_pengolah_id" class="form-select form-select-sm">
                                                    <option value="">-- Tidak Terikat Bidang --</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" {{ $u->unit_pengolah_id == $unit->id ? 'selected' : '' }}>
                                                            {{ $unit->kode }} - {{ $unit->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Password Baru (kosongkan jika tidak diubah)</label>
                                            <div class="input-group input-group-sm">
                                                <input type="password" name="password" id="passEdit{{ $u->id }}" class="form-control" placeholder="Minimal 6 karakter...">
                                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('passEdit{{ $u->id }}', this)" title="Lihat password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Status Akun</label>
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
                        <td colspan="8" class="text-center py-5 text-muted">Belum ada akun pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('pengguna.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6"><i class="bi bi-person-plus-fill me-2"></i>Tambah Akun Petugas Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Nama petugas lengkap dan gelar" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control form-control-sm" placeholder="operator.arsip" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">NIP</label>
                            <input type="text" name="nip" class="form-control form-control-sm" placeholder="1985xxxxxxxxxxxx">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="petugas@gmail.com">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Peran (Role) <span class="text-danger">*</span></label>
                            <select name="peran" class="form-select form-select-sm" required>
                                <option value="Operator">Operator Bidang</option>
                                <option value="SuperAdmin">Super Admin / Kabid</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Unit Pengolah</label>
                            <select name="unit_pengolah_id" class="form-select form-select-sm">
                                <option value="">-- Pilih Bidang --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->kode }} - {{ $unit->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Awal <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="password" name="password" id="passTambahUser" class="form-control" placeholder="Minimal 6 karakter" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('passTambahUser', this)" title="Lihat password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
@endsection
