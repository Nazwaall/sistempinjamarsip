<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements CanResetPassword
{
    use HasFactory, Notifiable, CanResetPasswordTrait;

    protected $fillable = [
        'unit_pengolah_id',
        'name',
        'nip',
        'username',
        'email',
        'password',
        'peran',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'aktif' => 'boolean',
    ];

    public function unitPengolah()
    {
        return $this->belongsTo(UnitPengolah::class, 'unit_pengolah_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->peran === 'SuperAdmin';
    }

    public function isOperator(): bool
    {
        return $this->peran === 'Operator';
    }

    public function peranLabel(): string
    {
        return $this->isSuperAdmin() ? 'Super Admin / Kabid' : 'Operator Bidang';
    }
}
