<?php

namespace Tests\Feature;

use App\Services\HtmlFetcher;
use Tests\TestCase;

class HtmlFetcherTest extends TestCase
{
    public function test_ssrf_protection_blocks_internal_ips()
    {
        $fetcher = new HtmlFetcher();
        $urls = [
            'http://127.0.0.1',
            'http://localhost',
            'http://169.254.169.254', // AWS metadata
        ];

        $results = $fetcher->fetchConcurrent($urls);

        foreach ($results as $url => $result) {
            $this->assertArrayHasKey('error', $result);
            $errorMsg = strtolower($result['error']);
            $isBlocked = str_contains($errorMsg, 'not allowed') 
                || str_contains($errorMsg, 'resolve')
                || str_contains($errorMsg, 'private');
            $this->assertTrue($isBlocked, "Expected $url to be blocked, got error: {$errorMsg}");
        }
    }

    public function test_concurrent_fetching_works()
    {
        $fetcher = new HtmlFetcher();
        $urls = [
            'https://example.com',
            'https://example.org'
        ];

        $results = $fetcher->fetchConcurrent($urls);
        
        $this->assertArrayHasKey('https://example.com', $results);
        $this->assertArrayHasKey('https://example.org', $results);
        
        // Assert at least one has html
        $hasHtml = isset($results['https://example.com']['html']) || isset($results['https://example.org']['html']);
        $this->assertTrue($hasHtml);
    }
}
