<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\HealthService;
use Illuminate\Support\Facades\{Cache, Artisan};
use Inertia\Inertia;

class HealthController extends Controller
{
    public function __construct(private HealthService $service) {}

    public function index()
    {
        return Inertia::render('Admin/Health/Index', [
            'status' => $this->service->getStatus(),
        ]);
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        return back()->with('success', 'All caches cleared.');
    }

    public function restartQueue()
    {
        Artisan::call('queue:restart');
        return back()->with('success', 'Queue restart signal sent.');
    }
}