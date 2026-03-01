<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'key', 'status', 'max_users',
        'expires_at', 'revoked_at', 'last_verified_at',
        'fingerprint', 'failure_count',
    ];

    protected $casts = [
        'expires_at'      => 'datetime',
        'revoked_at'      => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
}