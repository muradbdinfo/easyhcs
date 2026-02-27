<?php
// app/Models/System/SubscriptionInvoice.php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionInvoice extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'invoice_no', 'tenant_id', 'subscription_id', 'plan_id',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'paid_amount', 'due_amount', 'currency',
        'billing_cycle', 'billing_period_start', 'billing_period_end', 'due_date',
        'status', 'paid_at', 'payment_gateway', 'pdf_path', 'notes',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'due_amount'       => 'decimal:2',
        'due_date'         => 'date',
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
        'paid_at'          => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'invoice_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue'
            || ($this->status === 'pending' && $this->due_date?->isPast());
    }
}