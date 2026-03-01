<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\HealthController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\RefundController;


Route::middleware(['auth', 'super-admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Tenants
    Route::get('/tenants',                TenantController::class . '@index')->name('tenants.index');
    Route::get('/tenants/create',         TenantController::class . '@create')->name('tenants.create');
    Route::post('/tenants',               TenantController::class . '@store')->name('tenants.store');
    Route::get('/tenants/{tenant}',       TenantController::class . '@show')->name('tenants.show');
    Route::patch('/tenants/{tenant}/suspend',   TenantController::class . '@suspend')->name('tenants.suspend');
    Route::patch('/tenants/{tenant}/activate',  TenantController::class . '@activate')->name('tenants.activate');
    Route::delete('/tenants/{tenant}',    TenantController::class . '@destroy')->name('tenants.destroy');
    Route::post('/tenants/{tenant}/impersonate', TenantController::class . '@impersonate')->name('tenants.impersonate');

    // Plans
    Route::get('/plans',           PlanController::class . '@index')->name('plans.index');
    Route::post('/plans',          PlanController::class . '@store')->name('plans.store');
    Route::put('/plans/{plan}',    PlanController::class . '@update')->name('plans.update');
    Route::delete('/plans/{plan}', PlanController::class . '@destroy')->name('plans.destroy');

    // Subscriptions
    Route::get('/subscriptions',                        SubscriptionController::class . '@index')->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}',         SubscriptionController::class . '@show')->name('subscriptions.show');
    Route::patch('/subscriptions/{subscription}/cancel', SubscriptionController::class . '@cancel')->name('subscriptions.cancel');

    // Transactions
    Route::get('/transactions', TransactionController::class . '@index')->name('transactions.index');

    // Refunds
    Route::get('/refunds',                TransactionController::class . '@refunds')->name('refunds.index');
    Route::post('/transactions/{transaction}/refund', TransactionController::class . '@processRefund')->name('transactions.refund');

    // Payment Gateways
    Route::get('/gateways',          PaymentGatewayController::class . '@index')->name('gateways.index');
    Route::post('/gateways',         PaymentGatewayController::class . '@store')->name('gateways.store');
    Route::put('/gateways/{gateway}',    PaymentGatewayController::class . '@update')->name('gateways.update');
    Route::patch('/gateways/{gateway}/toggle', PaymentGatewayController::class . '@toggle')->name('gateways.toggle');

    // Licenses
    Route::get('/licenses',              LicenseController::class . '@index')->name('licenses.index');
    Route::post('/licenses',             LicenseController::class . '@store')->name('licenses.store');
    Route::patch('/licenses/{license}/revoke', LicenseController::class . '@revoke')->name('licenses.revoke');

    // Settings
    Route::get('/settings',    SettingsController::class . '@index')->name('settings.index');
    Route::post('/settings',   SettingsController::class . '@update')->name('settings.update');

    // Admin Users
    Route::get('/admin-users',               AdminUserController::class . '@index')->name('admin-users.index');
    Route::post('/admin-users',              AdminUserController::class . '@store')->name('admin-users.store');
    Route::put('/admin-users/{user}',        AdminUserController::class . '@update')->name('admin-users.update');
    Route::delete('/admin-users/{user}',     AdminUserController::class . '@destroy')->name('admin-users.destroy');

    // Audit Log
    Route::get('/audit-log', AuditLogController::class . '@index')->name('audit-log.index');

    // Health
    Route::get('/health', HealthController::class . '@index')->name('health.index');
    Route::post('/health/cache-clear', HealthController::class . '@clearCache')->name('health.cache-clear');
    Route::post('/health/queue-restart', HealthController::class . '@restartQueue')->name('health.queue-restart');
});