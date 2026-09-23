<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class LengthRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? '';
        $attribute = $config['attribute'] ?? null;
        
        if (empty($selector)) {
            return [
                'passed' => false,
                'issue' => "Selector kosong",
                'reason' => "Rule tidak memiliki selector konfigurasi.",
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
        
        // Trait will read from $rule->config automatically
        $text = $this->extractContent($nodes->first(), $rule);
        
        $length = mb_strlen(trim($text));

        $operator = $config['operator'] ?? '<=';
        $expectedValue = (int) ($config['expected'] ?? $config['max'] ?? 0);
        $min = (int) ($config['min'] ?? 0);
        $max = (int) ($config['max'] ?? 0);

        $passed = match ($operator) {
            '>' => $length > $expectedValue,
            '>=' => $length >= $expectedValue,
            '<' => $length < $expectedValue,
            '<=' => $length <= $expectedValue,
            '=' => $length === $expectedValue,
            'between' => $length >= $min && $length <= $max,
            default => $length <= $expectedValue,
        };

        $operatorString = $operator === 'between' 
            ? "{$min} - {$max} karakter" 
            : "{$operator} {$expectedValue} karakter";

        $issue = null;
        $reason = null;

        if (!$passed) {
            $issue = $rule->issue_message ?? "Panjang {$rule->name} tidak sesuai.";
            if ($operator === 'between') {
                if ($length < $min) {
                    $reason = $rule->reason_template ?? "Panjang teks terlalu pendek.";
                } else {
                    $reason = $rule->reason_template ?? "Panjang teks melebihi batas maksimum.";
                }
            } else {
                $reason = $rule->reason_template ?? "Panjang aktual tidak memenuhi syarat `{$operator} {$expectedValue}`.";
            }
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $operatorString,
            'actual' => "{$length} karakter",
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => $htmlSnippet,
        ];
    }
}
