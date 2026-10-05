<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditRun;

class HistoryController extends Controller
{
    public function index()
    {
        $runs = AuditRun::where('user_id', auth()->id())
            ->withCount('results')->with('results')
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

    public function destroy(AuditRun $run)
    {
        if ((int) $run->user_id !== (int) auth()->id()) {
            abort(403);
        }
        
        $run->delete();
        
        return redirect()->route('admin.history.index')->with('success', 'Riwayat berhasil dihapus.');
    }

    public function batchDestroy(\Illuminate\Http\Request $request)
    {
        $ids = $request->input('run_ids');
        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('admin.history.index')->with('error', 'Tidak ada riwayat yang dipilih.');
        }

        // We use Route Model Binding style logic if we rely on HashIds, but since we receive multiple hashids,
        // we can just decode them manually, or use a whereIn query. 
        // Hashids can be decoded via Vinkla Hashids facade, or the model trait.
        // Let's decode them one by one.
        $decodedIds = [];
        foreach ($ids as $hash) {
            $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hash);
            if (!empty($decoded)) {
                $decodedIds[] = $decoded[0];
            }
        }

        if (count($decodedIds) > 0) {
            AuditRun::where('user_id', auth()->id())
                ->whereIn('id', $decodedIds)
                ->delete();
            return redirect()->route('admin.history.index')->with('success', count($decodedIds) . ' riwayat berhasil dihapus.');
        }

        return redirect()->route('admin.history.index')->with('error', 'Riwayat tidak valid.');
    }
}
