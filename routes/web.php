<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstallerController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


// ── Installer ─────────────────────────────────────────────────────────────────
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/',               [InstallerController::class, 'index'])->name('index');
    Route::get('/requirements',   [InstallerController::class, 'checkRequirements'])->name('requirements');
    Route::post('/test-database', [InstallerController::class, 'testDatabase'])->name('test-database');
    Route::post('/run',           [InstallerController::class, 'run'])->name('run');
});



Route::get('/debug-install', function () {
    $lockPath   = storage_path('installed.lock');
    $lockExists = file_exists($lockPath);
    $env        = file_exists(base_path('.env')) ? file_get_contents(base_path('.env')) : '';
    preg_match('/APP_KEY=(.+)/', $env, $matches);

    return response()->json([
        'lock_exists'       => $lockExists,
        'lock_path'         => $lockPath,
        'lock_content'      => $lockExists ? file_get_contents($lockPath) : null,
        'app_key_in_env'    => $matches[1] ?? 'MISSING',
        'app_key_in_config' => config('app.key'),
        'storage_writable'  => is_writable(storage_path()),
    ]);
});


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
