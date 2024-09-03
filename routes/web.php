<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
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

// Filament overrides
Route::get('/admin/login', function () {
    return redirect('/login');
})->name('filament.admin.auth.login');

/*
|--------------------------------------------------------------------------
| user dashboard Routes
|--------------------------------------------------------------------------
 */
Route::prefix('dashboard')
    ->middleware(['auth', 'verified', 'dashboard-redirector:' . implode(',', [User::ROLE_USER])])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::as('dashboard.')->group(function () {

        });
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | admin dashboard Routes
    |--------------------------------------------------------------------------
     */
    Route::get('/adm', function () {
        return redirect()->route('filament.admin.pages.dashboard');
    })->name('admin.dashboard');

});

require __DIR__ . '/auth.php';
