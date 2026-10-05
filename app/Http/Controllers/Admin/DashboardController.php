<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoRule;
use App\Models\User;
use App\Models\AuditRun;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        // Stats
        $totalAudits = AuditRun::where('user_id', $userId)->count();
        
        $rulesPassed = 0;
        $rulesFailed = 0;
        
        // Calculate passed/failed from all results
        // To avoid memory issues, we can chunk or just get the latest run if it's too big, 
        // but for now let's just aggregate from all results.
        $results = \App\Models\AuditResult::whereHas('run', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->get();

        foreach ($results as $result) {
            $checks = $result->checks_data ?? [];
            foreach ($checks as $check) {
                if (isset($check['status'])) {
                    if ($check['status'] === 'passed') {
                        $rulesPassed++;
                    } elseif ($check['status'] === 'failed' || $check['status'] === 'error') {
                        $rulesFailed++;
                    }
                }
            }
        }
        
        $recentRuns = AuditRun::where('user_id', $userId)
            ->with('results')->latest()
            ->take(5)
            ->get();
        
        return view('admin.dashboard.index', compact(
            'totalAudits', 
            'rulesPassed', 
            'rulesFailed', 
            'recentRuns'
        ));
    }
}
