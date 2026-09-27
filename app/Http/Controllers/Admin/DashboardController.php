<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoRule;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $rulesCount = auth()->user()->rules()->count();
        $activeRulesCount = auth()->user()->rules()->where('is_active', true)->count();
        $usersCount = User::count();
        
        $recentRuns = \App\Models\AuditRun::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
        
        return view('admin.dashboard.index', compact('rulesCount', 'activeRulesCount', 'usersCount', 'recentRuns'));
    }
}
