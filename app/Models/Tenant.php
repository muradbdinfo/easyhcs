<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, SoftDeletes;

    protected $connection = 'mysql'; // system DB

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'business_name', 'business_type',
            'contact_email', 'contact_phone',
            'address', 'city', 'country',
            'timezone', 'currency', 'logo_path',
            'plan_id', 'plan_status',
            'trial_ends_at', 'plan_expires_at',
            'owner_user_id',
            'is_active', 'email_verified',
            'activated_at', 'suspended_at', 'suspension_reason',
            'license_key', 'license_failure_count', 'license_last_check',
            'modules_enabled',
            'user_count', 'patient_count', 'storage_used_mb',
            'meta',
        ];
    }

    protected $casts = [
        'modules_enabled'       => 'array',
        'meta'                  => 'array',
        'is_active'             => 'boolean',
        'email_verified'        => 'boolean',
        'trial_ends_at'         => 'datetime',
        'plan_expires_at'       => 'datetime',
        'activated_at'          => 'datetime',
        'suspended_at'          => 'datetime',
        'license_last_check'    => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id')
                    ->whereIn('status', ['active', 'trialing'])
                    ->latestOfMany();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'tenant_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'tenant_id');
    }

    public function license(): HasOne
    {
        return $this->hasOne(License::class, 'tenant_id')->where('status', 'active');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class, 'tenant_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSuspended($query)
    {
        return $query->where('plan_status', 'suspended');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getEnabledModules(): array
    {
        // Tenant override takes priority, else use plan's modules
        if ($this->modules_enabled) {
            return $this->modules_enabled;
        }
        return $this->plan?->modules_enabled ?? ['core', 'accounts'];
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->getEnabledModules());
    }

    public function isTrialing(): bool
    {
        return $this->plan_status === 'trialing'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->plan_status === 'suspended';
    }

    public function getDatabaseName(): string
    {
        return config('tenancy.database.prefix') . $this->id;
    }
}