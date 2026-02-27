<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\HealthController;

/*
|--------------------------------------------------------------------------
| Admin Routes — SuperAdmin Panel Only
| Middleware: auth, super-admin
| Prefix: /admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'super-admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tenants
    Route::resource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
    Route::post('tenants/{tenant}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');

    // Plans
    Route::resource('plans', PlanController::class);
    Route::post('plans/{plan}/toggle', [PlanController::class, 'toggle'])->name('plans.toggle');

    // Subscriptions
    Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'show']);
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('transactions/{transaction}/refund', [TransactionController::class, 'refund'])->name('transactions.refund');

    // Payment Gateways
    Route::resource('gateways', PaymentGatewayController::class)->except(['create', 'store', 'destroy']);
    Route::post('gateways/{gateway}/toggle', [PaymentGatewayController::class, 'toggle'])->name('gateways.toggle');
    Route::post('gateways/{gateway}/set-default', [PaymentGatewayController::class, 'setDefault'])->name('gateways.set-default');

    // Licenses
    Route::resource('licenses', LicenseController::class);
    Route::post('licenses/{license}/revoke', [LicenseController::class, 'revoke'])->name('licenses.revoke');
    Route::post('licenses/generate-batch', [LicenseController::class, 'generateBatch'])->name('licenses.generate-batch');

    // Admin Settings
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Admin Users
    Route::resource('admin-users', AdminUserController::class);

    // Audit Log
    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    // Health Check
    Route::get('health', [HealthController::class, 'index'])->name('health.index');

    // Redirect /admin → /admin/dashboard
    Route::redirect('/', '/admin/dashboard');
});