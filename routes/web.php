<?php

use App\Http\Controllers\Admin\SeoRuleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeoCheckerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [SeoCheckerController::class, 'index'])->name('seo.index');
    Route::post('/', [SeoCheckerController::class, 'process'])->name('seo.process');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::prefix('admin/rules')->name('admin.rules.')->middleware('auth')->group(function () {
    Route::get('/', [SeoRuleController::class, 'index'])->name('index');
    Route::get('/create', [SeoRuleController::class, 'create'])->name('create');
    Route::post('/', [SeoRuleController::class, 'store'])->name('store');
    Route::get('/{rule}/edit', [SeoRuleController::class, 'edit'])->name('edit');
    Route::put('/{rule}', [SeoRuleController::class, 'update'])->name('update');
    Route::delete('/{rule}', [SeoRuleController::class, 'destroy'])->name('destroy');
    Route::patch('/{rule}/toggle', [SeoRuleController::class, 'toggle'])->name('toggle');
    Route::post('/import', [SeoRuleController::class, 'import'])->name('import');
});
