<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = Activity::with('causer')
            ->when($request->search, fn($q, $s) =>
                $q->where('description', 'like', "%{$s}%")
            )
            ->when($request->causer, fn($q, $c) =>
                $q->where('causer_id', $c)
            )
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Admin/AuditLog/Index', [
            'logs'    => $logs,
            'filters' => $request->only('search', 'causer'),
        ]);
    }
}