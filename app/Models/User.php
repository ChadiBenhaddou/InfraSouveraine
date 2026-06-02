<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_tenant_id',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function currentTenant()
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
