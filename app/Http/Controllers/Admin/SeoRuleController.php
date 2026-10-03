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
        $user = auth()->user();
        
        $rules = $user->rules()->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();
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
            'target_page' => 'required|in:all,lp,amp',
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
        
        auth()->user()->rules()->create($data);
        return redirect()->route('admin.rules.index')->with('success', 'Aturan berhasil dibuat.');
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
        return redirect()->route('admin.rules.index')->with('success', 'Aturan berhasil diperbarui.');
    }

    public function destroy(SeoRule $rule)
    {
        $rule->delete();
        return redirect()->route('admin.rules.index')->with('success', 'Aturan berhasil dihapus.');
    }

    public function destroyAll()
    {
        auth()->user()->rules()->delete();
        return redirect()->route('admin.rules.index')->with('success', 'Semua aturan berhasil dihapus.');
    }

    public function toggle(Request $request, SeoRule $rule)
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $rule->is_active,
                'message' => 'Status aturan diubah.',
            ]);
        }
        return redirect()->route('admin.rules.index')->with('success', 'Status aturan diubah.');
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

    public function generateViaAi(Request $request, \App\Services\AI\RuleGeneratorService $aiService)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);
        
        try {
            $ruleData = $aiService->generate($request->prompt);
            return response()->json([
                'success' => true,
                'data' => $ruleData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'json_file' => 'nullable|file|mimetypes:application/json,text/plain|max:2048',
            'json_text' => 'nullable|string',
        ]);

        if (!$request->hasFile('json_file') && empty($request->input('json_text'))) {
            return back()->with('error', 'Silakan unggah file JSON atau masukkan teks JSON.');
        }

        $content = '';
        if ($request->hasFile('json_file')) {
            $content = file_get_contents($request->file('json_file')->getRealPath());
        } else {
            $content = $request->input('json_text');
        }

        $rules = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($rules)) {
            return back()->with('error', 'Format JSON tidak valid.');
        }

        $imported = 0;
        
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($rules, &$imported) {
                $types = array_map(fn(\App\Enums\RuleType $t) => $t->value, \App\Enums\RuleType::cases());
                
                // Peta fallback untuk menoleransi JSON AI versi lama
                $legacyMap = [
                    'tag_presence' => 'exist',
                    'tag_count' => 'count',
                    'attribute_presence' => 'attribute',
                    'text_length' => 'length',
                    'regex_match' => 'regex',
                ];

                foreach ($rules as $ruleData) {
                    if (empty($ruleData['name']) || empty($ruleData['rule_type'])) {
                        throw new \Exception("Missing required fields in JSON.");
                    }
                    
                    // Auto-fix legacy rule types
                    if (isset($legacyMap[$ruleData['rule_type']])) {
                        $ruleData['rule_type'] = $legacyMap[$ruleData['rule_type']];
                    }

                    if (!in_array($ruleData['rule_type'], $types)) {
                        throw new \Exception("Invalid rule_type: {$ruleData['rule_type']}");
                    }

                    auth()->user()->rules()->create([
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
            return back()->with('error', 'Gagal mengimpor: ' . $e->getMessage());
        }

        return redirect()->route('admin.rules.index')->with('success', "Berhasil mengimpor $imported aturan.");
    }

    public function export()
    {
        $rules = auth()->user()->rules()->get()->makeHidden(['id', 'user_id', 'created_at', 'updated_at']);
        
        $fileName = 'seo_rules_export_' . date('Y_m_d_His') . '.json';
        return response()->streamDownload(function () use ($rules) {
            echo json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $fileName, ['Content-Type' => 'application/json']);
    }
}
