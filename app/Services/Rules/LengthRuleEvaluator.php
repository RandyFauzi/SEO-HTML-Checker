<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class LengthRuleEvaluator implements RuleEvaluatorInterface
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
                'issue' => strtolower($rule->name) . ' tidak ditemukan',
                'reason' => "Tidak ada elemen yang cocok dengan selector `{$selector}`.",
                'expected' => 'Elemen harus ada',
                'actual' => 'Elemen tidak ditemukan',
                'selector' => $selector,
                'attribute' => $attribute,
                'html_snippet' => null,
            ];
        }

        $operator = $config['operator'] ?? '<=';
        $expectedValue = (int) ($config['expected'] ?? $config['max'] ?? 0);
        $min = (int) ($config['min'] ?? 0);
        $max = (int) ($config['max'] ?? 0);

        $operatorString = $operator === 'between'
            ? "{$min} - {$max} karakter"
            : "{$operator} {$expectedValue} karakter";

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
            $normalizedText = trim(preg_replace('/\s+/', ' ', $text));
            $length = mb_strlen($normalizedText);

            $nodePassed = match ($operator) {
                '>' => $length > $expectedValue,
                '>=' => $length >= $expectedValue,
                '<' => $length < $expectedValue,
                '<=' => $length <= $expectedValue,
                '=' => $length === $expectedValue,
                'between' => $length >= $min && $length <= $max,
                default => $length <= $expectedValue,
            };

            if ($scope === 'any') {
                if ($nodePassed) {
                    $passed = true;
                    $actualSnippet = "{$length} karakter";
                    break;
                }
            } else {
                if (!$nodePassed) {
                    $passed = false;
                    if (count($failedNodes) < 3) {
                        $failedNodes[] = $htmlSnippet;
                    }
                    $actualSnippet = "{$length} karakter";
                }
            }
        }

        if ($passed && $actualSnippet === null) {
            $actualSnippet = 'Semua elemen sesuai panjangnya';
        }

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? "panjang " . strtolower($rule->name) . " tidak sesuai";
            if ($operator === 'between') {
                $reason = $rule->reason_template ?? 'Panjang teks berada di luar rentang yang diizinkan.';
            } else {
                $reason = $rule->reason_template ?? "Panjang aktual tidak memenuhi syarat `{$operator} {$expectedValue}`.";
            }
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $operatorString,
            'actual' => $actualSnippet,
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => ! $passed && ! empty($failedNodes) ? implode("\n", $failedNodes) : $firstSnippet,
            'severity_override' => 'warning', // Downgrade length checks to warning (heuristic)
        ];
    }
}
