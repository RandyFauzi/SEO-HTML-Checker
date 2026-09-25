<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class CompareAmpRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? '';
        $attribute = $config['attribute'] ?? null;

        if (! $ampDom) {
            return [
                'passed' => true, // We don't want it to fail
                'skipped' => true, // custom flag we handle in RuleEngine
                'issue' => 'Skipped',
                'reason' => 'No AMP URL was provided for this audit.',
                'expected' => null,
                'actual' => 'Skipped',
                'selector' => $selector,
                'attribute' => $attribute,
                'html_snippet' => null,
            ];
        }

        $lpNodes = $dom->filter($selector);
        $ampNodes = $ampDom->filter($selector);

        $rule->setAttribute('attribute', $attribute);

        $lpValue = $lpNodes->count() > 0 ? $this->extractContent($lpNodes->first(), $rule) : null;
        $ampValue = $ampNodes->count() > 0 ? $this->extractContent($ampNodes->first(), $rule) : null;

        $lpSnippet = $lpNodes->count() > 0 ? substr($lpNodes->first()->outerHtml(), 0, 150) : 'N/A';
        $ampSnippet = $ampNodes->count() > 0 ? substr($ampNodes->first()->outerHtml(), 0, 150) : 'N/A';

        $passed = ($lpValue === $ampValue && $lpValue !== null);

        $issue = null;
        $reason = null;

        if (! $passed) {
            if ($lpValue === null || $ampValue === null) {
                $issue = $rule->issue_message ?? 'Elemen tidak ditemukan di salah satu versi.';
                $reason = $rule->reason_template ?? 'Elemen wajib ada di LP maupun AMP untuk diperbandingkan.';
            } else {
                $issue = $rule->issue_message ?? "Konten {$rule->name} berbeda antara LP dan AMP.";
                $reason = $rule->reason_template ?? 'Rule mewajibkan konten ini harus sama persis (identik) di kedua versi halaman.';
            }
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => 'LP dan AMP memiliki konten yang identik',
            'actual' => $passed ? 'Identik' : "LP: '".($lpValue ?? 'null')."' | AMP: '".($ampValue ?? 'null')."'",
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => "LP:\n{$lpSnippet}\n\nAMP:\n{$ampSnippet}",
        ];
    }
}
