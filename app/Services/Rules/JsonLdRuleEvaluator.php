<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class JsonLdRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? 'script[type="application/ld+json"]';

        $jsonNodes = $dom->filter($selector);

        if ($jsonNodes->count() === 0) {
            return [
                'passed' => false,
                'issue' => 'Script JSON-LD tidak ditemukan.',
                'reason' => "Halaman tidak memiliki tag `{$selector}`.",
                'expected' => 'Terdapat JSON-LD',
                'actual' => 'Tidak ditemukan',
                'selector' => $selector,
                'attribute' => null,
                'html_snippet' => null,
            ];
        }

        // Sub-rule types: valid, has_type, has_context
        $checkType = $config['check_type'] ?? 'valid'; // 'valid', 'has_type', 'has_context', 'match'

        $foundMatch = false;
        $firstSnippet = '';
        $invalidReason = '';

        $jsonNodes->each(function (Crawler $node, $i) use ($checkType, $config, &$foundMatch, &$firstSnippet, &$invalidReason) {
            if ($i === 0) {
                $firstSnippet = $node->outerHtml();
            }

            if ($foundMatch && $checkType !== 'valid') {
                return; // For exist checks, 1 is enough. For validity, maybe we check all? We'll check any valid
            }

            $jsonText = $node->text();
            $data = json_decode($jsonText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $invalidReason = 'Format JSON tidak valid: '.json_last_error_msg();

                return; // Not valid
            }

            if (! is_array($data)) {
                $invalidReason = 'JSON bukan berupa array/object.';

                return;
            }

            if ($checkType === 'valid') {
                $foundMatch = true;

                return;
            }

            if ($checkType === 'has_type') {
                $expected = $config['expected'] ?? null;
                $type = data_get($data, '@type');
                if ($type && (! $expected || (is_array($type) ? in_array($expected, $type) : $type === $expected))) {
                    $foundMatch = true;
                }

                return;
            }

            if ($checkType === 'has_context') {
                $expected = $config['expected'] ?? 'https://schema.org';
                $context = data_get($data, '@context');
                if ($context === $expected) {
                    $foundMatch = true;
                }

                return;
            }

            if ($checkType === 'match') {
                $expected = strtolower(trim($config['expected'] ?? ''));
                $operator = strtolower($config['operator'] ?? 'equals');
                $jsonPath = $config['attribute'] ?? '';

                if (empty($jsonPath)) {
                    if (str_contains(strtolower($jsonText), $expected)) {
                        $foundMatch = true;
                    }

                    return;
                }

                $extracted = data_get($data, $jsonPath);
                $valuesToCheck = is_array($extracted) ? $extracted : [$extracted];

                foreach ($valuesToCheck as $val) {
                    if ($val === null) {
                        continue;
                    }
                    $valLower = strtolower((string) $val);
                    $matched = match ($operator) {
                        'equals', '=' => $valLower === $expected,
                        'not_equals', '!=' => $valLower !== $expected,
                        'starts_with' => str_starts_with($valLower, $expected),
                        'ends_with' => str_ends_with($valLower, $expected),
                        'contains' => str_contains($valLower, $expected),
                        'not_contains' => ! str_contains($valLower, $expected),
                        default => $valLower === $expected,
                    };
                    if ($matched) {
                        $foundMatch = true;
                        break;
                    }
                }
            }
        });

        $issue = null;
        $reason = null;

        if (! $foundMatch) {
            $issue = $rule->issue_message ?? "Data JSON-LD tidak sesuai dengan pengecekan `{$checkType}`.";
            $reason = $rule->reason_template ?? ($invalidReason ?: 'Tidak ada satu pun blok JSON-LD yang memenuhi kriteria.');
        }

        return [
            'passed' => $foundMatch,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $config['expected'] ?? 'Valid JSON-LD',
            'actual' => $foundMatch ? 'Sesuai ekspektasi' : 'Tidak sesuai',
            'selector' => $selector,
            'attribute' => $config['attribute'] ?? null,
            'html_snippet' => mb_strimwidth($firstSnippet, 0, 500, '...'),
        ];
    }
}
