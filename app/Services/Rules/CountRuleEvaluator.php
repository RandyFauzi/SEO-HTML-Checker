<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class CountRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $nodes = $dom->filter($rule->target_selector);
        $actual = $nodes->count();

        $expected = (int) ($rule->expected_value ?? $rule->min_value ?? 0);
        $operator = $rule->operator ?? '=';

        $passed = match ($operator) {
            '>' => $actual > $expected,
            '>=' => $actual >= $expected,
            '<' => $actual < $expected,
            '<=' => $actual <= $expected,
            '!=' => $actual !== $expected,
            'between' => $actual >= (int) $rule->min_value && $actual <= (int) $rule->max_value,
            default => $actual === $expected,
        };

        $operatorString = $operator === 'between' ? "between {$rule->min_value} and {$rule->max_value}" : "{$operator} {$expected}";

        return [
            'passed' => $passed,
            'details' => "Expected count {$operatorString}, found {$actual}.",
            'html_snippet' => $actual > 0 ? $nodes->first()->outerHtml() : null,
        ];
    }
}
