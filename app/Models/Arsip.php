<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    use HasFactory;

    protected $table = 'arsip';

    protected $fillable = [
        'unit_pengolah_id',
        'unit_pengolah_asal',
        'kode_internal',
        'kode_klasifikasi',
        'uraian_berkas',
        'uraian_informasi',
        'tanggal_surat',
        'tahun',
        'bulan',
        'tingkat_perkembangan',
        'jra_aktif',
        'jra_inaktif',
        'jra_keterangan',
        'lokasi_simpan',
        'teks_label',
        'klasifikasi_keamanan',
        'dikecualikan',
        'status_ketersediaan',
        'skor_integritas_terakhir',
        'catatan_kondisi_terakhir',
        'terakhir_diperiksa_pada',
        'kode_qr',
        'aktif',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'jra_aktif' => 'integer',
        'jra_inaktif' => 'integer',
        'skor_integritas_terakhir' => 'integer',
        'terakhir_diperiksa_pada' => 'datetime',
        'dikecualikan' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function unitPengolah()
    {
        return $this->belongsTo(UnitPengolah::class, 'unit_pengolah_id');
    }

    public function suratPeminjamanItems()
    {
        return $this->hasMany(SuratPeminjamanItem::class, 'arsip_id');
    }

    public function isTersedia(): bool
    {
        return $this->status_ketersediaan === 'Tersedia' && $this->aktif;
    }
}
