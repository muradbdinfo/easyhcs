<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    public $timestamps = false;

    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'metric', 'value', 'limit', 'period', 'meta', 'logged_at',
    ];

    protected $casts = [
        'meta'      => 'array',
        'logged_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}