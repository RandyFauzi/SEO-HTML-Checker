<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

// Fix the syntax error by removing the backslash inside the route helper
$content = str_replace(
    "form.action = '{{ route(\\'admin.ai_editor.download_batch\\') }}';",
    "form.action = '{{ route(\"admin.ai_editor.download_batch\") }}';",
    $content
);

file_put_contents($path, $content);
echo "Fixed Blade syntax error!";
