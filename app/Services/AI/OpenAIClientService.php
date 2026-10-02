<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;
use RuntimeException;

class OpenAIClientService
{
    public function generateStructuredOutput(string $model, string $prompt): array
    {
        $apiKey = config('services.openai.api_key');
        $baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');

        if (!$apiKey) {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post($baseUrl . '/responses', [
                'model' => env('OPENAI_RULE_BUILDER_MODEL', 'gpt-6-astra'),
                'input' => $prompt,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("OpenAI API Error: " . $response->body());
        }

        $data = $response->json();
        $text = $data['output_text'] ?? '';
        
        // Remove markdown formatting if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);

        return json_decode($text, true) ?? [];
    }
}
