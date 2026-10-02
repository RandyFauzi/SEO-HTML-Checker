<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIClientService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
    }

    public function generateStructuredOutput(string $model, array $messages, array $jsonSchema): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("OPENAI_API_KEY is not set.");
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'response_format' => [
                    'type' => 'json_object'
                ],
                'temperature' => 0.1,
            ]);

        if ($response->failed()) {
            throw new Exception("OpenAI API Error: " . $response->body());
        }

        $content = $response->json('choices.0.message.content');
        return json_decode($content, true);
    }
}
