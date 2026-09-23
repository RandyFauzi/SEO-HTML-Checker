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

        $operatorString = $operator === 'between' 
            ? "{$rule->min_value} - {$rule->max_value} elemen" 
            : "{$operator} {$expected} elemen";

        $issue = null;
        $reason = null;

        if (!$passed) {
            $issue = "Jumlah elemen {$rule->name} tidak sesuai.";
            $reason = "Rule menetapkan jumlah harus {$operatorString}, tetapi ditemukan {$actual} elemen.";
        }
        
        $snippets = [];
        if ($actual > 0) {
            foreach ($nodes as $node) {
                if (count($snippets) < 3) { // Show up to 3 for context
                    $snippets[] = $node->ownerDocument->saveHTML($node);
                }
            }
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $operatorString,
            'actual' => "{$actual} elemen",
            'selector' => $rule->target_selector,
            'attribute' => null,
            'html_snippet' => !empty($snippets) ? implode("\n", $snippets) : null,
        ];
    }
}
