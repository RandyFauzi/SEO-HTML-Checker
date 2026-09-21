<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckSeoRequest;
use App\Models\SeoRule;
use App\Services\SeoAuditService;

class SeoCheckerController extends Controller
{
    protected SeoAuditService $auditService;

    public function __construct(SeoAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $hasCompareRule = SeoRule::where('is_active', true)
            ->where('rule_type', 'compare_amp')
            ->exists();

        return view('seo-checker.index', compact('hasCompareRule'));
    }

    public function process(CheckSeoRequest $request)
    {
        $hasCompareRule = SeoRule::where('is_active', true)
            ->where('rule_type', 'compare_amp')
            ->exists();

        $lpUrls = $request->input('lp_urls_array');
        $ampUrls = $request->input('amp_urls_array') ?? [];

        $results = $this->auditService->audit($lpUrls, $ampUrls);

        return view('seo-checker.index', compact('results', 'hasCompareRule'));
    }
}
