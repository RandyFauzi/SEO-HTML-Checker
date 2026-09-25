<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;

class HtmlFetcher
{
    const MAX_SIZE = 5242880; // 5 MB

    const TIMEOUT = 10;

    const MAX_REDIRECTS = 5;

    protected ?Client $client = null;

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function fetchConcurrent(array $urls): array
    {
        $client = $this->client ?? new Client([
            'timeout' => self::TIMEOUT,
            'allow_redirects' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            ],
        ]);

        $promises = [];
        foreach ($urls as $url) {
            $promises[$url] = $this->fetchAsync($url, $client, 0, []);
        }

        $results = Utils::settle($promises)->wait();

        $output = [];
        foreach ($results as $url => $result) {
            if ($result['state'] === 'fulfilled') {
                $output[$url] = $result['value'];
            } else {
                $output[$url] = [
                    'error' => $this->formatErrorMessage($result['reason']),
                    'redirects' => [],
                ];
            }
        }

        return $output;
    }

    private function fetchAsync(string $currentUrl, Client $client, int $hops, array $redirects): PromiseInterface
    {
        if ($hops > self::MAX_REDIRECTS) {
            return Create::promiseFor([
                'error' => 'Too many redirects',
                'redirects' => $redirects,
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
                    CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"],
                ],
                'progress' => function ($downloadTotal, $downloadedBytes) {
                    if ($downloadedBytes > self::MAX_SIZE) {
                        throw new Exception('Response size exceeds 5MB limit');
                    }
                },
            ]);

            return $promise->then(
                function ($response) use ($currentUrl, $client, $hops, $redirects, $parsed, $host) {
                    $statusCode = $response->getStatusCode();

                    // Handle Redirect
                    if ($statusCode >= 300 && $statusCode < 400) {
                        $redirects[] = $currentUrl;
                        $location = $response->getHeaderLine('Location');
                        if (! $location) {
                            return [
                                'error' => "Redirect status {$statusCode} without Location header",
                                'redirects' => $redirects,
                            ];
                        }

                        // resolve relative URL
                        if (! preg_match('~^https?://~i', $location)) {
                            $base = rtrim(preg_replace('~/[^/]*$~', '', $currentUrl), '/');
                            $location = str_starts_with($location, '/')
                                ? "{$parsed['scheme']}://{$host}{$location}"
                                : "{$base}/{$location}";
                        }

                        return $this->fetchAsync($location, $client, $hops + 1, $redirects);
                    }

                    if ($statusCode !== 200) {
                        return [
                            'error' => "HTTP Error {$statusCode}: Halaman tidak mengembalikan status 200 OK.",
                            'redirects' => $redirects,
                        ];
                    }

                    $contentType = strtolower($response->getHeaderLine('Content-Type'));
                    $mimeType = trim(explode(';', $contentType)[0]);

                    if ($contentType && ! in_array($mimeType, ['text/html', 'application/xhtml+xml'])) {
                        return [
                            'error' => "Invalid Content-Type. Expected HTML, got: {$contentType}",
                            'redirects' => $redirects,
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
                        'redirects' => $redirects,
                        'final_url' => $currentUrl,
                    ];
                },
                function ($e) use ($redirects) {
                    return [
                        'error' => $this->formatErrorMessage($e),
                        'redirects' => $redirects,
                    ];
                }
            );
        } catch (Exception $e) {
            return Create::promiseFor([
                'error' => $this->formatErrorMessage($e),
                'redirects' => $redirects,
            ]);
        }
    }

    private function formatErrorMessage($e): string
    {
        if ($e instanceof \GuzzleHttp\Exception\ConnectException) {
            return 'HTTP Error: Koneksi ke server tujuan timeout atau gagal (Connect Exception).';
        }

        if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
            $code = $e->getResponse()->getStatusCode();
            if ($code == 403) {
                return 'HTTP Error 403: Akses ditolak oleh server target (terblokir anti-bot atau firewall).';
            } elseif ($code == 404) {
                return 'HTTP Error 404: Halaman tidak ditemukan.';
            } elseif ($code == 401) {
                return 'HTTP Error 401: Membutuhkan autentikasi (Unauthorized).';
            } elseif ($code == 429) {
                return 'HTTP Error 429: Terlalu banyak request (Too Many Requests).';
            } elseif ($code >= 500) {
                return "HTTP Error {$code}: Server target mengalami masalah internal.";
            } else {
                return "HTTP Error {$code}: Permintaan gagal.";
            }
        }

        if ($e instanceof Exception) {
            $msg = $e->getMessage();
            // Truncate at the first newline if it's too long
            $msg = strtok($msg, "\n");
            // Also trim long string
            if (strlen($msg) > 150) {
                $msg = substr($msg, 0, 147) . '...';
            }
            return 'Gagal memuat URL: ' . $msg;
        }

        return 'Gagal memuat URL: Error tidak diketahui.';
    }
}
