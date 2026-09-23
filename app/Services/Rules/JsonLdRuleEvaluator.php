<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class JsonLdRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $jsonNodes = $dom->filter('script[type="application/ld+json"]');

        if ($jsonNodes->count() === 0) {
            return [
                'passed' => false,
                'issue' => 'Script JSON-LD tidak ditemukan.',
                'reason' => 'Halaman tidak memiliki tag `<script type="application/ld+json">`.',
                'expected' => 'Terdapat JSON-LD',
                'actual' => 'Tidak ditemukan',
                'selector' => 'script[type="application/ld+json"]',
                'attribute' => null,
                'html_snippet' => null,
            ];
        }

        $expected = strtolower(trim($rule->expected_value ?? $rule->value ?? ''));
        $operator = strtolower($rule->operator ?? 'equals');
        $jsonPath = $rule->attribute ?? '';

        $foundMatch = false;
        $firstSnippet = '';

        $jsonNodes->each(function (Crawler $node, $i) use ($jsonPath, $operator, $expected, &$foundMatch, &$firstSnippet) {
            if ($i === 0) {
                $firstSnippet = $node->outerHtml();
            }

            if ($foundMatch) {
                return;
            } // Short-circuit if already found

            $jsonText = $node->text();
            $data = json_decode($jsonText, true);

            if (is_array($data)) {
                if (empty($jsonPath)) {
                    if (str_contains(strtolower($jsonText), $expected)) {
                        $foundMatch = true;
                        $firstSnippet = $node->outerHtml();
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
                        $firstSnippet = $node->outerHtml();
                        break;
                    }
                }
            }
        });

        $pathMsg = empty($jsonPath) ? 'JSON text' : "Path `{$jsonPath}`";
        $issue = null;
        $reason = null;

        if (!$foundMatch) {
            $issue = "Data JSON-LD tidak sesuai spesifikasi SEO.";
            $reason = "Tidak ada satu pun blok JSON-LD di mana {$pathMsg} memenuhi syarat `{$operator} '{$expected}'`.";
        }

        return [
            'passed' => $foundMatch,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "{$operator} '{$expected}'",
            'actual' => $foundMatch ? 'Sesuai ekspektasi' : 'Tidak sesuai',
            'selector' => 'script[type="application/ld+json"]',
            'attribute' => empty($jsonPath) ? null : $jsonPath,
            'html_snippet' => mb_strimwidth($firstSnippet, 0, 500, '...'),
        ];
    }
}
