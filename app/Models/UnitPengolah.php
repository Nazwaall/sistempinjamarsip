<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitPengolah extends Model
{
    use HasFactory;

    protected $table = 'unit_pengolah';

    protected $fillable = [
        'kode',
        'nama',
        'kepala_nama',
        'kepala_nip',
        'lama_pinjam_standar',
        'lama_pinjam_maks',
        'aktif',
    ];

    protected $casts = [
        'lama_pinjam_standar' => 'integer',
        'lama_pinjam_maks' => 'integer',
        'aktif' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'unit_pengolah_id');
    }

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'unit_pengolah_id');
    }

    public function suratPeminjaman()
    {
        return $this->hasMany(SuratPeminjaman::class, 'unit_pengolah_id');
    }
}
