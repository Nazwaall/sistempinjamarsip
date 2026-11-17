<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuktiFoto extends Model
{
    use HasFactory;

    protected $table = 'bukti_foto';

    protected $fillable = [
        'surat_id',
        'jenis',
        'sumber',
        'file_path',
        'nama_asli',
        'ocr_text',
        'diunggah_oleh',
    ];

    public function surat()
    {
        return $this->belongsTo(SuratPeminjaman::class, 'surat_id');
    }

    public function diunggahOleh()
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }
}
