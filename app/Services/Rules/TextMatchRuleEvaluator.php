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
        $skipIfMissing = filter_var($config['skip_if_missing'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $scope = strtolower($config['scope'] ?? 'first'); // 'first', 'all', 'any'

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
            if ($skipIfMissing) {
                return [
                    'passed' => true,
                    'skipped' => true,
                    'issue' => null,
                    'reason' => 'Elemen di-skip karena tidak ditemukan di HTML.',
                    'expected' => 'Opsional',
                    'actual' => 'Tidak ditemukan (di-skip)',
                    'selector' => $selector,
                    'attribute' => $attribute,
                    'html_snippet' => null,
                ];
            }
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

        $operator = strtolower($config['operator'] ?? 'contains');
        $expected = $config['expected_value'] ?? $config['expected'] ?? '';
        
        $expectedStr = '';
        if ($operator === 'in_list' && is_array($expected)) {
            $expectedStr = implode(', ', $expected);
        } else {
            $expectedStr = (string) $expected;
        }

        $passed = ($scope === 'any') ? false : true;
        $failedNodes = [];
        $firstSnippet = null;
        $actualSnippet = null;

        foreach ($nodes as $index => $node) {
            if ($scope === 'first' && $index > 0) break;
            
            $crawlerNode = new Crawler($node);
            $htmlSnippet = substr($crawlerNode->outerHtml(), 0, 500);
            if ($index === 0) $firstSnippet = $htmlSnippet;

            $text = $this->extractContent($crawlerNode, $rule);
            $textLower = strtolower($text);

            if ($operator === 'in_list' && is_array($expected)) {
                $nodePassed = in_array($textLower, array_map('strtolower', $expected), true);
            } else {
                $expectedLower = strtolower((string) $expected);
                $nodePassed = match ($operator) {
                    'equals', '=' => $textLower === $expectedLower,
                    'not_equals', '!=' => $textLower !== $expectedLower,
                    'starts_with' => str_starts_with($textLower, $expectedLower),
                    'ends_with' => str_ends_with($textLower, $expectedLower),
                    'not_contains' => ! str_contains($textLower, $expectedLower),
                    default => str_contains($textLower, $expectedLower),
                };
            }

            if ($scope === 'any') {
                if ($nodePassed) {
                    $passed = true;
                    $actualSnippet = mb_strimwidth($text, 0, 50, '...');
                    break;
                }
            } else {
                if (!$nodePassed) {
                    $passed = false;
                    if (count($failedNodes) < 3) {
                        $failedNodes[] = $htmlSnippet;
                    }
                    $actualSnippet = mb_strimwidth($text, 0, 50, '...');
                }
            }
        }

        if ($passed && $actualSnippet === null) {
            $actualSnippet = 'Semua elemen sesuai teks';
        }

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? "Teks {$rule->name} tidak sesuai ekspektasi.";
            $reason = $rule->reason_template ?? "Nilai yang diekstrak tidak memenuhi kondisi `{$operator}` terhadap `{$expectedStr}`.";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "{$operator} '{$expectedStr}'",
            'actual' => $actualSnippet,
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => ! $passed && ! empty($failedNodes) ? implode("\n", $failedNodes) : $firstSnippet,
        ];
    }
}
