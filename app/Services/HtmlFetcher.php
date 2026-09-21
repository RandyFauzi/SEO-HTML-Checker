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
            'stream' => true,
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
                    ]
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

                $body = $response->getBody();
                $html = '';
                $downloaded = 0;

                while (!$body->eof()) {
                    $chunk = $body->read(8192);
                    $html .= $chunk;
                    $downloaded += strlen($chunk);

                    if ($downloaded > self::MAX_SIZE) {
                        throw new Exception('Response size exceeds 5MB limit');
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

