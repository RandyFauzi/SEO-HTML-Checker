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
        $skipIfMissing = filter_var($config['skip_if_missing'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $scope = strtolower($config['scope'] ?? 'first'); // 'first', 'all', 'any'

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
            if ($skipIfMissing) {
                return [
                    'passed' => true,
                    'skipped' => true,
                    'issue' => null,
                    'reason' => 'Elemen di-skip karena tidak ditemukan di HTML.',
                    'expected' => 'Opsional',
                    'actual' => 'Tidak ditemukan (di-skip)',
                    'selector' => $selector,
                    'attribute' => $attribute,
                    'html_snippet' => null,
                ];
            }
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
                'html_snippet' => null,
            ];
        }

        if (! str_starts_with($regex, '/') && ! str_starts_with($regex, '#') && ! str_starts_with($regex, '@')) {
            $regex = '@'.str_replace('@', '\@', $regex).'@u';
        }

        $passed = ($scope === 'any') ? false : true;
        $failedNodes = [];
        $firstSnippet = null;
        $actualSnippet = null;

        $originalBacktrack = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '10000');

        try {
            foreach ($nodes as $index => $node) {
                if ($scope === 'first' && $index > 0) break;
                
                $crawlerNode = new Crawler($node);
                $htmlSnippet = substr($crawlerNode->outerHtml(), 0, 500);
                if ($index === 0) $firstSnippet = $htmlSnippet;

                $text = $this->extractContent($crawlerNode, $rule);

                if (strlen($text) > 50000) {
                    $nodePassed = false;
                } else {
                    $result = @preg_match($regex, $text);
                    if ($result === false) {
                        return [
                            'passed' => false,
                            'issue' => 'Eksekusi Regex gagal.',
                            'reason' => 'Pattern Regex invalid atau terjadi catastrophic backtracking.',
                            'expected' => 'Regex berhasil dieksekusi',
                            'actual' => 'Error/Timeout',
                            'selector' => $selector,
                            'attribute' => $attribute,
                            'html_snippet' => $htmlSnippet,
                        ];
                    }
                    $nodePassed = ($result === 1);
                }

                if ($scope === 'any') {
                    if ($nodePassed) {
                        $passed = true;
                        $actualSnippet = mb_strimwidth($text, 0, 50, '...');
                        break;
                    }
                } else {
                    if (!$nodePassed) {
                        $passed = false;
                        if (count($failedNodes) < 3) {
                            $failedNodes[] = $htmlSnippet;
                        }
                        $actualSnippet = mb_strimwidth($text, 0, 50, '...');
                    }
                }
            }
        } finally {
            ini_set('pcre.backtrack_limit', $originalBacktrack);
        }
        
        if ($passed && $actualSnippet === null) {
            $actualSnippet = 'Semua elemen cocok dengan pola';
        }

        return [
            'passed' => $passed,
            'issue' => $passed ? null : 'Konten tidak cocok dengan pola regex.',
            'reason' => $passed ? null : 'Teks yang diekstrak tidak memenuhi pola regex yang ditentukan.',
            'expected' => "Cocok dengan: {$regex}",
            'actual' => $actualSnippet,
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => ! $passed && ! empty($failedNodes) ? implode("\n", $failedNodes) : $firstSnippet,
        ];
    }
}
