<?php

namespace App\Services;

use App\DTO\UrlAuditResult;
use App\Models\SeoRule;
use App\Enums\RuleType;
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

    /**
     * @return UrlAuditResult[]
     */
    public function audit(array $lpUrls, array $ampUrls = []): array
    {
        $activeRules = SeoRule::active()->get();

        // Unique fetch pool
        $allUrlsToFetch = array_unique(array_filter(array_merge($lpUrls, $ampUrls)));

        // Fetch concurrently
        $fetchResults = $this->fetcher->fetchConcurrent($allUrlsToFetch);

        $results = [];

        foreach ($lpUrls as $index => $lpUrl) {
            $ampUrl = $ampUrls[$index] ?? null;
            $checks = [];
            $errorMessage = null;

            // Redirect chain (if any)
            $redirectChain = [];
            if (isset($fetchResults[$lpUrl]['redirects'])) {
                $redirectChain = $fetchResults[$lpUrl]['redirects'];
            }

            // Check if LP fetch failed
            if (isset($fetchResults[$lpUrl]['error'])) {
                $errorMessage = 'LP Error: '.$fetchResults[$lpUrl]['error'];
                $results[] = new UrlAuditResult($lpUrl, $ampUrl, $errorMessage, $checks, $redirectChain);
                continue;
            }

            $lpHtml = $fetchResults[$lpUrl]['html'] ?? '';
            $lpCrawler = new Crawler($lpHtml);

            $ampCrawler = null;
            if ($ampUrl) {
                if (isset($fetchResults[$ampUrl]['error'])) {
                    $errorMessage = 'AMP Error: '.$fetchResults[$ampUrl]['error'];
                    $results[] = new UrlAuditResult($lpUrl, $ampUrl, $errorMessage, $checks, $redirectChain);
                    continue;
                }
                $ampHtml = $fetchResults[$ampUrl]['html'] ?? '';
                $ampCrawler = new Crawler($ampHtml);
            }

            // Run evaluations
            foreach ($activeRules as $rule) {
                $checks[] = $this->engine->evaluate($lpCrawler, $rule, $ampCrawler);
            }

            $results[] = new UrlAuditResult($lpUrl, $ampUrl, null, $checks, $redirectChain);
        }

        return $results;
    }
}

