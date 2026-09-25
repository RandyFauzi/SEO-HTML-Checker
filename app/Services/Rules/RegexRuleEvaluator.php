<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class RegexRuleEvaluator implements RuleEvaluatorInterface
{
    use NodeExtractorTrait;

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? $rule->target_selector ?? '';
        $attribute = $config['attribute'] ?? $rule->attribute ?? null;

        if (empty($selector)) {
            return [
                'passed' => false,
                'issue' => 'Selector kosong.',
                'reason' => 'Rule tidak memiliki selector konfigurasi.',
                'selector' => $selector,
                'attribute' => $attribute,
            ];
        }

        $nodes = $dom->filter($selector);

        if ($nodes->count() === 0) {
            return [
                'passed' => false,
                'issue' => 'Elemen tidak ditemukan.',
                'reason' => "Tidak ada elemen yang cocok dengan selector `{$selector}`.",
                'expected' => 'Elemen harus ada',
                'actual' => 'Elemen tidak ditemukan',
                'selector' => $selector,
                'attribute' => $attribute,
                'html_snippet' => null,
            ];
        }

        $htmlSnippet = substr($nodes->first()->outerHtml(), 0, 500);
        $text = $this->extractContent($nodes->first(), $rule);

        // Security limit: do not execute regex on extremely large strings to prevent CPU spikes
        if (strlen($text) > 50000) {
            return [
                'passed' => false,
                'issue' => 'Konten terlalu besar.',
                'reason' => 'Konten melebihi 50,000 karakter, evaluasi regex dibatalkan untuk mencegah CPU spike.',
                'expected' => '< 50000 karakter',
                'actual' => strlen($text).' karakter',
                'selector' => $selector,
                'attribute' => $attribute,
                'html_snippet' => $htmlSnippet,
            ];
        }

        $regex = $config['regex_pattern'] ?? $config['pattern'] ?? $config['expected_value'] ?? $rule->regex_pattern ?? $rule->expected_value ?? $rule->value ?? '';

        if (empty($regex)) {
            return [
                'passed' => false,
                'issue' => 'Pattern Regex kosong.',
                'reason' => 'Rule mewajibkan regex tapi field pattern/expected_value tidak diisi.',
                'expected' => 'Pattern regex valid',
                'actual' => 'Kosong',
                'selector' => $selector,
                'attribute' => $attribute,
                'html_snippet' => $htmlSnippet,
            ];
        }

        if (! str_starts_with($regex, '/') && ! str_starts_with($regex, '#') && ! str_starts_with($regex, '@')) {
            $regex = '@'.str_replace('@', '\@', $regex).'@u';
        }

        // Set PCRE backtrack limit temporarily to prevent catastrophic backtracking
        $originalBacktrack = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '10000');

        try {
            $result = @preg_match($regex, $text);

            if ($result === false) {
                return [
                    'passed' => false,
                    'issue' => 'Eksekusi Regex gagal.',
                    'reason' => 'Pattern Regex invalid atau terjadi catastrophic backtracking.',
                    'expected' => 'Regex berhasil dieksekusi',
                    'actual' => 'Error/Timeout',
                    'selector' => $rule->target_selector,
                    'attribute' => $rule->attribute,
                    'html_snippet' => $htmlSnippet,
                ];
            }

            $passed = $result === 1;

        } finally {
            // Restore original limit
            ini_set('pcre.backtrack_limit', $originalBacktrack);
        }

        return [
            'passed' => $passed,
            'issue' => $passed ? null : 'Konten tidak cocok dengan pola regex.',
            'reason' => $passed ? null : 'Teks yang diekstrak tidak memenuhi pola regex yang ditentukan.',
            'expected' => "Cocok dengan: {$regex}",
            'actual' => mb_strimwidth($text, 0, 50, '...'),
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => $htmlSnippet,
        ];
    }
}
