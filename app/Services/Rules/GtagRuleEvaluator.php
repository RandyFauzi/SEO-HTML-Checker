<?php

namespace App\Services\Rules;

use App\DTO\AuditContext;
use App\Models\Brand;
use App\Models\SeoRule;
use Symfony\Component\DomCrawler\Crawler;

class GtagRuleEvaluator implements RuleEvaluatorInterface
{
    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null, ?AuditContext $context = null): array
    {
        // Get brands belonging to the current user
        $userId = auth()->id() ?? 1; // Fallback to 1 if testing without auth
        $brands = Brand::where('user_id', $userId)->get();
        if ($brands->isEmpty()) {
            return [
                'passed' => false,
                'issue' => 'No brands configured with GTAG IDs',
                'reason' => 'Please add a brand with a GTAG ID in your settings.',
            ];
        }

        $html = $dom->html();
        $foundGtags = [];
        
        foreach ($brands as $brand) {
            if (empty($brand->gtag_id)) continue;
            
            if (strpos($html, $brand->gtag_id) !== false) {
                $foundGtags[] = $brand->gtag_id . ' (' . $brand->brand_name . ')';
            }
        }

        if (empty($foundGtags)) {
            return [
                'passed' => false,
                'issue' => 'gtag tidak sesuai',
                'reason' => 'GTAG ID dari brand Anda tidak ditemukan di dalam HTML.',
            ];
        }

        return [
            'passed' => true,
            'expected' => 'GTAG ID',
            'actual' => implode(', ', $foundGtags),
        ];
    }
}
