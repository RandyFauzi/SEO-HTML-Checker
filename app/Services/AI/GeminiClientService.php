<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiClientService implements AiClientInterface
{
    public function generateStructuredOutput(string $model, string $prompt): array
    {
        $actualModel = env('GEMINI_REMEDIATION_MODEL', $model);
        // Normalize model name to prevent 404 errors
        if (str_contains($actualModel, 'gpt')) {
            $actualModel = 'gemini-flash-latest';
        }
        
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi di file .env.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$actualModel}:generateContent?key={$apiKey}";

        $maxRetries = 2;
        $attempt = 0;
        $response = null;

        while ($attempt <= $maxRetries) {
            $attempt++;
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->timeout(300)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if ($response->successful()) {
                break;
            }

            // If 503 (Overloaded) or 429 (Rate Limit) and we have retries left, wait and retry
            if (($response->status() === 503 || $response->status() === 429) && $attempt <= $maxRetries) {
                sleep(2); // Wait 2 seconds before retry
                continue;
            }
            
            // Otherwise, break to handle error
            break;
        }

        if ($response->failed()) {
            $this->handleError($response);
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Sometimes the model might wrap the output in markdown block despite responseMimeType
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);

        return json_decode($text, true) ?? [];
    }

    public function generateText(string $model, string $prompt): string
    {
        $actualModel = env('GEMINI_REMEDIATION_MODEL', $model);
        // Normalize model name to prevent 404 errors
        if (str_contains($actualModel, 'gpt')) {
            $actualModel = 'gemini-flash-latest';
        }
        
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi di file .env.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$actualModel}:generateContent?key={$apiKey}";

        $maxRetries = 2;
        $attempt = 0;
        $response = null;

        while ($attempt <= $maxRetries) {
            $attempt++;
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->timeout(300)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                break;
            }

            // If 503 (Overloaded) or 429 (Rate Limit) and we have retries left, wait and retry
            if (($response->status() === 503 || $response->status() === 429) && $attempt <= $maxRetries) {
                sleep(2); // Wait 2 seconds before retry
                continue;
            }
            
            // Otherwise, break to handle error
            break;
        }

        if ($response->failed()) {
            $this->handleError($response);
        }

        $data = $response->json();
        return trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    private function handleError($response): void
    {
        $status = $response->status();
        $errorBody = $response->json();
        
        $errorMessage = $errorBody['error']['message'] ?? $response->body();
        
        if ($status === 400 && str_contains(strtolower($errorMessage), 'api key')) {
            throw new RuntimeException("API Key Gemini tidak valid.");
        }
        if ($status === 429) {
            throw new RuntimeException("Limit API Gemini tercapai (Rate Limit). Silakan coba lagi nanti.");
        }
        if ($status === 503) {
            throw new RuntimeException("Server Google Gemini sedang sibuk atau kelebihan beban (Overloaded). Silakan coba lagi dalam beberapa detik.");
        }

        throw new RuntimeException("Gemini API Error: " . $errorMessage);
    }
}
