<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class CountRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? '';

        if (empty($selector)) {
            return [
                'passed' => false,
                'issue' => 'Selector kosong',
                'reason' => 'Rule tidak memiliki selector konfigurasi.',
                'selector' => $selector,
            ];
        }

        $nodes = $dom->filter($selector);
        $actual = $nodes->count();

        $expected = (int) ($config['expected'] ?? $config['min'] ?? 0);
        $operator = $config['operator'] ?? '=';

        $min = (int) ($config['min'] ?? 0);
        $max = (int) ($config['max'] ?? 0);

        $passed = match ($operator) {
            '>' => $actual > $expected,
            '>=' => $actual >= $expected,
            '<' => $actual < $expected,
            '<=' => $actual <= $expected,
            '!=' => $actual !== $expected,
            'between' => $actual >= $min && $actual <= $max,
            default => $actual === $expected,
        };

        $operatorString = $operator === 'between'
            ? "{$min} - {$max} elemen"
            : "{$operator} {$expected} elemen";

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? "Jumlah elemen {$rule->name} tidak sesuai.";
            $reason = $rule->reason_template ?? "Rule menetapkan jumlah harus {$operatorString}, tetapi ditemukan {$actual} elemen.";
        }

        $snippets = [];
        if ($actual > 0) {
            foreach ($nodes as $node) {
                if (count($snippets) < 3) {
                    $snippets[] = substr($node->ownerDocument->saveHTML($node), 0, 150);
                }
            }
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $operatorString,
            'actual' => "{$actual} elemen",
            'selector' => $selector,
            'attribute' => null,
            'html_snippet' => ! empty($snippets) ? implode("\n", $snippets) : null,
        ];
    }
}
