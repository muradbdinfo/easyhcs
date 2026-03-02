<?php

namespace App\Services;

use App\Models\Tenant\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        $user = Auth::guard('tenant')->user();

        AuditLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name,
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->getKey(),
            'model_label' => $model ? static::getModelLabel($model) : null,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'description' => $description,
        ]);
    }

    private static function getModelLabel(Model $model): string
    {
        return match (true) {
            method_exists($model, 'getAuditLabel') => $model->getAuditLabel(),
            isset($model->name) => $model->name,
            isset($model->title) => $model->title,
            default => class_basename($model) . ' #' . $model->getKey(),
        };
    }
}