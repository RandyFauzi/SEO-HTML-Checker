<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class CompareAmpRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        if (! $ampDom) {
            return [
                'passed' => false,
                'issue' => 'DOM AMP tidak tersedia',
                'reason' => 'Untuk membandingkan rule ini, URL AMP wajib disertakan saat melakukan pengecekan.',
                'expected' => 'Terdapat halaman AMP',
                'actual' => 'Hanya LP yang di-scan',
                'selector' => $rule->target_selector,
                'attribute' => null,
                'html_snippet' => null,
            ];
        }

        $selector = $rule->target_selector;
        $lpNodes = $dom->filter($selector);
        $ampNodes = $ampDom->filter($selector);

        $lpValue = $lpNodes->count() > 0 ? $this->extractContent($lpNodes->first(), $rule) : null;
        $ampValue = $ampNodes->count() > 0 ? $this->extractContent($ampNodes->first(), $rule) : null;

        $lpSnippet = $lpNodes->count() > 0 ? $lpNodes->first()->outerHtml() : 'N/A';
        $ampSnippet = $ampNodes->count() > 0 ? $ampNodes->first()->outerHtml() : 'N/A';

        $passed = ($lpValue === $ampValue && $lpValue !== null);

        $issue = null;
        $reason = null;

        if (!$passed) {
            if ($lpValue === null || $ampValue === null) {
                $issue = "Elemen tidak ditemukan di salah satu versi.";
                $reason = "Elemen `{$selector}` wajib ada di LP maupun AMP untuk diperbandingkan.";
            } else {
                $issue = "Konten {$rule->name} berbeda antara LP dan AMP.";
                $reason = "Rule mewajibkan konten ini harus sama persis (identik) di kedua versi halaman.";
            }
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "LP dan AMP memiliki konten yang identik",
            'actual' => $passed ? "Identik" : "LP: '" . ($lpValue ?? 'null') . "' | AMP: '" . ($ampValue ?? 'null') . "'",
            'selector' => $selector,
            'attribute' => $rule->attribute,
            'html_snippet' => "LP:\n{$lpSnippet}\n\nAMP:\n{$ampSnippet}",
        ];
    }
}
