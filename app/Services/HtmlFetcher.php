<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;

class HtmlFetcher
{
    const MAX_SIZE = 5242880; // 5 MB
    const TIMEOUT = 10;
    const MAX_REDIRECTS = 5;

    public function fetchConcurrent(array $urls): array
    {
        $results = [];

        foreach ($urls as $url) {
            $results[$url] = $this->fetchSingle($url);
        }

        return $results;
    }

    private function fetchSingle(string $url): array
    {
        $redirects = [];
        $currentUrl = $url;
        $hops = 0;

        $client = new Client([
            'timeout' => self::TIMEOUT,
            'allow_redirects' => false,
        ]);

        while ($hops <= self::MAX_REDIRECTS) {
            try {
                // SSRF Validation & IP Pinning
                $ip = UrlSecurityValidator::validate($currentUrl);
                
                $parsed = parse_url($currentUrl);
                $host = $parsed['host'];
                $port = $parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80);

                $response = $client->request('GET', $currentUrl, [
                    'curl' => [
                        CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"]
                    ],
                    'progress' => function ($downloadTotal, $downloadedBytes) {
                        if ($downloadedBytes > self::MAX_SIZE) {
                            throw new Exception('Response size exceeds 5MB limit');
                        }
                    }
                ]);

                $statusCode = $response->getStatusCode();

                // Handle Redirect
                if ($statusCode >= 300 && $statusCode < 400) {
                    $redirects[] = $currentUrl;
                    $location = $response->getHeaderLine('Location');
                    if (!$location) {
                        throw new Exception("Redirect status {$statusCode} without Location header");
                    }
                    
                    // resolve relative URL
                    if (!preg_match('~^https?://~i', $location)) {
                        $base = rtrim(preg_replace('~/[^/]*$~', '', $currentUrl), '/');
                        $location = str_starts_with($location, '/') 
                            ? "{$parsed['scheme']}://{$host}{$location}"
                            : "{$base}/{$location}";
                    }
                    
                    $currentUrl = $location;
                    $hops++;
                    continue;
                }

                if ($statusCode !== 200) {
                    throw new Exception("HTTP Error: {$statusCode}");
                }

                $contentType = strtolower($response->getHeaderLine('Content-Type'));
                if ($contentType && !str_contains($contentType, 'text/html')) {
                    throw new Exception("Invalid Content-Type. Expected text/html, got: {$contentType}");
                }

                $html = (string) $response->getBody();

                // Detect and convert charset to UTF-8
                $charset = 'UTF-8';
                if (preg_match('/charset=([\w\-]+)/i', $contentType, $matches)) {
                    $charset = strtoupper($matches[1]);
                }

                if ($charset !== 'UTF-8') {
                    $html = @mb_convert_encoding($html, 'UTF-8', $charset);
                } else {
                    // Fallback to meta charset
                    if (preg_match('/<meta[^>]+charset=[\'"]?([\w\-]+)[\'"]?/i', $html, $matches)) {
                        $metaCharset = strtoupper($matches[1]);
                        if ($metaCharset !== 'UTF-8' && $metaCharset !== 'UTF8') {
                            $html = @mb_convert_encoding($html, 'UTF-8', $metaCharset);
                        }
                    }
                }

                return [
                    'html' => $html,
                    'redirects' => $redirects
                ];

            } catch (Exception $e) {
                return [
                    'error' => $e->getMessage(),
                    'redirects' => $redirects
                ];
            }
        }

        return [
            'error' => 'Too many redirects',
            'redirects' => $redirects
        ];
    }
}

