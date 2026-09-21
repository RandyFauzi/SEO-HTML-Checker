<?php

namespace App\Services;

use Exception;

class UrlSecurityValidator
{
    /**
     * Validate the given URL to prevent SSRF and other attacks.
     */
    public static function validate(string $url): string
    {
        if (empty($url)) {
            throw new Exception('URL is empty.');
        }

        // 1. Validate URL format
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL format.');
        }

        // 2. Validate Scheme
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array(strtolower($scheme), ['http', 'https'])) {
            throw new Exception('Only HTTP and HTTPS schemes are allowed.');
        }

        // 3. Validate Port
        $port = parse_url($url, PHP_URL_PORT);
        if ($port !== null && ! in_array($port, [80, 443, 8080])) {
            throw new Exception("Port $port is not allowed.");
        }

        // 4. Validate Hostname and IP
        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            throw new Exception('Could not parse hostname.');
        }

        // Check if the host itself is an IP address
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            self::checkIpAllowed($host);
        } else {
            // 5. Resolve DNS and protect against SSRF via DNS rebinding / local domains
            $records = dns_get_record($host, DNS_A + DNS_AAAA);
            if ($records === false || count($records) === 0) {
                throw new Exception("Could not resolve hostname: $host");
            }

            foreach ($records as $record) {
                if (isset($record['ip'])) {
                    self::checkIpAllowed($record['ip']);
                }
                if (isset($record['ipv6'])) {
                    self::checkIpAllowed($record['ipv6']);
                }
            }
        }

        return $url;
    }

    /**
     * Check if an IP address is allowed (not private, loopback, etc.)
     */
    private static function checkIpAllowed(string $ip): void
    {
        // filter_var with FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        // automatically blocks 10.x, 172.16.x, 192.168.x, 127.x, 169.254.x, etc.
        $isValid = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if ($isValid === false) {
            throw new Exception("Resolved IP ($ip) is a private, loopback, or reserved address, which is not allowed.");
        }

        // Extra block for 0.0.0.0
        if ($ip === '0.0.0.0' || $ip === '::') {
            throw new Exception("Invalid IP address ($ip).");
        }
    }
}
