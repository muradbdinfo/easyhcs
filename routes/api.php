<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\HealthController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\RefundController;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out']);
});


Route::middleware('auth:sanctum')->get('/user/permissions', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'permissions' => $user->is_super_admin ? [] : $user->getPermissionNames(),
        'roles'       => $user->is_super_admin ? [] : $user->getRoleNames(),
    ]);
});


Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials, $request->boolean('remember'))) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    $request->session()->regenerate();
    $user = Auth::user();

    return response()->json([
        'user'     => $user,
        'redirect' => $user->is_super_admin ? '/admin' : '/dashboard',
    ]);
});


// Admin API (super-admin only)
Route::middleware(['auth:sanctum', 'super-admin'])->prefix('admin')->group(function () {
    Route::get('tenants',          [TenantController::class, 'index']);
    Route::post('tenants',         [TenantController::class, 'store']);
    Route::post('tenants/{t}/suspend',  [TenantController::class, 'suspend']);
    Route::post('tenants/{t}/activate', [TenantController::class, 'activate']);
    Route::delete('tenants/{t}',   [TenantController::class, 'destroy']);

    Route::get('plans',            [PlanController::class, 'index']);
    Route::post('plans',           [PlanController::class, 'store']);
    Route::put('plans/{plan}',     [PlanController::class, 'update']);
    Route::delete('plans/{plan}',  [PlanController::class, 'destroy']);

    Route::get('subscriptions',    [SubscriptionController::class, 'index']);
    Route::get('transactions',     [TransactionController::class, 'index']);
    Route::post('transactions/{t}/refund', [TransactionController::class, 'processRefund']);

    Route::get('gateways',         [PaymentGatewayController::class, 'index']);
    Route::put('gateways/{gw}',    [PaymentGatewayController::class, 'update']);
    Route::post('gateways/{gw}/toggle', [PaymentGatewayController::class, 'toggle']);

    Route::get('licenses',         [LicenseController::class, 'index']);
    Route::post('licenses',        [LicenseController::class, 'store']);
    Route::post('licenses/{l}/revoke', [LicenseController::class, 'revoke']);

    Route::get('settings',         [AdminSettingsController::class, 'index']);
    Route::put('settings',         [AdminSettingsController::class, 'update']);

    Route::get('admin-users',      [AdminUserController::class, 'index']);
    Route::post('admin-users',     [AdminUserController::class, 'store']);
    Route::put('admin-users/{u}',  [AdminUserController::class, 'update']);
    Route::delete('admin-users/{u}', [AdminUserController::class, 'destroy']);

    Route::get('audit-log',        [AuditLogController::class, 'index']);
    Route::get('health',           [HealthController::class, 'index']);
    Route::post('health/cache-clear',   [HealthController::class, 'clearCache']);
    Route::post('health/queue-restart', [HealthController::class, 'restartQueue']);
});