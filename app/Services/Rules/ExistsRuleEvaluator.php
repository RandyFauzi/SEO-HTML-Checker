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

        return [
            'passed' => $passed,
            'details' => $passed ? 'Selector found.' : 'Selector not found.',
            'html_snippet' => $passed ? $nodes->first()->outerHtml() : null,
        ];
    }
}
