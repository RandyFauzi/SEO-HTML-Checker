<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class LengthRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $nodes = $dom->filter($rule->target_selector);

        if ($nodes->count() === 0) {
            return [
                'passed' => false,
                'details' => 'Selector not found for length check.',
                'html_snippet' => null,
            ];
        }

        $htmlSnippet = $nodes->first()->outerHtml();
        $text = $this->extractContent($nodes->first(), $rule);
        $length = mb_strlen($text);

        $operator = $rule->operator ?? '<=';
        $expected = (int) ($rule->expected_value ?? $rule->max_value ?? 0);

        $passed = match ($operator) {
            '>' => $length > $expected,
            '>=' => $length >= $expected,
            '<' => $length < $expected,
            '<=' => $length <= $expected,
            '=' => $length === $expected,
            'between' => $length >= (int) $rule->min_value && $length <= (int) $rule->max_value,
            default => $length <= $expected,
        };

        $operatorString = $operator === 'between' ? "between {$rule->min_value} and {$rule->max_value}" : "{$operator} {$expected}";

        return [
            'passed' => $passed,
            'details' => "Length is {$length} (Expected: {$operatorString}).",
            'html_snippet' => $htmlSnippet,
        ];
    }
}
