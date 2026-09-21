<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class HtmlFetcher
{
    const MAX_SIZE = 5242880; // 5 MB

    const TIMEOUT = 10;

    /**
     * Fetch multiple URLs concurrently.
     * Returns an array of html bodies or exception messages, indexed by url.
     */
    public function fetchConcurrent(array $urls): array
    {
        // 1. SSRF Validation before fetching
        $validatedUrls = [];
        $results = [];

        foreach ($urls as $url) {
            try {
                UrlSecurityValidator::validate($url);
                $validatedUrls[] = $url;
            } catch (Exception $e) {
                $results[$url] = ['error' => $e->getMessage()];
            }
        }

        if (empty($validatedUrls)) {
            return $results;
        }

        // 2. Fetch via Http::pool with Concurrency limit
        $concurrencyLimit = 5;
        $responses = [];

        foreach (array_chunk($validatedUrls, $concurrencyLimit) as $chunk) {
            $chunkResponses = Http::pool(function (Pool $pool) use ($chunk) {
                $requests = [];
                foreach ($chunk as $url) {
                    $requests[] = $pool->as($url)->withOptions([
                        'allow_redirects' => false,
                        'timeout' => self::TIMEOUT,
                        'progress' => function ($downloadTotal, $downloadedBytes) {
                            if ($downloadedBytes > self::MAX_SIZE) {
                                throw new Exception('Response size exceeds 5MB limit');
                            }
                        },
                    ])->get($url);
                }

                return $requests;
            });
            $responses = array_merge($responses, $chunkResponses);
        }

        // 3. Process Responses
        foreach ($responses as $url => $response) {
            if ($response instanceof Exception) {
                $results[$url] = ['error' => $response->getMessage()];

                continue;
            }

            if (! $response->successful()) {
                // If it's a redirect, we could handle it here, but for concurrent pool
                // handling manual redirects is complex. We will flag it as error for MVP
                // or just say redirect not followed if we don't handle it in pool.
                if ($response->isRedirect()) {
                    $results[$url] = ['error' => 'Redirects are not followed in concurrent mode for security reasons.'];
                } else {
                    $results[$url] = ['error' => 'HTTP Error: '.$response->status()];
                }

                continue;
            }

            $results[$url] = ['html' => $response->body()];
        }

        return $results;
    }
}
