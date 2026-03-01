<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'license_key', 'tenant_id', 'plan_id', 'type', 'status',
        'issued_at', 'activated_at', 'expires_at', 'revoked_at', 'revoke_reason',
        'last_heartbeat_at', 'last_heartbeat_ip', 'fingerprint',
        'failure_count', 'last_failure_at',
        'max_activations', 'activation_count',
        'generated_by', 'notes',
    ];

    protected $casts = [
        'issued_at'          => 'datetime',
        'activated_at'       => 'datetime',
        'expires_at'         => 'datetime',
        'revoked_at'         => 'datetime',
        'last_heartbeat_at'  => 'datetime',
        'last_failure_at'    => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isReadOnly(): bool
    {
        return $this->failure_count >= 3;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}