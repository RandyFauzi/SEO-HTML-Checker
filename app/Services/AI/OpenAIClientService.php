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

        $response = Http::withoutVerifying()
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post($baseUrl . '/responses', [
                'model' => env('OPENAI_RULE_BUILDER_MODEL', 'gpt-6-astra'),
                'input' => $prompt,
            ]);

        if ($response->failed()) {
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

        $data = $response->json();
        $text = $data['output_text'] ?? '';
        
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
            ->timeout(60)
            ->post($baseUrl . '/responses', [
                'model' => $model,
                'input' => $prompt,
            ]);

        if ($response->failed()) {
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

        $data = $response->json();
        return trim($data['output_text'] ?? '');
    }
}
