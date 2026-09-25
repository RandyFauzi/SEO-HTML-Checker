<?php

namespace Tests\Feature;

use App\Services\HtmlFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class HtmlFetcherTest extends TestCase
{
    public function test_ssrf_protection_blocks_internal_ips()
    {
        $fetcher = new HtmlFetcher;
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
        $fetcher = new HtmlFetcher;
        $urls = [
            'https://example.com',
            'https://example.org',
        ];

        $results = $fetcher->fetchConcurrent($urls);

        $this->assertArrayHasKey('https://example.com', $results);
        $this->assertArrayHasKey('https://example.org', $results);

        // Assert at least one has html
        $hasHtml = isset($results['https://example.com']['html']) || isset($results['https://example.org']['html']);
        $this->assertTrue($hasHtml);
    }

    public function test_content_type_validation()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/html'], '<html></html>'),
            new Response(200, ['Content-Type' => 'text/html; charset=UTF-8'], '<html></html>'),
            new Response(200, ['Content-Type' => 'application/xhtml+xml'], '<html></html>'),
            new Response(200, ['Content-Type' => 'application/xhtml+xml; charset=UTF-8'], '<html></html>'),
            new Response(200, ['Content-Type' => 'application/pdf'], '%PDF-1.4'),
            new Response(200, ['Content-Type' => 'image/jpeg'], 'binary'),
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $fetcher = new HtmlFetcher;
        $fetcher->setClient($client);

        // We use example.com because UrlSecurityValidator needs a resolvable, safe host.
        $urls = [
            'https://example.com/1',
            'https://example.com/2',
            'https://example.com/3',
            'https://example.com/4',
            'https://example.com/5',
            'https://example.com/6',
            'https://example.com/7',
        ];

        // Ensure we fetch sequentially or handle promises carefully?
        // fetchConcurrent fires them all. With MockHandler, they get dequeued in order.
        // Wait, fetchConcurrent iterates over $urls and fires requests.
        // The first URL gets the first mock, the second URL gets the second mock, etc.
        $results = $fetcher->fetchConcurrent($urls);

        // 1. text/html -> PASS
        $this->assertArrayHasKey('html', $results['https://example.com/1']);

        // 2. text/html; charset=UTF-8 -> PASS
        $this->assertArrayHasKey('html', $results['https://example.com/2']);

        // 3. application/xhtml+xml -> PASS
        $this->assertArrayHasKey('html', $results['https://example.com/3']);

        // 4. application/xhtml+xml; charset=UTF-8 -> PASS
        $this->assertArrayHasKey('html', $results['https://example.com/4']);

        // 5. application/pdf -> REJECT
        $this->assertArrayHasKey('error', $results['https://example.com/5']);
        $this->assertStringContainsString('Invalid Content-Type', $results['https://example.com/5']['error']);

        // 6. image/jpeg -> REJECT
        $this->assertArrayHasKey('error', $results['https://example.com/6']);

        // 7. application/json -> REJECT
        $this->assertArrayHasKey('error', $results['https://example.com/7']);
    }
}
