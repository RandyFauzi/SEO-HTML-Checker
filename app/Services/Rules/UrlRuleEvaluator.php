<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class UrlRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $condition = $config['condition'] ?? 'protocol';
        $target = $config['target'] ?? 'page_url'; // page_url, canonical_match

        if (! $context) {
            return [
                'passed' => false,
                'issue' => 'Context tidak tersedia',
                'reason' => 'Evaluasi URL memerlukan AuditContext yang valid.',
                'expected' => null,
                'actual' => null,
                'selector' => null,
                'attribute' => null,
                'html_snippet' => null,
            ];
        }

        if ($target === 'canonical_match' || $condition === 'canonical_match') {
            return $this->evaluateCanonicalMatch($dom, $rule, $context, $config);
        }

        return $this->evaluatePageUrl($rule, $context, $config);
    }

    private function evaluateCanonicalMatch(Crawler $dom, SeoRule $rule, AuditContext $context, array $config): array
    {
        $canonicalNodes = $dom->filter('link[rel="canonical"]');

        if ($canonicalNodes->count() === 0) {
            return [
                'passed' => false,
                'issue' => $rule->issue_message ?? 'Tag canonical tidak ditemukan.',
                'reason' => $rule->reason_template ?? 'Halaman tidak memiliki tag rel="canonical" untuk dibandingkan dengan URL halaman.',
                'expected' => $context->finalUrl,
                'actual' => 'Canonical tidak ada',
                'selector' => 'link[rel="canonical"]',
                'attribute' => 'href',
                'html_snippet' => null,
            ];
        }

        $canonicalHref = trim($canonicalNodes->first()->attr('href') ?? '');
        $htmlSnippet = substr($canonicalNodes->first()->outerHtml(), 0, 500);

        if (empty($canonicalHref)) {
            return [
                'passed' => false,
                'issue' => $rule->issue_message ?? 'Href canonical kosong.',
                'reason' => $rule->reason_template ?? 'Tag canonical ditemukan tetapi atribut href kosong.',
                'expected' => $context->finalUrl,
                'actual' => 'href kosong',
                'selector' => 'link[rel="canonical"]',
                'attribute' => 'href',
                'html_snippet' => $htmlSnippet,
            ];
        }

        $sourceType = $config['url_source'] ?? 'final_url';
        $pageUrl = $sourceType === 'original_url' ? $context->originalUrl : $context->finalUrl;

        // Resolve relative canonical if necessary
        $resolvedCanonical = $this->resolveUrl($canonicalHref, $pageUrl);

        $normalizedCanonical = $this->normalizeUrl($resolvedCanonical);
        $normalizedPage = $this->normalizeUrl($pageUrl);

        $passed = ($normalizedCanonical === $normalizedPage);

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? 'Canonical URL berbeda dari URL halaman.';
            $reason = $rule->reason_template ?? "Tag canonical menunjuk ke URL lain: {$canonicalHref}";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $pageUrl,
            'actual' => $canonicalHref,
            'selector' => 'link[rel="canonical"]',
            'attribute' => 'href',
            'html_snippet' => $htmlSnippet,
        ];
    }

    private function evaluatePageUrl(SeoRule $rule, AuditContext $context, array $config): array
    {
        $sourceType = $config['url_source'] ?? 'final_url';
        $url = $sourceType === 'original_url' ? $context->originalUrl : $context->finalUrl;

        $condition = $config['condition'] ?? 'protocol';
        $operator = strtolower($config['operator'] ?? 'equals');
        $expected = $config['expected'] ?? $config['expected_value'] ?? '';

        $parsed = parse_url($url);
        $passed = true;
        $actual = $url;
        $expectedDisplay = $expected;

        switch ($condition) {
            case 'protocol':
                $scheme = strtolower($parsed['scheme'] ?? 'http');
                $expectedScheme = strtolower($expected ?: 'https');
                $passed = match ($operator) {
                    'not_equals', '!=' => $scheme !== $expectedScheme,
                    default => $scheme === $expectedScheme,
                };
                $actual = $scheme;
                $expectedDisplay = $expectedScheme;
                break;

            case 'contains':
                $passed = str_contains(strtolower($url), strtolower($expected));
                $expectedDisplay = "Mengandung '{$expected}'";
                break;

            case 'not_contains':
                $passed = ! str_contains(strtolower($url), strtolower($expected));
                $expectedDisplay = "Tidak mengandung '{$expected}'";
                break;

            case 'starts_with':
                $passed = str_starts_with(strtolower($url), strtolower($expected));
                $expectedDisplay = "Diawali '{$expected}'";
                break;

            case 'ends_with':
                $passed = str_ends_with(strtolower($url), strtolower($expected));
                $expectedDisplay = "Diakhiri '{$expected}'";
                break;

            case 'regex':
                $regex = $config['regex_pattern'] ?? $expected;
                if (empty($regex)) {
                    $passed = false;
                    $actual = 'Regex kosong';
                    break;
                }
                if (! str_starts_with($regex, '/') && ! str_starts_with($regex, '#') && ! str_starts_with($regex, '@')) {
                    $regex = '@'.str_replace('@', '\@', $regex).'@i';
                }
                $origLimit = ini_get('pcre.backtrack_limit');
                ini_set('pcre.backtrack_limit', '10000');
                try {
                    $match = @preg_match($regex, $url);
                    $passed = ($match === 1);
                } finally {
                    ini_set('pcre.backtrack_limit', $origLimit);
                }
                $expectedDisplay = "Cocok regex {$regex}";
                break;

            case 'equals':
            default:
                $normalizedUrl = $this->normalizeUrl($url);
                $normalizedExpected = $this->normalizeUrl($expected);
                $passed = ($normalizedUrl === $normalizedExpected);
                $expectedDisplay = $expected;
                break;
        }

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? "Pemeriksaan URL {$rule->name} tidak terpenuhi.";
            $reason = $rule->reason_template ?? "URL `{$url}` tidak memenuhi kondisi {$condition} ({$expectedDisplay}).";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => (string) $expectedDisplay,
            'actual' => (string) $actual,
            'selector' => null,
            'attribute' => null,
            'html_snippet' => null,
        ];
    }

    public function normalizeUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        if (! $parts) {
            return $url;
        }

        $scheme = strtolower($parts['scheme'] ?? 'http');
        $host = strtolower($parts['host'] ?? '');
        $port = isset($parts['port']) ? (int) $parts['port'] : null;

        // Strip default ports
        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            $port = null;
        }

        $path = $parts['path'] ?? '/';
        // Normalize multiple slashes and trim trailing slash except root
        $path = preg_replace('#/{2,}#', '/', $path);
        if (strlen($path) > 1 && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $query = '';
        if (isset($parts['query']) && $parts['query'] !== '') {
            parse_str($parts['query'], $queryArr);
            ksort($queryArr);
            $query = '?'.http_build_query($queryArr);
        }

        $portStr = $port ? ":{$port}" : '';

        // Ignore fragment completely (#...)
        return "{$scheme}://{$host}{$portStr}{$path}{$query}";
    }

    private function resolveUrl(string $rel, string $base): string
    {
        if (parse_url($rel, PHP_URL_SCHEME) != '') {
            return $rel;
        }

        if (str_starts_with($rel, '//')) {
            $baseScheme = parse_url($base, PHP_URL_SCHEME) ?: 'http';

            return "{$baseScheme}:{$rel}";
        }

        $baseParts = parse_url($base);
        $scheme = $baseParts['scheme'] ?? 'http';
        $host = $baseParts['host'] ?? '';
        $port = isset($baseParts['port']) ? ":{$baseParts['port']}" : '';

        if (str_starts_with($rel, '/')) {
            return "{$scheme}://{$host}{$port}{$rel}";
        }

        $basePath = $baseParts['path'] ?? '/';
        $dir = preg_replace('#/[^/]*$#', '', $basePath);

        return "{$scheme}://{$host}{$port}{$dir}/{$rel}";
    }
}
