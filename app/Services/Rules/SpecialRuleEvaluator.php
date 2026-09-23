<?php

namespace App\Services\Rules;

use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class SpecialRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $config = $rule->config ?? [];
        $specialType = $config['special_type'] ?? '';

        return match ($specialType) {
            'h2_hierarchy' => $this->evaluateH2Hierarchy($dom, $rule),
            default => [
                'passed' => false,
                'issue' => "Special Rule tidak dikenali",
                'reason' => "Tipe rule khusus '{$specialType}' belum diimplementasikan.",
                'selector' => null,
            ],
        };
    }

    private function evaluateH2Hierarchy(Crawler $dom, SeoRule $rule): array
    {
        $passed = true;
        $issue = null;
        $reason = null;
        $htmlSnippet = null;

        // Mendapatkan semua heading di halaman secara berurutan
        $headings = $dom->filter('h1, h2, h3, h4, h5, h6');
        
        $hasH2 = false;
        
        foreach ($headings as $node) {
            $tag = strtolower($node->nodeName);
            
            if ($tag === 'h2') {
                $hasH2 = true;
            } elseif ($tag === 'h3' && !$hasH2) {
                $passed = false;
                $htmlSnippet = substr($node->ownerDocument->saveHTML($node), 0, 150);
                break;
            }
        }

        if (!$passed) {
            $issue = $rule->issue_message ?? "Heading hierarchy skips H2.";
            $reason = $rule->reason_template ?? "Ditemukan elemen H3 sebelum elemen H2. Struktur heading harus urut untuk aksesibilitas dan SEO.";
        }

        return [
            'passed' => $passed,
            'issue' => $issue,
            'reason' => $reason,
            'expected' => "H2 harus ada sebelum H3",
            'actual' => $passed ? "Hierarki benar" : "H3 muncul sebelum H2",
            'selector' => 'h1, h2, h3, h4, h5, h6',
            'attribute' => null,
            'html_snippet' => $htmlSnippet,
        ];
    }
}
