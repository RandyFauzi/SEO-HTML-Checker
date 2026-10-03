<?php

namespace App\Services\AI;

interface AiClientInterface
{
    /**
     * Generate structured JSON output based on the prompt.
     */
    public function generateStructuredOutput(string $model, string $prompt): array;

    /**
     * Generate plain text (or HTML) output based on the prompt.
     */
    public function generateText(string $model, string $prompt): string;
}
