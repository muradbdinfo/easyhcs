<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql'; // system DB

    protected $fillable = [
        'name', 'slug', 'description',
        'price_monthly', 'price_yearly', 'currency',
        'modules_enabled',
        'max_users', 'max_patients', 'max_sms_per_month', 'max_storage_mb',
        'has_api_access', 'has_custom_domain', 'has_white_label',
        'has_priority_support', 'has_backup',
        'plan_type', 'trial_days',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'modules_enabled'      => 'array',
        'price_monthly'        => 'decimal:2',
        'price_yearly'         => 'decimal:2',
        'has_api_access'       => 'boolean',
        'has_custom_domain'    => 'boolean',
        'has_white_label'      => 'boolean',
        'has_priority_support' => 'boolean',
        'has_backup'           => 'boolean',
        'is_active'            => 'boolean',
        'is_featured'          => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules_enabled ?? []);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_monthly, 2) . ' ' . $this->currency;
    }
}