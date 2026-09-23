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
                'issue' => 'Elemen tidak ditemukan.',
                'reason' => "Tidak ada elemen yang cocok dengan selector `{$rule->target_selector}`.",
                'expected' => 'Elemen harus ada',
                'actual' => 'Elemen tidak ditemukan',
                'selector' => $rule->target_selector,
                'attribute' => $rule->attribute,
                'html_snippet' => null,
            ];
        }

        $htmlSnippet = $nodes->first()->outerHtml();
        $text = $this->extractContent($nodes->first(), $rule);
        $length = mb_strlen($text);

        $operator = $rule->operator ?? '<=';
        $expectedValue = (int) ($rule->expected_value ?? $rule->max_value ?? 0);

        $passed = match ($operator) {
            '>' => $length > $expectedValue,
            '>=' => $length >= $expectedValue,
            '<' => $length < $expectedValue,
            '<=' => $length <= $expectedValue,
            '=' => $length === $expectedValue,
            'between' => $length >= (int) $rule->min_value && $length <= (int) $rule->max_value,
            default => $length <= $expectedValue,
        };

        $operatorString = $operator === 'between' 
            ? "{$rule->min_value} - {$rule->max_value} karakter" 
            : "{$operator} {$expectedValue} karakter";

        $issue = null;
        $reason = null;

        if (!$passed) {
            if ($operator === 'between') {
                if ($length < $rule->min_value) {
                    $issue = "Panjang {$rule->name} terlalu pendek.";
                    $reason = "Panjang berada di bawah minimum batas rule.";
                } else {
                    $issue = "Panjang {$rule->name} terlalu panjang.";
                    $reason = "Panjang melebihi batas maksimum rule.";
                }
            } else {
                $issue = "Panjang {$rule->name} tidak sesuai.";
                $reason = "Panjang aktual tidak memenuhi syarat `{$operator} {$expectedValue}`.";
            }
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $operatorString,
            'actual' => "{$length} karakter",
            'selector' => $rule->target_selector,
            'attribute' => $rule->attribute,
            'html_snippet' => $htmlSnippet,
        ];
    }
}
