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
        $client = new Client([
            'timeout' => self::TIMEOUT,
            'allow_redirects' => false,
        ]);

        $promises = [];
        foreach ($urls as $url) {
            $promises[$url] = $this->fetchAsync($url, $client, 0, []);
        }

        $results = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

        $output = [];
        foreach ($results as $url => $result) {
            if ($result['state'] === 'fulfilled') {
                $output[$url] = $result['value'];
            } else {
                $output[$url] = [
                    'error' => $result['reason']->getMessage(),
                    'redirects' => []
                ];
            }
        }

        return $output;
    }

    private function fetchAsync(string $currentUrl, Client $client, int $hops, array $redirects): \GuzzleHttp\Promise\PromiseInterface
    {
        if ($hops > self::MAX_REDIRECTS) {
            return \GuzzleHttp\Promise\Create::promiseFor([
                'error' => 'Too many redirects',
                'redirects' => $redirects
            ]);
        }

        try {
            // SSRF Validation & IP Pinning
            $ip = UrlSecurityValidator::validate($currentUrl);
            
            $parsed = parse_url($currentUrl);
            $host = $parsed['host'];
            $port = $parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80);

            $promise = $client->requestAsync('GET', $currentUrl, [
                'curl' => [
                    CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"]
                ],
                'progress' => function ($downloadTotal, $downloadedBytes) {
                    if ($downloadedBytes > self::MAX_SIZE) {
                        throw new Exception('Response size exceeds 5MB limit');
                    }
                }
            ]);

            return $promise->then(
                function ($response) use ($currentUrl, $client, $hops, $redirects, $parsed, $host) {
                    $statusCode = $response->getStatusCode();

                    // Handle Redirect
                    if ($statusCode >= 300 && $statusCode < 400) {
                        $redirects[] = $currentUrl;
                        $location = $response->getHeaderLine('Location');
                        if (!$location) {
                            return [
                                'error' => "Redirect status {$statusCode} without Location header",
                                'redirects' => $redirects
                            ];
                        }
                        
                        // resolve relative URL
                        if (!preg_match('~^https?://~i', $location)) {
                            $base = rtrim(preg_replace('~/[^/]*$~', '', $currentUrl), '/');
                            $location = str_starts_with($location, '/') 
                                ? "{$parsed['scheme']}://{$host}{$location}"
                                : "{$base}/{$location}";
                        }
                        
                        return $this->fetchAsync($location, $client, $hops + 1, $redirects);
                    }

                    if ($statusCode !== 200) {
                        return [
                            'error' => "HTTP Error: {$statusCode}",
                            'redirects' => $redirects
                        ];
                    }

                    $contentType = strtolower($response->getHeaderLine('Content-Type'));
                    if ($contentType && !str_contains($contentType, 'text/html')) {
                        return [
                            'error' => "Invalid Content-Type. Expected text/html, got: {$contentType}",
                            'redirects' => $redirects
                        ];
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
                },
                function ($e) use ($redirects) {
                    return [
                        'error' => $e->getMessage(),
                        'redirects' => $redirects
                    ];
                }
            );
        } catch (Exception $e) {
            return \GuzzleHttp\Promise\Create::promiseFor([
                'error' => $e->getMessage(),
                'redirects' => $redirects
            ]);
        }
    }
}

