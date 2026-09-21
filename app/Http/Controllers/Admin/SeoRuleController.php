<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoRule;
use Illuminate\Http\Request;

class SeoRuleController extends Controller
{
    public function index()
    {
        $rules = SeoRule::orderBy('id', 'desc')->get();

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
            'name' => 'required|string|max:255',
            'target_selector' => 'nullable|string|max:255',
            'rule_type' => 'required|string|in:' . $typesStr,
            'expected_value' => 'nullable|string',
            'attribute' => 'nullable|string|max:255',
            'operator' => 'nullable|string|max:50',
            'min_value' => 'nullable|integer',
            'max_value' => 'nullable|integer',
            'regex_pattern' => 'nullable|string',
            'severity' => 'required|in:warning,error',
            'is_active' => 'boolean',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRule($request);
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
                        throw new \Exception("Missing required fields (name, rule_type) in JSON.");
                    }
                    if (!in_array($ruleData['rule_type'], $types)) {
                        throw new \Exception("Invalid rule_type: {$ruleData['rule_type']}");
                    }

                    SeoRule::create([
                        'name' => $ruleData['name'],
                        'target_selector' => $ruleData['target_selector'] ?? null,
                        'rule_type' => $ruleData['rule_type'],
                        'expected_value' => $ruleData['expected_value'] ?? null,
                        'attribute' => $ruleData['attribute'] ?? null,
                        'operator' => $ruleData['operator'] ?? null,
                        'min_value' => $ruleData['min_value'] ?? null,
                        'max_value' => $ruleData['max_value'] ?? null,
                        'regex_pattern' => $ruleData['regex_pattern'] ?? null,
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
