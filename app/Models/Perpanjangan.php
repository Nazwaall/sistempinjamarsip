<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perpanjangan extends Model
{
    use HasFactory;

    protected $table = 'perpanjangan';

    protected $fillable = [
        'surat_id',
        'tanggal_kembali_lama',
        'tanggal_kembali_baru',
        'alasan',
        'disetujui_oleh',
    ];

    protected $casts = [
        'tanggal_kembali_lama' => 'date',
        'tanggal_kembali_baru' => 'date',
    ];

    public function surat()
    {
        return $this->belongsTo(SuratPeminjaman::class, 'surat_id');
    }

    public function disetujuiOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
