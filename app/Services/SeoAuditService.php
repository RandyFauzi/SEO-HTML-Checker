<?php

namespace App\Services;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class SeoAuditService
{
    protected HtmlFetcher $fetcher;

    protected RuleEngine $engine;

    public function __construct(HtmlFetcher $fetcher, RuleEngine $engine)
    {
        $this->fetcher = $fetcher;
        $this->engine = $engine;
    }

    public function audit(array $lpUrls, array $ampUrls = []): array
    {
        $activeRules = SeoRule::active()->get();
        $compareRules = $activeRules->where('rule_type', 'compare_amp');

        // Ensure both sets of URLs are unique across the whole fetching pool
        $allUrlsToFetch = array_unique(array_filter(array_merge($lpUrls, $ampUrls)));

        // Fetch concurrently
        $fetchResults = $this->fetcher->fetchConcurrent($allUrlsToFetch);

        $results = [];

        foreach ($lpUrls as $index => $lpUrl) {
            $results[$index] = [
                'lp_url' => $lpUrl,
                'amp_url' => $ampUrls[$index] ?? null,
                'error' => null,
                'checks' => [],
            ];

            // Check if LP fetch failed
            if (isset($fetchResults[$lpUrl]['error'])) {
                $results[$index]['error'] = 'LP Error: '.$fetchResults[$lpUrl]['error'];

                continue;
            }

            $lpHtml = $fetchResults[$lpUrl]['html'] ?? '';
            $lpCrawler = new Crawler($lpHtml);

            $ampCrawler = null;
            $ampUrl = $ampUrls[$index] ?? null;
            if ($ampUrl) {
                if (isset($fetchResults[$ampUrl]['error'])) {
                    $results[$index]['error'] = 'AMP Error: '.$fetchResults[$ampUrl]['error'];

                    continue;
                }
                $ampHtml = $fetchResults[$ampUrl]['html'] ?? '';
                $ampCrawler = new Crawler($ampHtml);
            }

            // Run evaluations
            foreach ($activeRules as $rule) {
                if ($rule->rule_type === 'compare_amp' && ! $ampCrawler) {
                    continue; // Skip if no AMP URL provided
                }

                $evalResult = $this->engine->evaluate($lpCrawler, $rule, $ampCrawler);

                $results[$index]['checks'][] = [
                    'rule' => $rule->name,
                    'status' => $evalResult['status'],
                    'details' => $evalResult['details'],
                    'html_snippet' => $evalResult['html_snippet'],
                ];
            }
        }

        return $results;
    }
}
