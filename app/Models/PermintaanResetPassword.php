<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanResetPassword extends Model
{
    use HasFactory;

    protected $table = 'permintaan_reset_password';

    protected $fillable = [
        'user_id',
        'catatan_pemohon',
        'ip_address',
        'status',
        'diproses_oleh',
        'diproses_pada',
    ];

    protected $casts = [
        'diproses_pada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
