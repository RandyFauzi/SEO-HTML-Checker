<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class LinkRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $condition = $config['condition'] ?? 'href_exists';
        $selector = $config['selector'] ?? 'a';

        return match ($condition) {
            'href_exists' => $this->evaluateHrefExists($dom, $rule, $selector),
            'target_blank_rel' => $this->evaluateTargetBlankRel($dom, $rule, $config),
            'classification', 'internal_external' => $this->evaluateClassification($dom, $rule, $context, $config),
            default => [
                'passed' => false,
                'issue' => 'Kondisi link tidak dikenali.',
                'reason' => "Tipe kondisi link `{$condition}` belum didukung.",
                'expected' => null,
                'actual' => null,
                'selector' => $selector,
                'attribute' => 'href',
                'html_snippet' => null,
            ],
        };
    }

    private function evaluateHrefExists(Crawler $dom, SeoRule $rule, string $selector): array
    {
        $links = $dom->filter($selector);
        $missingHrefNodes = [];
        $firstSnippet = null;

        foreach ($links as $index => $node) {
            $crawlerNode = new Crawler($node);
            if ($index === 0) {
                $firstSnippet = substr($crawlerNode->outerHtml(), 0, 150);
            }

            if ($crawlerNode->attr('href') === null) {
                if (count($missingHrefNodes) < 3) {
                    $missingHrefNodes[] = substr($crawlerNode->outerHtml(), 0, 150);
                }
            }
        }

        $passed = empty($missingHrefNodes);
        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? 'Terdapat tautan <a> tanpa atribut href.';
            $reason = $rule->reason_template ?? 'Setiap elemen <a> untuk navigasi wajib memiliki atribut href.';
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => 'Semua tag <a> memiliki atribut href',
            'actual' => $passed ? 'Semua tag <a> memiliki href' : count($missingHrefNodes).' tag <a> tanpa atribut href',
            'selector' => $selector,
            'attribute' => 'href',
            'html_snippet' => ! $passed ? implode("\n", $missingHrefNodes) : $firstSnippet,
        ];
    }

    private function evaluateTargetBlankRel(Crawler $dom, SeoRule $rule, array $config): array
    {
        $selector = 'a[target="_blank"]';
        $targetBlankLinks = $dom->filter($selector);

        // If no target="_blank" links exist on the page, this rule gracefully passes!
        if ($targetBlankLinks->count() === 0) {
            return [
                'passed' => true,
                'issue' => null,
                'reason' => null,
                'expected' => 'Link target="_blank" aman',
                'actual' => 'Tidak ada link target="_blank"',
                'selector' => $selector,
                'attribute' => 'rel',
                'html_snippet' => null,
            ];
        }

        $requiredRel = strtolower($config['expected'] ?? $config['required_rel'] ?? 'noopener');
        $failedNodes = [];
        $firstSnippet = null;

        foreach ($targetBlankLinks as $index => $node) {
            $crawlerNode = new Crawler($node);
            $rel = strtolower(trim($crawlerNode->attr('rel') ?? ''));

            if ($index === 0) {
                $firstSnippet = substr($crawlerNode->outerHtml(), 0, 150);
            }

            // Check if rel contains the required value (e.g. noopener or noreferrer)
            if ($rel === '' || ! str_contains($rel, $requiredRel)) {
                if (count($failedNodes) < 3) {
                    $failedNodes[] = substr($crawlerNode->outerHtml(), 0, 150);
                }
            }
        }

        $passed = empty($failedNodes);
        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? 'Tautan target="_blank" tanpa rel="noopener".';
            $reason = $rule->reason_template ?? "Membuka link tab baru tanpa rel=\"{$requiredRel}\" rentan terhadap keamanan (reverse tabnabbing).";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "rel mengandung '{$requiredRel}'",
            'actual' => $passed ? "Semua target=\"_blank\" memiliki rel=\"{$requiredRel}\"" : 'Ditemukan target="_blank" tanpa rel yang sesuai',
            'selector' => $selector,
            'attribute' => 'rel',
            'html_snippet' => ! $passed ? implode("\n", $failedNodes) : $firstSnippet,
        ];
    }

    private function evaluateClassification(Crawler $dom, SeoRule $rule, ?AuditContext $context, array $config): array
    {
        $links = $dom->filter('a[href]');
        $pageUrl = $context ? $context->finalUrl : 'http://localhost';
        $pageHost = strtolower(parse_url($pageUrl, PHP_URL_HOST) ?? '');

        $counts = [
            'internal' => 0,
            'external' => 0,
            'relative' => 0,
            'anchor' => 0,
            'special' => 0,
        ];

        foreach ($links as $node) {
            $href = trim((new Crawler($node))->attr('href') ?? '');

            if ($href === '' || str_starts_with($href, '#')) {
                $counts['anchor']++;

                continue;
            }

            if (preg_match('/^(mailto:|tel:|javascript:|data:)/i', $href)) {
                $counts['special']++;

                continue;
            }

            $parsedHref = parse_url($href);
            $linkHost = strtolower($parsedHref['host'] ?? '');

            if ($linkHost === '') {
                // Relative URL -> inherently internal
                $counts['relative']++;
                $counts['internal']++;
            } elseif ($linkHost === $pageHost) {
                // Absolute URL with exact same host -> internal
                $counts['internal']++;
            } else {
                // Different host -> external
                $counts['external']++;
            }
        }

        $targetType = $config['target_type'] ?? 'internal'; // internal, external
        $operator = $config['operator'] ?? '>=';
        $expected = (int) ($config['expected'] ?? 1);

        $actual = $counts[$targetType] ?? 0;

        $passed = match ($operator) {
            '>' => $actual > $expected,
            '>=' => $actual >= $expected,
            '<' => $actual < $expected,
            '<=' => $actual <= $expected,
            '!=' => $actual !== $expected,
            default => $actual === $expected,
        };

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? "Jumlah tautan {$targetType} tidak sesuai.";
            $reason = $rule->reason_template ?? "Ditemukan {$actual} tautan {$targetType}, syarat rule: {$operator} {$expected}.";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "{$operator} {$expected} link {$targetType}",
            'actual' => "{$actual} link {$targetType} (Internal: {$counts['internal']}, External: {$counts['external']})",
            'selector' => 'a[href]',
            'attribute' => 'href',
            'html_snippet' => null,
        ];
    }
}
