<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class RegexRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $nodes = $dom->filter($rule->target_selector);

        if ($nodes->count() === 0) {
            return [
                'passed' => false,
                'details' => 'Selector not found for regex check.',
                'html_snippet' => null,
            ];
        }

        $htmlSnippet = $nodes->first()->outerHtml();
        $text = $this->extractContent($nodes->first(), $rule);

        // Security limit: do not execute regex on extremely large strings to prevent CPU spikes
        if (strlen($text) > 50000) {
            return [
                'passed' => false,
                'details' => 'Content too large for regex evaluation (Security limit).',
                'html_snippet' => $htmlSnippet,
            ];
        }

        $regex = $rule->regex_pattern ?? $rule->expected_value ?? $rule->value ?? '';

        if (empty($regex)) {
            return [
                'passed' => false,
                'details' => 'No regex pattern provided.',
                'html_snippet' => $htmlSnippet,
            ];
        }

        if (! str_starts_with($regex, '/') && ! str_starts_with($regex, '#') && ! str_starts_with($regex, '@')) {
            $regex = '@'.str_replace('@', '\@', $regex).'@u';
        }

        // Set PCRE backtrack limit temporarily to prevent catastrophic backtracking
        $originalBacktrack = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '10000');

        try {
            $result = @preg_match($regex, $text);

            if ($result === false) {
                return [
                    'passed' => false,
                    'details' => 'Regex execution failed or pattern is invalid.',
                    'html_snippet' => $htmlSnippet,
                ];
            }

            $passed = $result === 1;

        } finally {
            // Restore original limit
            ini_set('pcre.backtrack_limit', $originalBacktrack);
        }

        return [
            'passed' => $passed,
            'details' => $passed ? 'Matched regex.' : 'Did not match regex.',
            'html_snippet' => $htmlSnippet,
        ];
    }
}
