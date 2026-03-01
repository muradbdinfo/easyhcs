<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'invoice_id', 'gateway', 'type',
        'amount', 'currency', 'status', 'transaction_ref',
        'gateway_response', 'notes', 'refunded_from',
    ];

    protected $casts = ['gateway_response' => 'array'];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function invoice() { return $this->belongsTo(SubscriptionInvoice::class); }
    public function gateway() { return $this->belongsTo(PaymentGateway::class, 'gateway', 'slug'); }
}