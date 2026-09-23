<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class ExistsRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? '';
        
        if (empty($selector)) {
            return [
                'passed' => false,
                'issue' => "Selector kosong",
                'reason' => "Rule tidak memiliki selector konfigurasi.",
                'selector' => $selector,
            ];
        }

        $nodes = $dom->filter($selector);
        $passed = $nodes->count() > 0;
        
        $issue = null;
        $reason = null;

        if (!$passed) {
            $issue = $rule->issue_message ?? "Elemen {$rule->name} tidak ditemukan.";
            $reason = $rule->reason_template ?? "Rule mewajibkan keberadaan elemen ini, tetapi tidak ditemukan di dalam HTML.";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => 'Elemen harus ada (Minimal 1)',
            'actual' => $passed ? 'Ditemukan' : 'Tidak ditemukan',
            'selector' => $selector,
            'attribute' => null,
            'html_snippet' => $passed ? substr($nodes->first()->outerHtml(), 0, 500) : null,
        ];
    }
}
