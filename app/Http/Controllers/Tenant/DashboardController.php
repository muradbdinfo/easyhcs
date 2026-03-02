<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AuditLog;
use App\Models\Tenant\Patient;
use App\Models\Tenant\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $stats = [
            'total_patients'       => Patient::count(),
            'patients_today'       => Patient::whereDate('created_at', today())->count(),
            'patients_this_month'  => Patient::whereMonth('created_at', now()->month)
                                             ->whereYear('created_at', now()->year)
                                             ->count(),
            'total_users'          => User::count(),
            'active_users'         => User::where('status', 'active')->count(),
        ];

        $recent_patients = Patient::latest()->take(5)->get(['id', 'patient_no', 'name', 'gender', 'phone', 'created_at']);

        $recent_activity = AuditLog::with('user:id,name')
                                   ->latest()
                                   ->take(10)
                                   ->get(['id', 'user_id', 'user_name', 'action', 'model_label', 'description', 'created_at']);

        // Patients per day for the last 7 days
        $patients_chart = Patient::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map->count;

        $chart_labels = collect(range(6, 0))->map(fn ($d) => now()->subDays($d)->toDateString());
        $chart_data   = $chart_labels->map(fn ($date) => $patients_chart[$date] ?? 0);

        return response()->json([
            'stats'           => $stats,
            'recent_patients' => $recent_patients,
            'recent_activity' => $recent_activity,
            'chart'           => [
                'labels' => $chart_labels->values(),
                'data'   => $chart_data->values(),
            ],
        ]);
    }
}