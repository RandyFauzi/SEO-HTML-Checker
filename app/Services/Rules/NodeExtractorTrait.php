<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

trait NodeExtractorTrait
{
    /**
     * Extracts content from a DOM node based on the rule configuration.
     * If the rule specifies an attribute, it returns the attribute value.
     * Otherwise, it returns the node's inner text.
     */
    protected function extractContent(Crawler $node, SeoRule $rule): string
    {
        if (! empty($rule->attribute)) {
            return trim($node->attr($rule->attribute) ?? '');
        }

        return trim($node->text());
    }
}
