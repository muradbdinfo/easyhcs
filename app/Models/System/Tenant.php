<?php

namespace App\Models\System;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $connection = 'mysql'; // system DB

protected $fillable = [
    'id', 'business_name', 'contact_email', 'contact_phone',
    'address', 'plan_id', 'plan_status', 'trial_ends_at',
    'plan_expires_at', 'is_active', 'modules_enabled', 'data',
];

protected $casts = [
    'data'           => 'array',
    'meta'           => 'array',
    'modules_enabled'=> 'array',
    'trial_ends_at'  => 'datetime',
    'plan_expires_at'=> 'datetime',
];

public static function getCustomColumns(): array
{
    return [
        'id', 'business_name', 'business_type', 'contact_email', 'contact_phone',
        'address', 'city', 'country', 'timezone', 'currency', 'logo_path',
        'plan_id', 'plan_status', 'trial_ends_at', 'plan_expires_at',
        'owner_user_id', 'is_active', 'email_verified', 'activated_at',
        'suspended_at', 'suspension_reason', 'license_key',
        'license_failure_count', 'license_last_check', 'modules_enabled',
        'user_count', 'patient_count', 'storage_used_mb', 'meta', 'deleted_at',
    ];
}

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionInvoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function scopeActive($q)     { return $q->where('status', 'active'); }
    public function scopeSuspended($q)  { return $q->where('status', 'suspended'); }

    public function getNameAttribute()   { return $this->business_name; }
public function getEmailAttribute()  { return $this->contact_email; }
public function getStatusAttribute() { return $this->plan_status; }
}