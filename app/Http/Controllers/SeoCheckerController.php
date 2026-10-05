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
        $hasCompareRule = auth()->user()->rules()->where('is_active', true)
            ->whereIn('rule_type', ['compare_amp', 'alternate'])
            ->exists();

        $sessionResults = session('results');
        $results = null;

        if (is_array($sessionResults)) {
            // Reconstruct the objects in case the session driver (like Redis/JSON) returns associative arrays
            $results = array_map(function ($result) {
                // If it's already the right object, return it
                if ($result instanceof \App\DTO\UrlAuditResult) {
                    return $result;
                }
                
                // Convert to array for processing
                $data = json_decode(json_encode($result), true);
                
                $checks = [];
                foreach ($data['checks'] ?? [] as $c) {
                    // Handle enums safely
                    $ruleTypeVal = is_string($c['ruleType']) ? $c['ruleType'] : ($c['ruleType']['value'] ?? $c['ruleType'] ?? 'exist');
                    $statusVal = is_string($c['status']) ? $c['status'] : ($c['status']['value'] ?? $c['status'] ?? 'error');
                    
                    $checks[] = new \App\DTO\CheckResult(
                        ruleName: $c['ruleName'] ?? '',
                        ruleType: \App\Enums\RuleType::tryFrom($ruleTypeVal) ?? \App\Enums\RuleType::Exist,
                        status: \App\Enums\CheckStatus::tryFrom($statusVal) ?? \App\Enums\CheckStatus::Error,
                        category: $c['category'] ?? null,
                        issue: $c['issue'] ?? null,
                        reason: $c['reason'] ?? null,
                        expected: $c['expected'] ?? null,
                        actual: $c['actual'] ?? null,
                        selector: $c['selector'] ?? null,
                        attribute: $c['attribute'] ?? null,
                        htmlSnippet: $c['htmlSnippet'] ?? null,
                    );
                }

                return new \App\DTO\UrlAuditResult(
                    lpUrl: $data['lpUrl'] ?? '',
                    ampUrl: $data['ampUrl'] ?? null,
                    errorMessage: $data['errorMessage'] ?? null,
                    checks: $checks,
                    redirectChain: $data['redirectChain'] ?? [],
                );
            }, $sessionResults);
        }

        return view('seo-checker.index', compact('hasCompareRule', 'results'));
    }

    public function process(CheckSeoRequest $request)
    {
        $lpUrls = $request->input('lp_urls_array');
        $ampUrls = $request->input('amp_urls_array') ?? [];
        $activeRules = auth()->user()->rules()->active()->get();

        $results = $this->auditService->audit($lpUrls, $ampUrls, $activeRules);

        // --- Save to History ---
        \App\Helpers\Logger::log('Audit URL', 'Menjalankan audit SEO pada URL: ' . implode(', ', $lpUrls));
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
