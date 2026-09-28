<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class AnchorHrefAllowlistRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? 'a[href]';
        $allowedTypes = $config['allowed_target_types'] ?? ['canonical', 'amphtml'];

        if (!is_array($allowedTypes)) {
            $allowedTypes = [$allowedTypes];
        }

        $nodes = $dom->filter($selector);

        if ($nodes->count() === 0) {
            return [
                'passed' => true,
                'issue' => null,
                'reason' => 'Tidak ada elemen yang cocok dengan selector.',
                'expected' => 'URL sesuai allowlist',
                'actual' => 'Elemen tidak ditemukan',
                'selector' => $selector,
                'attribute' => 'href',
                'html_snippet' => null,
            ];
        }

        $allowedUrls = [];
        $allowedUrlDisplays = [];

        foreach ($allowedTypes as $type) {
            if ($type === 'canonical' || $type === 'lp') {
                if ($context && $context->finalUrl) {
                    $allowedUrls[] = rtrim($this->normalizeUrl($context->finalUrl), '/');
                    $allowedUrlDisplays[] = 'Canonical URL';
                }
            } elseif ($type === 'amphtml' || $type === 'amp') {
                if ($context && $context->ampUrl) {
                    $allowedUrls[] = rtrim($this->normalizeUrl($context->ampUrl), '/');
                    $allowedUrlDisplays[] = 'AMP URL';
                }
            } else {
                // If it's a specific string/URL
                $allowedUrls[] = rtrim($this->normalizeUrl($type), '/');
                $allowedUrlDisplays[] = $type;
            }
        }

        $invalidNodes = [];
        $invalidUrls = [];

        foreach ($nodes as $index => $node) {
            $crawlerNode = new Crawler($node);
            $href = $crawlerNode->attr('href');

            if (empty($href)) {
                continue; // skip empty hrefs if any
            }

            // Handle anchor links or javascript:
            if (str_starts_with($href, '#') || str_starts_with(strtolower($href), 'javascript:')) {
                continue;
            }

            $resolvedHref = $this->resolveUrl($href, $context ? $context->finalUrl : '');
            $normalizedHref = rtrim($this->normalizeUrl($resolvedHref), '/');

            $isAllowed = false;
            foreach ($allowedUrls as $allowed) {
                // We check if it matches EXACTLY, or if it starts with the allowed URL?
                // The prompt says "wajibkan hanya boleh ada url yang di tentukan" - meaning exact match.
                if ($normalizedHref === $allowed) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                if (!in_array($href, $invalidUrls)) {
                    $invalidUrls[] = $href;
                }
                if (count($invalidNodes) < 5) {
                    $invalidNodes[] = substr($crawlerNode->outerHtml(), 0, 150);
                }
            }
        }

        $passed = count($invalidUrls) === 0;

        $issue = null;
        $reason = null;

        if (!$passed) {
            $issue = $rule->issue_message ?? "Ditemukan URL yang tidak diizinkan pada elemen {$selector}.";
            $reason = $rule->reason_template ?? 'Semua link pada elemen ini hanya boleh mengarah ke URL yang diizinkan (allowlist).';
        }

        $expectedText = implode(' atau ', $allowedUrlDisplays);

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "Hanya boleh: " . $expectedText,
            'actual' => $passed ? 'Semua URL sesuai ketentuan' : implode(', ', $invalidUrls),
            'selector' => $selector,
            'attribute' => 'href',
            'html_snippet' => !$passed && !empty($invalidNodes) ? implode("\n", $invalidNodes) : null,
        ];
    }

    private function normalizeUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        if (!$parts) return $url;

        $scheme = strtolower($parts['scheme'] ?? 'http');
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '/';
        
        $path = preg_replace('#/{2,}#', '/', $path);
        
        $query = '';
        if (isset($parts['query']) && $parts['query'] !== '') {
            parse_str($parts['query'], $queryArr);
            ksort($queryArr);
            $query = '?' . http_build_query($queryArr);
        }

        return "{$scheme}://{$host}{$path}{$query}";
    }

    private function resolveUrl(string $rel, string $base): string
    {
        if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
        if (str_starts_with($rel, '//')) {
            $baseScheme = parse_url($base, PHP_URL_SCHEME) ?: 'http';
            return "{$baseScheme}:{$rel}";
        }
        $baseParts = parse_url($base);
        $scheme = $baseParts['scheme'] ?? 'http';
        $host = $baseParts['host'] ?? '';
        if (str_starts_with($rel, '/')) {
            return "{$scheme}://{$host}{$rel}";
        }
        $basePath = $baseParts['path'] ?? '/';
        $dir = preg_replace('#/[^/]*$#', '', $basePath);
        return "{$scheme}://{$host}{$dir}/{$rel}";
    }
}
