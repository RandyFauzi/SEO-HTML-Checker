<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class JsonLdRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $jsonNodes = $dom->filter('script[type="application/ld+json"]');

        if ($jsonNodes->count() === 0) {
            return [
                'passed' => false,
                'details' => 'No application/ld+json script found.',
                'html_snippet' => null,
            ];
        }

        $expected = strtolower(trim($rule->expected_value ?? $rule->value ?? ''));
        $operator = strtolower($rule->operator ?? 'equals');
        $jsonPath = $rule->attribute ?? '';

        $foundMatch = false;
        $firstSnippet = '';

        $jsonNodes->each(function (Crawler $node, $i) use ($jsonPath, $operator, $expected, &$foundMatch, &$firstSnippet) {
            if ($i === 0) {
                $firstSnippet = $node->outerHtml();
            }

            if ($foundMatch) {
                return;
            } // Short-circuit if already found

            $jsonText = $node->text();
            $data = json_decode($jsonText, true);

            if (is_array($data)) {
                // If no specific path is given, we could fail or fallback to a basic search.
                // But path-based is strongly recommended.
                if (empty($jsonPath)) {
                    // Fallback basic exact search string if no path
                    if (str_contains(strtolower($jsonText), $expected)) {
                        $foundMatch = true;
                        $firstSnippet = $node->outerHtml();
                    }

                    return;
                }

                // data_get allows wildcard extraction e.g. '@graph.*.@type'
                $extracted = data_get($data, $jsonPath);

                // data_get could return an array if wildcard is used
                $valuesToCheck = is_array($extracted) ? $extracted : [$extracted];

                foreach ($valuesToCheck as $val) {
                    if ($val === null) {
                        continue;
                    }

                    $valLower = strtolower((string) $val);

                    $matched = match ($operator) {
                        'equals', '=' => $valLower === $expected,
                        'not_equals', '!=' => $valLower !== $expected,
                        'starts_with' => str_starts_with($valLower, $expected),
                        'ends_with' => str_ends_with($valLower, $expected),
                        'contains' => str_contains($valLower, $expected),
                        'not_contains' => ! str_contains($valLower, $expected),
                        default => $valLower === $expected,
                    };

                    if ($matched) {
                        $foundMatch = true;
                        $firstSnippet = $node->outerHtml();
                        break;
                    }
                }
            }
        });

        $pathMsg = empty($jsonPath) ? '' : " at path '{$jsonPath}'";

        return [
            'passed' => $foundMatch,
            'details' => $foundMatch ? "JSON-LD matched condition '{$operator}' with '{$expected}'{$pathMsg}." : "JSON-LD did not match condition '{$operator}' with '{$expected}'{$pathMsg}.",
            'html_snippet' => $firstSnippet,
        ];
    }
}
