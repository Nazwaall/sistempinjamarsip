<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAudit extends Model
{
    public $timestamps = false;
    protected $table = 'log_audit';

    protected $fillable = [
        'user_id',
        'aksi',
        'entitas',
        'entitas_id',
        'detail',
        'created_at',
    ];

    protected $casts = [
        'detail' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
