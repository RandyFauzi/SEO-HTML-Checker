<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class FallbackAiClientService implements AiClientInterface
{
    protected $primary;
    protected $fallback;

    public function __construct(AiClientInterface $primary, AiClientInterface $fallback)
    {
        $this->primary = $primary;
        $this->fallback = $fallback;
    }

    public function generateStructuredOutput(string $model, string $prompt): array
    {
        try {
            return $this->primary->generateStructuredOutput($model, $prompt);
        } catch (\Exception $e) {
            Log::warning('Primary AI Provider failed (Structured Output). Falling back to secondary.', [
                'error' => $e->getMessage()
            ]);
            return $this->fallback->generateStructuredOutput($model, $prompt);
        }
    }

    public function generateText(string $model, string $prompt): string
    {
        try {
            return $this->primary->generateText($model, $prompt);
        } catch (\Exception $e) {
            Log::warning('Primary AI Provider failed (Text). Falling back to secondary.', [
                'error' => $e->getMessage()
            ]);
            return $this->fallback->generateText($model, $prompt);
        }
    }
}
