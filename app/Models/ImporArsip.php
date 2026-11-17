<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImporArsip extends Model
{
    use HasFactory;

    protected $table = 'impor_arsip';

    protected $fillable = [
        'unit_pengolah_id',
        'nama_file',
        'jumlah_baris',
        'jumlah_berhasil',
        'jumlah_gagal',
        'dibuat_oleh',
    ];

    public function unitPengolah()
    {
        return $this->belongsTo(UnitPengolah::class, 'unit_pengolah_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
