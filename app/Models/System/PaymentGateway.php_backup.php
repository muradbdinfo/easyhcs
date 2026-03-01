<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'slug', 'display_name', 'description',
        'config', 'mode',
        'supported_currencies', 'supported_methods',
        'supports_refund', 'supports_recurring', 'supports_webhook',
        'is_active', 'is_default',
        'fee_percentage', 'fee_fixed', 'fee_currency',
        'webhook_secret', 'notes',
    ];

    protected $casts = [
        'supported_currencies' => 'array',
        'supported_methods'    => 'array',
        'supports_refund'      => 'boolean',
        'supports_recurring'   => 'boolean',
        'supports_webhook'     => 'boolean',
        'is_active'            => 'boolean',
        'is_default'           => 'boolean',
        'fee_percentage'       => 'decimal:2',
        'fee_fixed'            => 'decimal:2',
    ];

    protected $hidden = ['config', 'webhook_secret'];

    // Encrypt/decrypt config
    public function getConfigAttribute(?string $value): ?array
    {
        if (!$value) return null;
        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (\Exception) {
            return null;
        }
    }

    public function setConfigAttribute(?array $value): void
    {
        $this->attributes['config'] = $value
            ? Crypt::encryptString(json_encode($value))
            : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}