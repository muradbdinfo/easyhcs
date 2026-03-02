<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view-audit-log');

        $query = AuditLog::query();

        if ($search = $request->string('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('model_label', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }
        if ($action = $request->string('action')) {
            $query->where('action', $action);
        }
        if ($userId = $request->integer('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($from = $request->string('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->string('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json(compact('logs'));
    }
}