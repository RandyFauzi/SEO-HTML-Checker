<?php
$path = 'routes/web.php';
$content = file_get_contents($path);

$search = "Route::post('/admin/ai-editor/process', [\App\Http\Controllers\Admin\AiHtmlEditorController::class, 'process'])->name('admin.ai_editor.process');";
$replace = $search . "\n    Route::post('/admin/ai-editor/download-batch', [\App\Http\Controllers\Admin\AiHtmlEditorController::class, 'downloadBatch'])->name('admin.ai_editor.download_batch');";

$content = str_replace($search, $replace, $content);

file_put_contents($path, $content);
echo "Added downloadBatch route";
