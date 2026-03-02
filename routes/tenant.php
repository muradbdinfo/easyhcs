<?php

use App\Http\Controllers\Tenant\AuditLogController;
use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\BillingController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\PatientController;
use App\Http\Controllers\Tenant\SettingController;
use App\Http\Controllers\Tenant\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
| All routes run inside tenant context (tenancy bootstrapper has already
| switched the DB connection).
|
| Public (within tenant context): login / logout
| Protected:                       auth + verified + tenant.auth
*/

// ─── Auth — public within tenant context ────────────────────────────────────
Route::middleware(['tenancy', 'web'])->group(function () {
    Route::post('/tenant/login',  [AuthController::class, 'login'])->name('tenant.login');
    Route::post('/tenant/logout', [AuthController::class, 'logout'])->name('tenant.logout');
});

// ─── Authenticated Tenant Routes ─────────────────────────────────────────────
Route::middleware(['tenancy', 'web', 'auth', 'verified', 'tenant.auth'])->group(function () {

    // ── Auth / Profile ────────────────────────────────────────────────────────
    Route::get('/me',                [AuthController::class, 'me'])->name('tenant.me');
    Route::put('/profile',           [AuthController::class, 'updateProfile'])->name('tenant.profile.update');
    Route::put('/profile/password',  [AuthController::class, 'updatePassword'])->name('tenant.password.update');

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Notifications ─────────────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                     [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count',         [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::patch('/{id}/read',          [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read',       [NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::delete('/{id}',              [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ── Billing & Plan ────────────────────────────────────────────────────────
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/',         [BillingController::class, 'index'])->name('index');
        Route::post('/subscribe', [BillingController::class, 'subscribe'])->name('subscribe');
    });

    // ── Audit Log ─────────────────────────────────────────────────────────────
    Route::get('/audit-log', [\App\Http\Controllers\Tenant\Core\AuditLogController::class, 'index'])
        ->middleware('permission:view-audit-log')
        ->name('audit-log');

    // ─── Core Module (always enabled) ────────────────────────────────────────

    // Patients
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/',             [\App\Http\Controllers\Tenant\Core\PatientController::class, 'index'])->name('index');
        Route::post('/',            [\App\Http\Controllers\Tenant\Core\PatientController::class, 'store'])->name('store');
        Route::get('/search',       [\App\Http\Controllers\Tenant\Core\PatientController::class, 'search'])->name('search');
        Route::get('/{patient}',    [\App\Http\Controllers\Tenant\Core\PatientController::class, 'show'])->name('show');
        Route::put('/{patient}',    [\App\Http\Controllers\Tenant\Core\PatientController::class, 'update'])->name('update');
        Route::delete('/{patient}', [\App\Http\Controllers\Tenant\Core\PatientController::class, 'destroy'])->name('destroy');
    });

    // Users
    Route::prefix('users')->name('users.')->middleware('permission:manage-users')->group(function () {
        Route::get('/',                       [\App\Http\Controllers\Tenant\Core\UserController::class, 'index'])->name('index');
        Route::post('/',                      [\App\Http\Controllers\Tenant\Core\UserController::class, 'store'])->name('store');
        Route::get('/{user}',                 [\App\Http\Controllers\Tenant\Core\UserController::class, 'show'])->name('show');
        Route::put('/{user}',                 [\App\Http\Controllers\Tenant\Core\UserController::class, 'update'])->name('update');
        Route::delete('/{user}',              [\App\Http\Controllers\Tenant\Core\UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/toggle-status', [\App\Http\Controllers\Tenant\Core\UserController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Roles
    Route::resource('roles', \App\Http\Controllers\Tenant\Core\RoleController::class)
        ->middleware('permission:manage-roles');

    // Settings
    Route::prefix('settings')->name('settings.')->middleware('permission:manage-settings')->group(function () {
        Route::get('/',       [\App\Http\Controllers\Tenant\Core\SettingsController::class, 'index'])->name('index');
        Route::put('/',       [\App\Http\Controllers\Tenant\Core\SettingsController::class, 'update'])->name('update');
        Route::post('/logo',  [SettingController::class, 'uploadLogo'])->name('logo');
    });

    // ─── Pharmacy Module ──────────────────────────────────────────────────────
    Route::middleware(['module:pharmacy'])->prefix('pharmacy')->name('pharmacy.')->group(function () {
        // POS
        Route::get('/pos', [\App\Http\Controllers\Tenant\Pharmacy\PosController::class, 'index'])
            ->middleware('permission:pharmacy-sell')->name('pos');

        // Medicines
        Route::resource('medicines', \App\Http\Controllers\Tenant\Pharmacy\MedicineController::class)
            ->middleware('permission:pharmacy-manage-medicines');

        // Medicine Categories
        Route::resource('categories', \App\Http\Controllers\Tenant\Pharmacy\CategoryController::class)
            ->middleware('permission:pharmacy-manage-medicines');

        // Suppliers
        Route::resource('suppliers', \App\Http\Controllers\Tenant\Pharmacy\SupplierController::class)
            ->middleware('permission:pharmacy-manage-suppliers');

        // Purchase Requests
        Route::resource('purchase-requests', \App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class)
            ->middleware('permission:pr-create');
        Route::post('purchase-requests/{pr}/submit', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class, 'submit'])
            ->middleware('permission:pr-create')->name('purchase-requests.submit');
        Route::post('purchase-requests/{pr}/approve-step1', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class, 'approveStep1'])
            ->middleware('permission:pr-approve-step1')->name('purchase-requests.approve-step1');
        Route::post('purchase-requests/{pr}/reject-step1', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class, 'rejectStep1'])
            ->middleware('permission:pr-approve-step1')->name('purchase-requests.reject-step1');
        Route::post('purchase-requests/{pr}/approve-step2', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class, 'approveStep2'])
            ->middleware('permission:pr-approve-step2')->name('purchase-requests.approve-step2');
        Route::post('purchase-requests/{pr}/reject-step2', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class, 'rejectStep2'])
            ->middleware('permission:pr-approve-step2')->name('purchase-requests.reject-step2');
        Route::post('purchase-requests/{pr}/cancel', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class, 'cancel'])
            ->middleware('permission:pr-cancel')->name('purchase-requests.cancel');
        Route::get('purchase-requests/{pr}/pdf', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseRequestController::class, 'pdf'])
            ->name('purchase-requests.pdf');

        // Purchase Orders
        Route::resource('purchase-orders', \App\Http\Controllers\Tenant\Pharmacy\PurchaseOrderController::class)
            ->middleware('permission:po-view');
        Route::post('purchase-orders/{po}/receive', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseOrderController::class, 'receive'])
            ->middleware('permission:po-receive')->name('purchase-orders.receive');
        Route::get('purchase-orders/{po}/pdf', [\App\Http\Controllers\Tenant\Pharmacy\PurchaseOrderController::class, 'pdf'])
            ->name('purchase-orders.pdf');

        // Sales
        Route::get('/sales', [\App\Http\Controllers\Tenant\Pharmacy\SaleController::class, 'index'])
            ->middleware('permission:pharmacy-view-sales')->name('sales.index');
        Route::get('/sales/{sale}', [\App\Http\Controllers\Tenant\Pharmacy\SaleController::class, 'show'])
            ->middleware('permission:pharmacy-view-sales')->name('sales.show');
        Route::post('/sales/{sale}/return', [\App\Http\Controllers\Tenant\Pharmacy\SaleController::class, 'processReturn'])
            ->middleware('permission:pharmacy-sell')->name('sales.return');

        // Stock Alerts
        Route::get('/stock-alerts', [\App\Http\Controllers\Tenant\Pharmacy\StockAlertController::class, 'index'])
            ->name('stock-alerts');

        // Stock Adjustments
        Route::post('/stock-adjust', [\App\Http\Controllers\Tenant\Pharmacy\StockAdjustmentController::class, 'store'])
            ->middleware('permission:pharmacy-stock-adjust')->name('stock-adjust');
    });

    // ─── Diagnostic Module ────────────────────────────────────────────────────
    Route::middleware(['module:diagnostic'])->prefix('diagnostic')->name('diagnostic.')->group(function () {
        Route::resource('test-orders', \App\Http\Controllers\Tenant\Diagnostic\TestOrderController::class)
            ->middleware('permission:diagnostic-create-order');
        Route::resource('tests', \App\Http\Controllers\Tenant\Diagnostic\TestController::class)
            ->middleware('permission:diagnostic-manage-tests');
        Route::resource('test-categories', \App\Http\Controllers\Tenant\Diagnostic\TestCategoryController::class)
            ->middleware('permission:diagnostic-manage-tests');
        Route::post('test-orders/{order}/collect-sample', [\App\Http\Controllers\Tenant\Diagnostic\SampleController::class, 'collect'])
            ->middleware('permission:diagnostic-collect-sample')->name('test-orders.collect-sample');
        Route::post('test-orders/{order}/items/{item}/enter-result', [\App\Http\Controllers\Tenant\Diagnostic\ResultController::class, 'enter'])
            ->middleware('permission:diagnostic-enter-result')->name('results.enter');
        Route::post('test-orders/{order}/items/{item}/verify', [\App\Http\Controllers\Tenant\Diagnostic\ResultController::class, 'verify'])
            ->middleware('permission:diagnostic-verify-result')->name('results.verify');
        Route::post('test-orders/{order}/release', [\App\Http\Controllers\Tenant\Diagnostic\ResultController::class, 'release'])
            ->middleware('permission:diagnostic-release-result')->name('results.release');
        Route::get('test-orders/{order}/report', [\App\Http\Controllers\Tenant\Diagnostic\ResultController::class, 'report'])
            ->name('results.report');
    });

    // ─── Hospital Module ──────────────────────────────────────────────────────
    Route::middleware(['module:hospital'])->prefix('hospital')->name('hospital.')->group(function () {
        Route::resource('doctors', \App\Http\Controllers\Tenant\Hospital\DoctorController::class)
            ->middleware('permission:hospital-manage-doctors');
        Route::resource('appointments', \App\Http\Controllers\Tenant\Hospital\AppointmentController::class)
            ->middleware('permission:hospital-appointments');
        Route::resource('patients', \App\Http\Controllers\Tenant\Hospital\PatientController::class)
            ->middleware('permission:manage-patients');
        Route::resource('admissions', \App\Http\Controllers\Tenant\Hospital\AdmissionController::class)
            ->middleware('permission:hospital-admissions');
        Route::resource('emr', \App\Http\Controllers\Tenant\Hospital\EmrController::class)
            ->middleware('permission:hospital-emr');
        Route::resource('prescriptions', \App\Http\Controllers\Tenant\Hospital\PrescriptionController::class)
            ->middleware('permission:hospital-prescribe');
        Route::resource('wards', \App\Http\Controllers\Tenant\Hospital\WardController::class)
            ->middleware('permission:hospital-ward-management');
        Route::resource('ot-schedules', \App\Http\Controllers\Tenant\Hospital\OtController::class)
            ->middleware('permission:hospital-ot');
        Route::post('admissions/{admission}/discharge', [\App\Http\Controllers\Tenant\Hospital\AdmissionController::class, 'discharge'])
            ->middleware('permission:hospital-discharge')->name('admissions.discharge');
        Route::resource('insurance-claims', \App\Http\Controllers\Tenant\Hospital\InsuranceController::class)
            ->middleware('permission:hospital-insurance');
    });

    // ─── Accounts Module ──────────────────────────────────────────────────────
    Route::middleware(['module:accounts'])->prefix('accounts')->name('accounts.')->group(function () {
        Route::resource('chart-of-accounts', \App\Http\Controllers\Tenant\Accounts\ChartOfAccountsController::class)
            ->middleware('permission:accounts-manage');
        Route::resource('journal-entries', \App\Http\Controllers\Tenant\Accounts\JournalController::class)
            ->middleware('permission:accounts-journals');
        Route::resource('expenses', \App\Http\Controllers\Tenant\Accounts\ExpenseController::class)
            ->middleware('permission:accounts-expenses');
        Route::get('reports', [\App\Http\Controllers\Tenant\Accounts\ReportController::class, 'index'])
            ->middleware('permission:accounts-view-reports')->name('reports.index');
        Route::resource('patient-dues', \App\Http\Controllers\Tenant\Accounts\PatientDueController::class)
            ->middleware('permission:accounts-manage-dues');
    });

    // ── Catch-all SPA Fallback ────────────────────────────────────────────────
    Route::get('/{any}', fn() => inertia('Tenant/NotFound'))->where('any', '.*')->name('spa');
});