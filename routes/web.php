<?php

use App\Http\Controllers\Admin\SeoRuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeoCheckerController;
use Illuminate\Support\Facades\Route;

// Public Public Facing
Route::get('/', [SeoCheckerController::class, 'index'])->name('seo.index');
Route::get('/checker', function () {
    return redirect()->route('seo.index');
});
Route::post('/checker', [SeoCheckerController::class, 'process'])
    ->name('seo.process')
    ->middleware('throttle:10,1');

Route::view('/tutorial', 'tutorial.index')->name('tutorial.index');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\Admin\DashboardController;

// Protected Admin Panel
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin: User Management
    Route::middleware('role:super_admin')->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::resource('/', UserController::class)->parameter('', 'user');
    });

    // Admin & Super Admin: SEO Rules
    Route::middleware('role:super_admin,admin')->prefix('admin/rules')->name('admin.rules.')->group(function () {
        Route::get('/', [SeoRuleController::class, 'index'])->name('index');
        Route::get('/create', [SeoRuleController::class, 'create'])->name('create');
        Route::post('/', [SeoRuleController::class, 'store'])->name('store');
        Route::post('/test', [SeoRuleController::class, 'test'])->name('test');
        Route::get('/{rule}/edit', [SeoRuleController::class, 'edit'])->name('edit');
        Route::put('/{rule}', [SeoRuleController::class, 'update'])->name('update');
        Route::delete('/{rule}', [SeoRuleController::class, 'destroy'])->name('destroy');
        Route::patch('/{rule}/toggle', [SeoRuleController::class, 'toggle'])->name('toggle');
        Route::post('/import', [SeoRuleController::class, 'import'])->name('import');
        Route::get('/export', [SeoRuleController::class, 'export'])->name('export');
    });
});

