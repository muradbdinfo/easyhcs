<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly',
        'price_yearly', 'modules', 'limits', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'modules'   => 'array',
        'limits'    => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
}