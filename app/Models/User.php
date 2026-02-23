<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'api';

    protected $fillable = [
        'user_id', 'name', 'username', 'email', 'role',
        'phone', 'password', 'status', 'settings', 'avatar'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'json',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['role'];

    public function getRoleAttribute($value)
    {
        return $this->roles->pluck('name')->first() ?? $value;
    }

    /**
     * Required by JWTSubject
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Required by JWTSubject
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // បង្កើត Custom ID ស្វ័យប្រវត្តិ (ឧទាហរណ៍៖ USR-2026-0001)
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $year = date('Y');
                $latestId = static::max('id') ?? 0;
                $nextId = str_pad($latestId + 1, 4, '0', STR_PAD_LEFT);
                $model->user_id = "USE-{$year}-{$nextId}";
            }
        });
    }
}
