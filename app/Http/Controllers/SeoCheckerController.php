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
        if (auth()->user()->rules()->count() === 0) {
            \App\Services\DefaultRules::populateFor(auth()->user());
        }

        $hasCompareRule = auth()->user()->rules()->where('is_active', true)
            ->whereIn('rule_type', ['compare_amp', 'alternate'])
            ->exists();

        $results = session('results');

        return view('seo-checker.index', compact('hasCompareRule', 'results'));
    }

    public function process(CheckSeoRequest $request)
    {
        if (auth()->user()->rules()->count() === 0) {
            \App\Services\DefaultRules::populateFor(auth()->user());
        }

        $lpUrls = $request->input('lp_urls_array');
        $ampUrls = $request->input('amp_urls_array') ?? [];
        $activeRules = auth()->user()->rules()->active()->get();

        $results = $this->auditService->audit($lpUrls, $ampUrls, $activeRules);

        // --- Save to History ---
        $run = \App\Models\AuditRun::create([
            'user_id' => auth()->id(),
            'total_urls' => count($lpUrls),
        ]);

        foreach ($results as $result) {
            \App\Models\AuditResult::create([
                'audit_run_id' => $run->id,
                'lp_url' => $result->lpUrl,
                'amp_url' => $result->ampUrl,
                'error_message' => $result->errorMessage,
                'checks_data' => array_map(function($check) {
                    return [
                        'ruleName' => $check->ruleName,
                        'status' => $check->status->value,
                        'issue' => $check->issue,
                        'actual' => $check->actual,
                    ];
                }, $result->checks),
            ]);
        }

        return redirect()->route('seo.index')->with('results', $results);
    }
}
