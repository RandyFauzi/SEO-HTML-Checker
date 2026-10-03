<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class NotExistRuleEvaluator implements RuleEvaluatorInterface
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
        $passed = $nodes->count() === 0;

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? 'Elemen terlarang ditemukan';
            $reason = $rule->reason_template ?? 'Elemen ' . $selector . ' seharusnya tidak ada di HTML.';
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => 'Elemen tidak boleh ada (0)',
            'actual' => $passed ? 'Tidak ditemukan (Aman)' : 'Ditemukan ' . $nodes->count() . ' elemen',
            'selector' => $selector,
            'attribute' => null,
            'html_snippet' => $passed ? null : substr($nodes->first()->outerHtml(), 0, 500),
        ];
    }
}
