<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    public function __invoke()
    {
        return Inertia::render('Admin/Dashboard', [
            'metrics'      => $this->service->getMetrics(),
            'revenueChart' => $this->service->getRevenueChart(),
            'recentTenants' => $this->service->getRecentTenants(),
            'recentTransactions' => $this->service->getRecentTransactions(),
        ]);
    }
}