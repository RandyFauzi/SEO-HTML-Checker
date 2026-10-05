<?php
$path = 'config/services.php';
$content = file_get_contents($path);

// Remove gemini block
$content = preg_replace("/\s*'gemini' => \[\s*'api_key' => env\('GEMINI_API_KEY'\),\s*'base_url' => env\('GEMINI_BASE_URL', '[^']+'\),\s*\],/s", "", $content);

// Remove ai_provider block
$content = preg_replace("/\s*'ai_provider' => env\('AI_PROVIDER', 'openai'\),/s", "", $content);

file_put_contents($path, $content);
echo "Cleaned config/services.php";
