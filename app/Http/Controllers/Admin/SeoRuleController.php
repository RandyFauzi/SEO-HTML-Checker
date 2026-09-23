<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoRule;
use App\Services\RuleEngine;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class SeoRuleController extends Controller
{
    public function index()
    {
        $rules = SeoRule::orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();
        return view('admin.rules.index', compact('rules'));
    }

    public function create()
    {
        return view('admin.rules.form', ['rule' => new SeoRule]);
    }

    private function validateRule(Request $request)
    {
        $types = array_map(fn(\App\Enums\RuleType $t) => $t->value, \App\Enums\RuleType::cases());
        $typesStr = implode(',', $types);

        return $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'rule_type' => 'required|string|in:' . $typesStr,
            'config' => 'nullable|string', // JSON string from frontend
            'issue_message' => 'nullable|string',
            'reason_template' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'severity' => 'required|in:warning,error',
            'is_active' => 'boolean',
        ]);
    }

    private function processRuleData(array $data)
    {
        if (isset($data['config']) && is_string($data['config'])) {
            $data['config'] = json_decode($data['config'], true);
        }
        return $data;
    }

    public function store(Request $request)
    {
        $data = $this->validateRule($request);
        $data = $this->processRuleData($data);
        $data['is_active'] = $request->has('is_active');
        
        SeoRule::create($data);
        return redirect()->route('admin.rules.index')->with('success', 'Rule created successfully.');
    }

    public function edit(SeoRule $rule)
    {
        return view('admin.rules.form', compact('rule'));
    }

    public function update(Request $request, SeoRule $rule)
    {
        $data = $this->validateRule($request);
        $data = $this->processRuleData($data);
        $data['is_active'] = $request->has('is_active');
        
        $rule->update($data);
        return redirect()->route('admin.rules.index')->with('success', 'Rule updated successfully.');
    }

    public function destroy(SeoRule $rule)
    {
        $rule->delete();
        return redirect()->route('admin.rules.index')->with('success', 'Rule deleted successfully.');
    }

    public function toggle(Request $request, SeoRule $rule)
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $rule->is_active,
                'message' => 'Rule status toggled.',
            ]);
        }
        return redirect()->route('admin.rules.index')->with('success', 'Rule status toggled.');
    }

    // New Endpoint: Rule Preview / Test
    public function test(Request $request, RuleEngine $engine)
    {
        $request->validate([
            'html' => 'required|string',
            'rule' => 'required|array',
        ]);

        $html = $request->input('html');
        $ruleData = $request->input('rule');
        
        // Mock a SeoRule model in memory
        $rule = new SeoRule();
        $rule->name = $ruleData['name'] ?? 'Test Rule';
        $rule->rule_type = $ruleData['rule_type'];
        $rule->category = 'Test';
        $rule->severity = 'error';
        $rule->config = is_string($ruleData['config'] ?? []) ? json_decode($ruleData['config'], true) : ($ruleData['config'] ?? []);

        $crawler = new Crawler($html);
        
        // We will just pass the same HTML as AMP for compare_amp testing
        $ampCrawler = $rule->rule_type === \App\Enums\RuleType::CompareAmp->value ? new Crawler($html) : null;

        $result = $engine->evaluate($crawler, $rule, $ampCrawler);

        return response()->json([
            'success' => true,
            'passed' => $result->status->value === 'passed',
            'status' => $result->status->value,
            'expected' => $result->expected,
            'actual' => $result->actual,
            'issue' => $result->issue,
            'reason' => $result->reason,
            'snippet' => $result->htmlSnippet,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|mimetypes:application/json,text/plain|max:2048',
        ]);

        $content = file_get_contents($request->file('json_file')->getRealPath());
        $rules = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($rules)) {
            return back()->with('error', 'Invalid JSON file format.');
        }

        $imported = 0;
        
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($rules, &$imported) {
                $types = array_map(fn(\App\Enums\RuleType $t) => $t->value, \App\Enums\RuleType::cases());
                
                foreach ($rules as $ruleData) {
                    if (empty($ruleData['name']) || empty($ruleData['rule_type'])) {
                        throw new \Exception("Missing required fields in JSON.");
                    }
                    if (!in_array($ruleData['rule_type'], $types)) {
                        throw new \Exception("Invalid rule_type: {$ruleData['rule_type']}");
                    }

                    SeoRule::create([
                        'code' => $ruleData['code'] ?? null,
                        'name' => $ruleData['name'],
                        'category' => $ruleData['category'] ?? 'General',
                        'rule_type' => $ruleData['rule_type'],
                        'config' => $ruleData['config'] ?? [],
                        'issue_message' => $ruleData['issue_message'] ?? null,
                        'reason_template' => $ruleData['reason_template'] ?? null,
                        'recommendation' => $ruleData['recommendation'] ?? null,
                        'severity' => in_array($ruleData['severity'] ?? '', ['warning', 'error']) ? $ruleData['severity'] : 'warning',
                        'is_active' => $ruleData['is_active'] ?? true,
                    ]);
                    $imported++;
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.rules.index')->with('success', "Imported $imported rules successfully.");
    }

    public function export()
    {
        $rules = SeoRule::all()->makeHidden(['id', 'created_at', 'updated_at']);
        
        $fileName = 'seo_rules_export_' . date('Y_m_d_His') . '.json';
        return response()->streamDownload(function () use ($rules) {
            echo json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $fileName, ['Content-Type' => 'application/json']);
    }
}
