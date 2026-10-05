<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

// Move x-data="aiEditor()" from the div to main
$content = str_replace('<main class="relative z-0', '<main x-data="aiEditor()" class="relative z-0', $content);
$content = str_replace('<div x-data="aiEditor()" class="w-full max-w-[1600px]', '<div class="w-full max-w-[1600px]', $content);

file_put_contents($path, $content);
echo "Moved x-data=aiEditor() to main tag.";
