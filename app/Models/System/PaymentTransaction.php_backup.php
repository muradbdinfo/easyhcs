<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'transaction_no', 'tenant_id', 'invoice_id',
        'gateway', 'gateway_transaction_id', 'gateway_response',
        'payment_method', 'amount', 'currency', 'gateway_fee', 'net_amount',
        'status', 'completed_at', 'failed_at', 'failure_reason',
        'is_refunded', 'refund_amount', 'refunded_at', 'refund_reason', 'refunded_by',
        'ipn_validation_id', 'ipn_verified', 'notes',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'amount'           => 'decimal:2',
        'gateway_fee'      => 'decimal:2',
        'net_amount'       => 'decimal:2',
        'refund_amount'    => 'decimal:2',
        'is_refunded'      => 'boolean',
        'ipn_verified'     => 'boolean',
        'completed_at'     => 'datetime',
        'failed_at'        => 'datetime',
        'refunded_at'      => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id');
    }
}