<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPeminjaman extends Model
{
    use HasFactory;

    public const BENTUK_LAYANAN_LIST = [
        'Fisik',
        'Digital (PDF)',
        'Layanan Informasi',
    ];

    protected $table = 'surat_peminjaman';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'unit_pengolah_id',
        'peminjam_id',
        'bentuk_layanan',
        'maksud_keperluan',
        'jaminan',
        'tanggal_mulai',
        'tanggal_kembali',
        'lama_pinjam_hari',
        'diperpanjang_kali',
        'butuh_persetujuan',
        'status_persetujuan',
        'disetujui_oleh',
        'tanggal_disetujui',
        'alasan_penolakan',
        'metode_ttd',
        'ttd_peminjam_path',
        'surat_ttd_path',
        'petugas_id',
        'petugas_layanan_nama',
        'petugas_layanan_nip',
        'petugas_layanan_bidang',
        'status',
        'is_janjian',
        'rencana_ambil_pada',
        'catatan_janjian',
        'tanggal_selesai',
        'catatan',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_kembali' => 'date',
        'lama_pinjam_hari' => 'integer',
        'diperpanjang_kali' => 'integer',
        'butuh_persetujuan' => 'boolean',
        'is_janjian' => 'boolean',
        'tanggal_disetujui' => 'datetime',
        'rencana_ambil_pada' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function unitPengolah()
    {
        return $this->belongsTo(UnitPengolah::class, 'unit_pengolah_id');
    }

    public function peminjam()
    {
        return $this->belongsTo(Peminjam::class, 'peminjam_id');
    }

    public function disetujuiOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function items()
    {
        return $this->hasMany(SuratPeminjamanItem::class, 'surat_id');
    }

    public function buktiFoto()
    {
        return $this->hasMany(BuktiFoto::class, 'surat_id');
    }

    public function perpanjangan()
    {
        return $this->hasMany(Perpanjangan::class, 'surat_id');
    }

    public function isFisik(): bool
    {
        return ($this->bentuk_layanan ?? 'Fisik') === 'Fisik';
    }

    public function namaPetugasLayanan(): string
    {
        if (!empty($this->petugas_layanan_nama)) {
            return $this->petugas_layanan_nama;
        }

        $namaAkun = $this->petugas?->name;
        if ($namaAkun && strcasecmp(trim($namaAkun), 'Super Admin SIPA') !== 0) {
            return $namaAkun;
        }

        return 'Petugas Layanan';
    }

    public function nipPetugasLayanan(): ?string
    {
        if (!empty($this->petugas_layanan_nip)) {
            return $this->petugas_layanan_nip;
        }

        $namaAkun = $this->petugas?->name;
        if ($namaAkun && strcasecmp(trim($namaAkun), 'Super Admin SIPA') !== 0) {
            return $this->petugas?->nip;
        }

        return null;
    }

    public function bidangPetugasLayanan(): ?string
    {
        if (!empty($this->petugas_layanan_bidang)) {
            return $this->petugas_layanan_bidang;
        }

        return $this->petugas?->unitPengolah?->nama ?: $this->unitPengolah?->nama;
    }

    public function dapatDiaksesOleh(User $user): bool
    {
        return $user->isSuperAdmin()
            || !$user->unit_pengolah_id
            || $this->unit_pengolah_id === $user->unit_pengolah_id
            || $this->petugas_id === $user->id;
    }

    public function isTerlambat(): bool
    {
        return in_array($this->status, ['Aktif', 'Terlambat'])
            && $this->tanggal_kembali
            && $this->tanggal_kembali->copy()->startOfDay()->lt(now()->startOfDay());
    }

    public function layananBadgeClass(): string
    {
        return match ($this->bentuk_layanan) {
            'Digital (PDF)' => 'bg-info-subtle text-info-emphasis border-info-subtle',
            'Layanan Informasi', 'Layanan Informasi (Statis)' => 'bg-dark-subtle text-dark border-secondary-subtle',
            default => 'bg-primary-subtle text-primary border-primary-subtle',
        };
    }

    public function statusBadgeInfo(): array
    {
        if ($this->status === 'Menunggu Persetujuan') {
            return ['class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle', 'icon' => 'bi-hourglass-split', 'label' => 'Menunggu'];
        }
        if ($this->status === 'Janjian') {
            return ['class' => 'bg-info-subtle text-info-emphasis border border-info-subtle', 'icon' => 'bi-calendar2-check', 'label' => 'Janjian / Dipesan'];
        }
        if ($this->isTerlambat() || $this->status === 'Terlambat') {
            return ['class' => 'bg-danger-subtle text-danger border border-danger-subtle', 'icon' => 'bi-exclamation-circle-fill', 'label' => 'Terlambat'];
        }
        if ($this->status === 'Aktif') {
            return ['class' => 'bg-primary-subtle text-primary border border-primary-subtle', 'icon' => 'bi-clock-history', 'label' => $this->isFisik() ? 'Dipinjam' : 'Layanan Aktif'];
        }
        if ($this->status === 'Selesai') {
            return ['class' => 'bg-success-subtle text-success border border-success-subtle', 'icon' => 'bi-check-circle-fill', 'label' => 'Selesai'];
        }
        if ($this->status === 'Ditolak') {
            return ['class' => 'bg-danger-subtle text-danger border border-danger-subtle', 'icon' => 'bi-x-circle-fill', 'label' => 'Ditolak'];
        }
        if ($this->status === 'Dibatalkan') {
            return ['class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle', 'icon' => 'bi-slash-circle', 'label' => 'Dibatalkan'];
        }

        return ['class' => 'bg-secondary-subtle text-secondary border', 'icon' => 'bi-circle', 'label' => $this->status];
    }
}
