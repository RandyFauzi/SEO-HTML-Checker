<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditRun;

class HistoryController extends Controller
{
    public function index()
    {
        $runs = AuditRun::where('user_id', auth()->id())
            ->withCount('results')
            ->latest()
            ->paginate(15);
            
        return view('admin.history.index', compact('runs'));
    }

    public function show(AuditRun $run)
    {
        if ((int) $run->user_id !== (int) auth()->id()) {
            abort(403);
        }
        
        $run->load('results');
        return view('admin.history.show', ['history' => $run]);
    }
}
