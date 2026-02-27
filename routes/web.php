<?php

use App\Http\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

// ── Installer routes (no auth, no tenancy) ────────────────────────
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/',               [InstallerController::class, 'index'])->name('index');
    Route::get('/requirements',   [InstallerController::class, 'checkRequirements'])->name('requirements');
    Route::post('/test-database', [InstallerController::class, 'testDatabase'])->name('test-database');
    Route::post('/run',           [InstallerController::class, 'run'])->name('run');
});



// ── Named routes Laravel internals need ───────────────────────────
Route::get('/reset-password/{token}', function () {
    return view('app');
})->name('password.reset');

Route::get('/verify-email/{id}/{hash}', function () {
    return view('app');
})->middleware(['signed'])->name('verification.verify');


// ── SPA catch-all — Vue Router handles everything else ────────────
// Excludes: api/*, sanctum/*, install/* (already defined above)
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api|sanctum|install|debug-install).*$');

require __DIR__.'/auth.php';
