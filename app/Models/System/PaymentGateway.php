<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $connection = 'mysql';

    protected $fillable = ['name', 'slug', 'credentials', 'is_active', 'sort_order'];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'is_active'   => 'boolean',
    ];
}