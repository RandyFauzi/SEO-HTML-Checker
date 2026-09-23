<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class ExistsRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $nodes = $dom->filter($rule->target_selector);
        $passed = $nodes->count() > 0;
        
        $issue = null;
        $reason = null;

        if (!$passed) {
            $issue = "Elemen {$rule->name} tidak ditemukan.";
            $reason = "Rule mewajibkan keberadaan elemen ini, tetapi tidak ditemukan di dalam HTML.";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => 'Elemen harus ada (Minimal 1)',
            'actual' => $passed ? 'Ditemukan' : 'Tidak ditemukan',
            'selector' => $rule->target_selector,
            'attribute' => null,
            'html_snippet' => $passed ? $nodes->first()->outerHtml() : null,
        ];
    }
}
