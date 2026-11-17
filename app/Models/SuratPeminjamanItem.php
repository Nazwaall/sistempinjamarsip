<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPeminjamanItem extends Model
{
    use HasFactory;

    protected $table = 'surat_peminjaman_item';

    protected $fillable = [
        'surat_id',
        'arsip_id',
        'kondisi_keluar',
        'tanggal_kembali_riil',
        'kondisi_kembali',
        'skor_integritas',
        'ringkasan_kerusakan',
        'detail_kerusakan_json',
        'foto_kembali_path',
        'catatan',
    ];

    protected $casts = [
        'tanggal_kembali_riil' => 'datetime',
        'skor_integritas' => 'integer',
        'detail_kerusakan_json' => 'array',
    ];

    public function surat()
    {
        return $this->belongsTo(SuratPeminjaman::class, 'surat_id');
    }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }
}
