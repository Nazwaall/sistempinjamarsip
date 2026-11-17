<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjam extends Model
{
    use HasFactory;

    public const JENIS_LABELS = [
        'Internal' => 'Internal Dinas',
        'SKPD' => 'SKPD / Dinas Lain',
        'Kecamatan / Kelurahan' => 'Kecamatan / Kelurahan',
        'Eksternal' => 'Eksternal (Peneliti / Masyarakat)',
    ];

    protected $table = 'peminjam';

    protected $fillable = [
        'jenis',
        'nama',
        'nip_nik',
        'jabatan',
        'instansi_unit',
        'alamat',
        'no_hp',
        'email',
        'jaminan',
    ];

    public function suratPeminjaman()
    {
        return $this->hasMany(SuratPeminjaman::class, 'peminjam_id');
    }

    public function jenisLabel(): string
    {
        return self::JENIS_LABELS[$this->jenis] ?? $this->jenis;
    }

    public function jenisBadgeClass(): string
    {
        return match ($this->jenis) {
            'Internal' => 'bg-primary-subtle text-primary border-primary-subtle',
            'SKPD' => 'bg-info-subtle text-info-emphasis border-info-subtle',
            'Kecamatan / Kelurahan' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            default => 'bg-success-subtle text-success border-success-subtle',
        };
    }
}
