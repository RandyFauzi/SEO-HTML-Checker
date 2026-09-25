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
        $rulesCount = SeoRule::count();
        $activeRulesCount = SeoRule::where('is_active', true)->count();
        $usersCount = User::count();
        
        return view('admin.dashboard.index', compact('rulesCount', 'activeRulesCount', 'usersCount'));
    }
}
