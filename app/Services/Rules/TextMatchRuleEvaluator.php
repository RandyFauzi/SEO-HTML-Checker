<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class TextMatchRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? '';
        $attribute = $config['attribute'] ?? null;

        if (empty($selector)) {
            return [
                'passed' => false,
                'issue' => 'Selector kosong',
                'reason' => 'Rule tidak memiliki selector konfigurasi.',
                'selector' => $selector,
            ];
        }

        $nodes = $dom->filter($selector);

        if ($nodes->count() === 0) {
            return [
                'passed' => false,
                'issue' => 'Elemen tidak ditemukan.',
                'reason' => "Tidak ada elemen yang cocok dengan selector `{$selector}`.",
                'expected' => 'Elemen harus ada',
                'actual' => 'Elemen tidak ditemukan',
                'selector' => $selector,
                'attribute' => $attribute,
                'html_snippet' => null,
            ];
        }

        $htmlSnippet = substr($nodes->first()->outerHtml(), 0, 500);
        $text = $this->extractContent($nodes->first(), $rule);

        $operator = strtolower($config['operator'] ?? 'contains');
        $expected = $config['expected_value'] ?? $config['expected'] ?? '';

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

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? "Teks {$rule->name} tidak sesuai ekspektasi.";
            $reason = $rule->reason_template ?? "Nilai yang diekstrak tidak memenuhi kondisi `{$operator}` terhadap `{$expected}`.";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "{$operator} '{$expected}'",
            'actual' => mb_strimwidth($text, 0, 50, '...'),
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => $htmlSnippet,
        ];
    }
}
