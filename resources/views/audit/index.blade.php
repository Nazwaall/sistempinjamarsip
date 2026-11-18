@extends('layouts.app')

@section('title', 'Log Audit Aktivitas')

@section('content')
<div class="sipa-page-hero mb-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div>
        <div class="sipa-kicker">PENGAWASAN &amp; KEAMANAN</div>
        <h3 class="fw-bold mb-1 text-slate-800">Log Audit Aktivitas Sistem</h3>
        <p class="text-muted small mb-0">Rekam jejak seluruh aktivitas penting dan transaksi peminjaman berkas arsip.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('audit-log.backup-sql', request()->query()) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Unduh File Cadangan Database SQL Log Audit">
            <i class="bi bi-database-down"></i>
            <span>Backup Log (SQL)</span>
        </a>
        <a href="{{ route('audit-log.export-excel', request()->query()) }}" class="btn btn-success btn-sm rounded-pill px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Ekspor Rekap Log Audit ke Spreadsheet Excel">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Ekspor Excel</span>
        </a>
    </div>
</div>

<!-- Filter Log -->
<div class="sipa-filter-card mb-3">
    <div class="p-0">
        <form action="{{ route('audit-log.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari aksi atau entitas...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">-- Semua Petugas --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->username }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="entitas" class="form-select form-select-sm">
                    <option value="">-- Semua Entitas --</option>
                    @foreach($entities as $e)
                        <option value="{{ $e }}" {{ request('entitas') == $e ? 'selected' : '' }}>{{ $e }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm rounded-pill w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @if(request()->hasAny(['q', 'user_id', 'entitas']))
                    <a href="{{ route('audit-log.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset">
                        <i class="bi bi-x"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Log Audit -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-3" style="width: 170px;">Waktu Kejadian</th>
                    <th style="width: 180px;">Petugas (User)</th>
                    <th>Aksi Dilakukan</th>
                    <th>Entitas Terkait</th>
                    <th class="pe-3">Rincian Perubahan / Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="ps-3 text-muted small">
                            <i class="bi bi-clock me-1"></i> {{ $log->created_at?->translatedFormat('d F Y, H:i:s') }}
                        </td>
                        <td>
                            @if($log->user)
                                <div class="fw-semibold text-slate-800">{{ $log->user->name }}</div>
                                <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">{{ $log->user->peran }}</span>
                            @else
                                <span class="text-muted fst-italic">Sistem / Tamu</span>
                            @endif
                        </td>
                        <td>
                            @if(str_contains($log->aksi, 'Tambah') || str_contains($log->aksi, 'Buat'))
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-plus-circle me-1"></i> {{ $log->aksi }}
                                </span>
                            @elseif(str_contains($log->aksi, 'Setujui'))
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-check-all me-1"></i> {{ $log->aksi }}
                                </span>
                            @elseif(str_contains($log->aksi, 'Pengembalian'))
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-arrow-return-left me-1"></i> {{ $log->aksi }}
                                </span>
                            @elseif(str_contains($log->aksi, 'Hapus') || str_contains($log->aksi, 'Tolak') || str_contains($log->aksi, 'Nonaktifkan'))
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-exclamation-circle me-1"></i> {{ $log->aksi }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                    {{ $log->aksi }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace">{{ $log->entitas }}</span>
                            @if($log->entitas_id)
                                <small class="text-muted">#{{ $log->entitas_id }}</small>
                            @endif
                        </td>
                        <td class="pe-3">
                            @if(is_array($log->detail) && !empty($log->detail))
                                <div class="bg-light p-2 rounded-2 small font-monospace text-slate-800" style="font-size: 0.75rem; max-width: 450px;">
                                    @foreach($log->detail as $key => $val)
                                        <div>
                                            <span class="text-muted">{{ $key }}:</span>
                                            <span class="fw-semibold">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x display-6 d-block mb-2 text-secondary"></i>
                            Belum ada catatan log audit.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
