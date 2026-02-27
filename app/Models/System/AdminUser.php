<?php

namespace App\Models\System;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mysql';
    protected $table      = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'phone',
        'avatar_path',
        'timezone',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_super_admin'          => 'boolean',
        'is_active'               => 'boolean',
        'email_verified_at'       => 'datetime',
        'last_login_at'           => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];
}