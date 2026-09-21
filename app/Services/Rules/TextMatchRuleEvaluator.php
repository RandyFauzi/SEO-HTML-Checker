<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class TextMatchRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $nodes = $dom->filter($rule->target_selector);

        if ($nodes->count() === 0) {
            return [
                'passed' => false,
                'details' => 'Selector not found for text match.',
                'html_snippet' => null,
            ];
        }

        $htmlSnippet = $nodes->first()->outerHtml();
        $text = $this->extractContent($nodes->first(), $rule);

        $operator = strtolower($rule->operator ?? 'contains');
        $expected = $rule->expected_value ?? $rule->value ?? '';

        // Case-insensitive comparisons for simplicity
        $textLower = strtolower($text);
        $expectedLower = strtolower($expected);

        $passed = match ($operator) {
            'equals', '=' => $textLower === $expectedLower,
            'not_equals', '!=' => $textLower !== $expectedLower,
            'starts_with' => str_starts_with($textLower, $expectedLower),
            'ends_with' => str_ends_with($textLower, $expectedLower),
            'not_contains' => ! str_contains($textLower, $expectedLower),
            default => str_contains($textLower, $expectedLower),
        };

        return [
            'passed' => $passed,
            'details' => $passed ? "Matched condition '{$operator}' with '{$expected}'." : "Did not match condition '{$operator}' with '{$expected}'.",
            'html_snippet' => $htmlSnippet,
        ];
    }
}
