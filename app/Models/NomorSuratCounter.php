<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorSuratCounter extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'nomor_surat_counter';
    protected $primaryKey = null;

    protected $fillable = [
        'unit_pengolah_id',
        'tahun',
        'terakhir',
    ];

    public function unitPengolah()
    {
        return $this->belongsTo(UnitPengolah::class, 'unit_pengolah_id');
    }
}
