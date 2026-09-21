<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

interface RuleEvaluatorInterface
{
    /**
     * Evaluate the rule against the provided DOM.
     *
     * @return array{passed: bool, details: string, html_snippet: ?string}
     */
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array;
}
