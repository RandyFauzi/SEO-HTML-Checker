<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiClientService implements AiClientInterface
{
    public function generateStructuredOutput(string $model, string $prompt): array
    {
        $apiKey = config('services.gemini.api_key');
        $baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta/models');

        if (!$apiKey) {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $url = "{$baseUrl}/{$model}:generateContent?key={$apiKey}";

        $response = Http::withoutVerifying()
            ->acceptJson()
            ->timeout(60)
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ]
            ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Remove markdown formatting if present (Gemini might sometimes still wrap JSON)
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);

        return json_decode($text, true) ?? [];
    }

        public function generateText(string $model, string $prompt, int $retries = 3): string
    {
        $apiKey = config('services.gemini.api_key');
        $baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta/models');

        if (!$apiKey) {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $url = "{$baseUrl}/{$model}:generateContent?key={$apiKey}";

        $attempt = 0;
        $response = null;

        while ($attempt <= $retries) {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->timeout(60)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                    ]
                ]);

            if ($response->successful()) {
                break;
            }

            $status = $response->status();
            // Retry only on 429 (Rate Limit), 503 (Service Unavailable), or 500
            if (in_array($status, [429, 500, 503]) && $attempt < $retries) {
                $attempt++;
                // Exponential backoff: 2s, 4s, 8s...
                sleep((int) pow(2, $attempt));
                continue;
            }

            $this->handleError($response);
        }

        $data = $response->json();
        return trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    private function handleError($response): void
    {
        $status = $response->status();
        $errorBody = $response->json();
        
        if ($status === 429) {
            throw new RuntimeException("Gemini API sedang sibuk (Rate Limit) atau kuota habis. Silakan coba beberapa saat lagi.");
        }
        
        if ($status === 400 || $status === 401 || $status === 403) {
            $message = $errorBody['error']['message'] ?? 'API Key Gemini tidak valid atau request salah.';
            throw new RuntimeException("Gemini Error: " . $message);
        }

        throw new RuntimeException("Gemini API Error: " . ($errorBody['error']['message'] ?? $response->body()));
    }
}
