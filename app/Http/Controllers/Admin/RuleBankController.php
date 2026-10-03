<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RuleBankController extends Controller
{
    public function index()
    {
        $path = base_path('database/data/rules_import.json');
        
        $rules = [];
        if (File::exists($path)) {
            $json = json_decode(File::get($path), true);
            $rules = $json['rules'] ?? [];
        }

        // Get currently installed codes
        $installedCodes = SeoRule::whereNotNull('code')->where('user_id', auth()->id())->pluck('code')->toArray();

        // Organize rules by prefix (e.g., HEAD, ROB, MD, etc.)
        $categorizedRules = [];
        foreach ($rules as $rule) {
            $codeParts = explode('-', $rule['code'] ?? 'MISC');
            $category = $codeParts[0];
            $categorizedRules[$category][] = $rule;
        }

        return view('admin.rules.bank', compact('categorizedRules', 'installedCodes'));
    }

    public function install(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $path = base_path('database/data/rules_import.json');
        
        if (!File::exists($path)) {
            return back()->with('error', 'File Bank Rule tidak ditemukan.');
        }

        $json = json_decode(File::get($path), true);
        $rules = $json['rules'] ?? [];

        $ruleData = collect($rules)->firstWhere('code', $request->code);

        if (!$ruleData) {
            return back()->with('error', 'Rule tidak ditemukan di dalam bank.');
        }

        // Check if already installed
        $existing = SeoRule::where('code', $ruleData['code'])
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return back()->with('error', 'Rule ini sudah terpasang.');
        }

        // Create new rule
        SeoRule::create([
            'user_id' => auth()->id(),
            'code' => $ruleData['code'],
            'name' => $ruleData['name'],
            'description' => $ruleData['description'] ?? ($ruleData['name'] . ' (Diambil dari Bank Rules)'),
            'category' => 'SEO', // Default
            'target_page' => 'all', // Default
            'rule_type' => $ruleData['type'] === 'text_match' ? 'text_match' : ($ruleData['type'] === 'exist' ? 'exist' : $ruleData['type']),
            'config' => $ruleData['configuration'] ?? [],
            'issue_message' => $ruleData['issue_message'] ?? ($ruleData['name'] . ' gagal verifikasi.'),
            'reason_template' => $ruleData['reason_template'] ?? ('Pengecekan gagal pada elemen ' . ($ruleData['selector'] ?? '')),
            'recommendation' => $ruleData['recommendation'] ?? 'Perbaiki elemen HTML sesuai standar SEO.',
            'severity' => $ruleData['severity'] ?? 'warning',
            'is_active' => $ruleData['is_active'] ?? true,
        ]);

        return back()->with('success', 'Rule ' . $ruleData['code'] . ' berhasil dipasang ke sistem Anda!');
    }
}
