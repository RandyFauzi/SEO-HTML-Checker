<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class AttributeRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        $config = $rule->config ?? [];
        $selector = $config['selector'] ?? '';
        $attribute = $config['attribute'] ?? '';

        $skipIfMissing = filter_var($config['skip_if_missing'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $scope = strtolower($config['scope'] ?? 'all'); // Default 'all' for attributes usually

        if (empty($selector) || empty($attribute)) {
            return [
                'passed' => false,
                'issue' => 'Selector atau Attribute kosong',
                'reason' => 'Rule tidak memiliki selector/attribute konfigurasi.',
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
                'expected' => "Atribut `{$attribute}` ada",
                'actual' => 'Elemen tidak ditemukan',
                'selector' => $selector,
                'attribute' => $attribute,
                'html_snippet' => null,
            ];
        }

        $condition = $config['condition'] ?? 'exists'; // exists, not_empty, equals, contains, regex
        $expectedValue = $config['expected_value'] ?? null;

        $passed = ($scope === 'any') ? false : true;
        $failedNodes = [];
        $firstSnippet = null;

        foreach ($nodes as $index => $node) {
            if ($scope === 'first' && $index > 0) break;
            
            $crawlerNode = new Crawler($node);
            $attrValue = $crawlerNode->attr($attribute);

            $nodePassed = true;

            if ($attrValue === null) {
                // Attribute does not exist at all
                $nodePassed = false;
            } else {
                switch ($condition) {
                    case 'not_empty':
                        if (trim($attrValue) === '') {
                            $nodePassed = false;
                        }
                        break;
                    case 'equals':
                        if ($attrValue !== $expectedValue) {
                            $nodePassed = false;
                        }
                        break;
                    case 'contains':
                        if (stripos($attrValue, $expectedValue) === false) {
                            $nodePassed = false;
                        }
                        break;
                    case 'not_contains':
                        if (stripos($attrValue, $expectedValue) !== false) {
                            $nodePassed = false;
                        }
                        break;
                    case 'regex':
                        if (! preg_match($expectedValue, $attrValue)) {
                            $nodePassed = false;
                        }
                        break;
                    case 'exists':
                    default:
                        // Already checked if null above
                        break;
                }
            }

            if ($scope === 'any') {
                if ($nodePassed) {
                    $passed = true;
                    break;
                }
            } else {
                if (! $nodePassed) {
                    $passed = false;
                    if (count($failedNodes) < 3) {
                        $failedNodes[] = substr($crawlerNode->outerHtml(), 0, 150);
                    }
                }
            }
            if ($index === 0) {
                $firstSnippet = substr($crawlerNode->outerHtml(), 0, 150);
            }
        }

        $issue = null;
        $reason = null;

        if (! $passed) {
            $issue = $rule->issue_message ?? "Atribut `{$attribute}` tidak ditemukan pada elemen {$rule->name}.";
            $reason = $rule->reason_template ?? 'Rule mewajibkan keberadaan atribut ini pada setiap elemen target.';
        }

        $expectedText = match ($condition) {
            'not_empty' => "Atribut `{$attribute}` tidak boleh kosong",
            'equals' => "Atribut `{$attribute}` harus bernilai '{$expectedValue}'",
            'contains' => "Atribut `{$attribute}` harus mengandung '{$expectedValue}'",
            'not_contains' => "Atribut `{$attribute}` tidak boleh mengandung '{$expectedValue}'",
            'regex' => "Atribut `{$attribute}` harus sesuai pola regex",
            default => "Atribut `{$attribute}` ada",
        };

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => $expectedText,
            'actual' => $passed ? 'Sesuai ketentuan' : 'Tidak sesuai (pada beberapa elemen)',
            'selector' => $selector,
            'attribute' => $attribute,
            'html_snippet' => ! $passed && ! empty($failedNodes) ? implode("\n", $failedNodes) : $firstSnippet,
        ];
    }
}
