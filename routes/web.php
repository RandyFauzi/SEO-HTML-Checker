<?php

use App\Http\Controllers\Admin\SeoRuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeoCheckerController;
use Illuminate\Support\Facades\Route;

// Public Public Facing
Route::view('/tutorial', 'tutorial.index')->name('tutorial.index');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\Admin\DashboardController;

// Protected Admin Panel
Route::middleware(['auth'])->group(function () {
    Route::get('/', [SeoCheckerController::class, 'index'])->name('seo.index');
    Route::get('/checker', function () {
        return redirect()->route('seo.index');
    });
    Route::post('/checker', [SeoCheckerController::class, 'process'])
        ->name('seo.process')
        ->middleware('throttle:10,1');
        
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin: User Management
    Route::middleware('role:super_admin')->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::resource('/', UserController::class)->parameter('', 'user');
    });

    // Admin & Super Admin: SEO Rules
    Route::middleware('role:super_admin,admin')->prefix('admin/rules')->name('admin.rules.')->group(function () {
        Route::get('/', [SeoRuleController::class, 'index'])->name('index');
        Route::get('/bank', [\App\Http\Controllers\Admin\RuleBankController::class, 'index'])->name('bank');
        Route::post('/bank/install', [\App\Http\Controllers\Admin\RuleBankController::class, 'install'])->name('bank.install');
        Route::get('/create', [SeoRuleController::class, 'create'])->name('create');
        Route::post('/', [SeoRuleController::class, 'store'])->name('store');
        Route::post('/ai-generate', [SeoRuleController::class, 'generateViaAi'])->name('ai-generate');
        Route::post('/test', [SeoRuleController::class, 'test'])->name('test');
        Route::get('/{rule}/edit', [SeoRuleController::class, 'edit'])->name('edit');
        Route::put('/{rule}', [SeoRuleController::class, 'update'])->name('update');
        Route::delete('/destroy-all', [SeoRuleController::class, 'destroyAll'])->name('destroyAll');
        Route::delete('/{rule}', [SeoRuleController::class, 'destroy'])->name('destroy');
        Route::patch('/{rule}/toggle', [SeoRuleController::class, 'toggle'])->name('toggle');
        Route::post('/import', [SeoRuleController::class, 'import'])->name('import');
        Route::get('/export', [SeoRuleController::class, 'export'])->name('export');
    });

    Route::middleware('role:super_admin,admin')->post('admin/remediations/generate', [\App\Http\Controllers\Admin\RemediationController::class, 'generate'])->name('admin.remediations.generate');

    // Admin & Super Admin: Brands
    Route::middleware('role:super_admin,admin')->prefix('admin/brands')->name('admin.brands.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BrandController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\BrandController::class, 'store'])->name('store');
        Route::delete('/{brand}', [\App\Http\Controllers\Admin\BrandController::class, 'destroy'])->name('destroy');
    });

    // History
    Route::get('/admin/history', [\App\Http\Controllers\Admin\HistoryController::class, 'index'])->name('admin.history.index');
    Route::get('/admin/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity_logs.index')->middleware('role:super_admin,admin');
    
    // AI Editor & Templates
    Route::get('/admin/ai-templates', [\App\Http\Controllers\Admin\AiTemplateController::class, 'index'])->name('admin.ai_templates.index');
    Route::post('/admin/ai-templates', [\App\Http\Controllers\Admin\AiTemplateController::class, 'store'])->name('admin.ai_templates.store');
    Route::delete('/admin/ai-templates/{template}', [\App\Http\Controllers\Admin\AiTemplateController::class, 'destroy'])->name('admin.ai_templates.destroy');
    Route::post('/admin/ai-editor/process-auto', [\App\Http\Controllers\Admin\AiHtmlEditorController::class, 'processAuto'])->name('admin.ai_editor.process_auto');
    Route::get('/admin/ai-editor', [\App\Http\Controllers\Admin\AiHtmlEditorController::class, 'index'])->name('admin.ai_editor.index');
    Route::post('/admin/ai-editor/process', [\App\Http\Controllers\Admin\AiHtmlEditorController::class, 'process'])->name('admin.ai_editor.process');
    Route::post('/admin/ai-editor/download-batch', [\App\Http\Controllers\Admin\AiHtmlEditorController::class, 'downloadBatch'])->name('admin.ai_editor.download_batch');
    Route::delete('/admin/history-batch', [\App\Http\Controllers\Admin\HistoryController::class, 'batchDestroy'])->name('admin.history.batchDestroy');
    Route::get('/admin/history/{run}', [\App\Http\Controllers\Admin\HistoryController::class, 'show'])->name('admin.history.show');
    Route::delete('/admin/history/{run}', [\App\Http\Controllers\Admin\HistoryController::class, 'destroy'])->name('admin.history.destroy');
    
    // Clear Cache
    Route::get('/admin/clear-cache-force', function() {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "Cache Cleared";
    });
});


