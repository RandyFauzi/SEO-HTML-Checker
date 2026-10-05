<?php
$path = 'app/Providers/AppServiceProvider.php';
$content = file_get_contents($path);

$content = str_replace(
"            if (\$provider === 'gemini') {
                return new \App\Services\AI\GeminiClientService();
            }
            
            return new \App\Services\AI\OpenAIClientService();",
"            return new \App\Services\AI\OpenAIClientService();",
$content);

file_put_contents($path, $content);
echo "Updated AppServiceProvider";
