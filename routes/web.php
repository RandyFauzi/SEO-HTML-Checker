<?php

use App\Http\Controllers\Admin\SeoRuleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeoCheckerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SeoCheckerController::class, 'index'])->name('seo.index');
Route::post('/', [SeoCheckerController::class, 'process'])
    ->name('seo.process')
    ->middleware('throttle:10,1');

// Keep auth routes but they are not strictly enforced on other pages anymore
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin/rules')->name('admin.rules.')->group(function () {
    Route::get('/', [SeoRuleController::class, 'index'])->name('index');
    Route::get('/create', [SeoRuleController::class, 'create'])->name('create');
    Route::post('/', [SeoRuleController::class, 'store'])->name('store');
    Route::get('/{rule}/edit', [SeoRuleController::class, 'edit'])->name('edit');
    Route::put('/{rule}', [SeoRuleController::class, 'update'])->name('update');
    Route::delete('/{rule}', [SeoRuleController::class, 'destroy'])->name('destroy');
    Route::patch('/{rule}/toggle', [SeoRuleController::class, 'toggle'])->name('toggle');
    Route::post('/import', [SeoRuleController::class, 'import'])->name('import');
    Route::get('/export', [SeoRuleController::class, 'export'])->name('export');
});
