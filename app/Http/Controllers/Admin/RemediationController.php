<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AI\HtmlRemediationService;
use App\Models\SeoRule;
use App\Models\AuditRemediation;

class RemediationController extends Controller
{
    public function generate(Request $request, HtmlRemediationService $service)
    {
        $request->validate([
            'rule_id' => 'required|exists:seo_rules,id',
            'run_id' => 'required|exists:audit_runs,id',
            'html_snippet' => 'required|string',
            'issue' => 'nullable|string',
            'expected' => 'nullable|string'
        ]);

        try {
            $rule = SeoRule::findOrFail($request->rule_id);
            $fixedHtml = $service->generateFix($request->html_snippet, $rule, $request->issue ?? '', $request->expected ?? '');

            // Store in DB for history/tracking using the available columns
            $remediation = AuditRemediation::create([
                'audit_run_id' => $request->run_id,
                'remediation_log' => [
                    'seo_rule_id' => $rule->id,
                    'original_html' => $request->html_snippet,
                    'fixed_html' => $fixedHtml,
                ]
            ]);

            return response()->json([
                'success' => true,
                'remediation_id' => $remediation->id,
                'original' => $request->html_snippet,
                'fixed' => $fixedHtml
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
