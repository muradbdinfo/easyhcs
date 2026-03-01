<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'plan_id', 'status', 'billing_cycle',
        'starts_at', 'ends_at', 'cancelled_at', 'trial_ends_at','amount', 'currency',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function tenant()      { return $this->belongsTo(Tenant::class); }
    public function plan()        { return $this->belongsTo(Plan::class); }
    public function invoices()    { return $this->hasMany(SubscriptionInvoice::class); }
}