<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SafeHttpClient
{
    const MAX_SIZE = 5242880; // 5 MB

    const MAX_REDIRECTS = 5;

    const TIMEOUT = 10;

    /**
     * Safely fetch URL content preventing SSRF, large responses, and redirect loops.
     */
    public static function get(string $url): Response|string
    {
        $currentUrl = $url;
        $redirects = 0;

        while ($redirects <= self::MAX_REDIRECTS) {
            // 1. SSRF Protection: Validate URL
            UrlSecurityValidator::validate($currentUrl);

            // 2. Fetch using Http facade with strict options
            $response = Http::withOptions([
                'allow_redirects' => false,
                'timeout' => self::TIMEOUT,
                'stream' => true,
            ])->get($currentUrl);

            // 3. Handle Redirects
            if ($response->isRedirect()) {
                $location = $response->header('Location');
                if (! $location) {
                    throw new Exception('Redirected but no Location header found.');
                }

                // Convert relative redirect to absolute
                $currentUrl = self::resolveUrl($currentUrl, $location);
                $redirects++;

                if ($redirects > self::MAX_REDIRECTS) {
                    throw new Exception('Too many redirects (max '.self::MAX_REDIRECTS.').');
                }

                continue;
            }

            // 4. Validate Status and Response Size
            if (! $response->successful()) {
                throw new Exception('HTTP Error: '.$response->status());
            }

            $contentLength = $response->header('Content-Length');
            if ($contentLength && (int) $contentLength > self::MAX_SIZE) {
                throw new Exception('Response exceeds maximum size limit of 5MB.');
            }

            // Read the stream chunk by chunk to prevent loading huge bodies if Content-Length is missing
            $stream = $response->toPsrResponse()->getBody();
            $content = '';

            while (! $stream->eof()) {
                $content .= $stream->read(8192); // Read 8KB chunks
                if (strlen($content) > self::MAX_SIZE) {
                    throw new Exception('Response body exceeds maximum size limit of 5MB.');
                }
            }

            return $content; // Return HTML body string directly
        }

        throw new Exception('Unexpected end of SafeHttpClient fetch loop.');
    }

    /**
     * Helper to resolve relative URLs to absolute.
     */
    private static function resolveUrl(string $base, string $rel): string
    {
        if (parse_url($rel, PHP_URL_SCHEME) != '') {
            return $rel;
        }

        if ($rel[0] == '#' || $rel[0] == '?') {
            return $base.$rel;
        }

        extract(parse_url($base));
        $path = preg_replace('#/[^/]*$#', '', $path ?? '');

        if ($rel[0] == '/') {
            $path = '';
        }

        $abs = "$host$path/$rel";
        $re = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];

        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        return $scheme.'://'.$abs;
    }
}
