<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;
use RuntimeException;

class OpenAIClientService implements AiClientInterface
{
    public function generateStructuredOutput(string $model, string $prompt): array
    {
        $apiKey = config('services.openai.api_key');
        $baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');

        if (!$apiKey) {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
        }

        $response = Http::withoutVerifying()
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(300)
            ->post($baseUrl . '/chat/completions', [
                'model' => env('OPENAI_REMEDIATION_MODEL', $model),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'response_format' => ['type' => 'json_object'] // Or rely on prompt
            ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? '';
        
        // Remove markdown formatting if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);

        return json_decode($text, true) ?? [];
    }

    public function generateText(string $model, string $prompt): string
    {
        $apiKey = config('services.openai.api_key');
        $baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');

        if (!$apiKey) {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
        }

        $response = Http::withoutVerifying()
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(300)
            ->post($baseUrl . '/chat/completions', [
                'model' => env('OPENAI_REMEDIATION_MODEL', $model),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        $data = $response->json();
        return trim($data['choices'][0]['message']['content'] ?? '');
    }

    private function handleError($response): void
    {
        $status = $response->status();
        $errorBody = $response->json();
        
        if ($status === 429) {
            $errorCode = $errorBody['error']['code'] ?? '';
            if ($errorCode === 'insufficient_quota' || str_contains(strtolower($response->body()), 'quota')) {
                throw new RuntimeException("Saldo (Credit) OpenAI Anda telah habis atau limit tercapai. Silakan isi ulang saldo di dashboard OpenAI.");
            }
            throw new RuntimeException("OpenAI sedang sibuk (Rate Limit). Silakan coba beberapa saat lagi.");
        }
        
        if ($status === 401) {
            throw new RuntimeException("API Key OpenAI tidak valid. Periksa kembali konfigurasi OPENAI_API_KEY Anda.");
        }

        throw new RuntimeException("OpenAI API Error: " . ($errorBody['error']['message'] ?? $response->body()));
    }
}
