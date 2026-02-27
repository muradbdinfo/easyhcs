<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'plan_id', 'billing_cycle', 'amount', 'currency',
        'starts_at', 'ends_at', 'trial_ends_at', 'cancelled_at', 'renewed_at',
        'status', 'auto_renew', 'grace_period_days',
        'dunning_count', 'dunning_last_sent_at',
        'modules_snapshot', 'notes',
    ];

    protected $casts = [
        'starts_at'            => 'datetime',
        'ends_at'              => 'datetime',
        'trial_ends_at'        => 'datetime',
        'cancelled_at'         => 'datetime',
        'renewed_at'           => 'datetime',
        'dunning_last_sent_at' => 'datetime',
        'auto_renew'           => 'boolean',
        'modules_snapshot'     => 'array',
        'amount'               => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'subscription_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function daysUntilExpiry(): int
    {
        return max(0, now()->diffInDays($this->ends_at, false));
    }
}