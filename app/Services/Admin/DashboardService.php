<?php

namespace App\Services\Admin;

use App\Models\System\Tenant;
use App\Models\System\Subscription;
use App\Models\System\PaymentTransaction;
use App\Models\System\Plan;
use Carbon\Carbon;

class DashboardService
{
    public function getMetrics(): array
    {
        $now = Carbon::now();

        return [
            'tenants' => [
                'total'          => Tenant::count(),
                'active'         => Tenant::where('status', 'active')->count(),
                'new_this_month' => Tenant::whereMonth('created_at', $now->month)
                                          ->whereYear('created_at', $now->year)->count(),
                'suspended'      => Tenant::where('status', 'suspended')->count(),
            ],
            'revenue' => [
                'this_month' => PaymentTransaction::where('status', 'completed')
                    ->whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->sum('amount'),
                'last_month' => PaymentTransaction::where('status', 'completed')
                    ->whereMonth('created_at', $now->subMonth()->month)
                    ->whereYear('created_at', $now->year)
                    ->sum('amount'),
                'total' => PaymentTransaction::where('status', 'completed')->sum('amount'),
            ],
            'subscriptions' => [
                'active'   => Subscription::where('status', 'active')->count(),
                'trial'    => Subscription::where('status', 'trial')->count(),
                'expiring' => Subscription::where('status', 'active')
                    ->whereBetween('ends_at', [now(), now()->addDays(7)])->count(),
            ],
            'plans' => Plan::withCount('subscriptions')
                          ->orderBy('subscriptions_count', 'desc')
                          ->get(['id','name','subscriptions_count']),
        ];
    }

    public function getRevenueChart(int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $data[] = [
                'month'   => $date->format('M Y'),
                'revenue' => PaymentTransaction::where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
            ];
        }
        return $data;
    }

    public function getRecentTenants(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Tenant::with('subscription.plan')
                     ->latest()
                     ->limit($limit)
                     ->get();
    }

    public function getRecentTransactions(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return PaymentTransaction::with('tenant')
                                 ->latest()
                                 ->limit($limit)
                                 ->get();
    }
}