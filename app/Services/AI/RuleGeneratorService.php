<?php

namespace App\Services\AI;

class RuleGeneratorService
{
    protected OpenAIClientService $client;

    public function __construct(OpenAIClientService $client)
    {
        $this->client = $client;
    }

    public function generate(string $userPrompt): array
    {
        // TODO: Implement prompt and schema definition
        return [];
    }
}
