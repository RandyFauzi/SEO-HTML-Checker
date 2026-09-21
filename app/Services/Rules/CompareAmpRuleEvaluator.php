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
                'details' => 'AMP DOM not provided for comparison.',
                'html_snippet' => null,
            ];
        }

        $selector = $rule->target_selector;
        $lpNodes = $dom->filter($selector);
        $ampNodes = $ampDom->filter($selector);

        $lpValue = $lpNodes->count() > 0 ? $this->extractContent($lpNodes->first(), $rule) : null;
        $ampValue = $ampNodes->count() > 0 ? $this->extractContent($ampNodes->first(), $rule) : null;

        $lpSnippet = $lpNodes->count() > 0 ? $lpNodes->first()->outerHtml() : null;
        $ampSnippet = $ampNodes->count() > 0 ? $ampNodes->first()->outerHtml() : null;

        $passed = ($lpValue === $ampValue && $lpValue !== null);

        return [
            'passed' => $passed,
            'details' => $passed ? 'Match found.' : "Mismatch. LP: '{$lpValue}' | AMP: '{$ampValue}'",
            'html_snippet' => "LP:\n".($lpSnippet ?? 'N/A')."\n\nAMP:\n".($ampSnippet ?? 'N/A'),
        ];
    }
}
