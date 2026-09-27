<x-admin-layout>
    <x-slot name="title">Detail Pengecekan</x-slot>

    <div class="mb-8 flex items-center justify-between relative z-10">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.history.index') }}" class="text-slate-400 hover:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-3xl font-bold text-slate-800">Detail Pengecekan</h1>
            </div>
            <p class="text-slate-500 ml-9">Dijalankan pada: {{ $history->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    <div class="space-y-6 relative z-10">
        @foreach($history->results as $result)
        <div class="bg-white/70 backdrop-blur-2xl rounded-3xl border border-white/50 shadow-xl p-6">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <div>
                    <span class="text-xs font-bold tracking-wider text-slate-400 uppercase block mb-1">Landing Page</span>
                    <a href="{{ $result->lp_url }}" target="_blank" class="text-indigo-600 font-semibold hover:underline break-all">{{ $result->lp_url }}</a>
                </div>
                @if($result->amp_url)
                <div class="sm:text-right">
                    <span class="text-xs font-bold tracking-wider text-slate-400 uppercase block mb-1">AMP Page</span>
                    <a href="{{ $result->amp_url }}" target="_blank" class="text-emerald-600 font-semibold hover:underline break-all">{{ $result->amp_url }}</a>
                </div>
                @endif
            </div>

            @if($result->error_message)
            <div class="bg-red-50 text-red-700 p-4 rounded-2xl border border-red-100 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 shrink-0 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ $result->error_message }}</span>
                </div>
            </div>
            @elseif($result->checks_data)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200/60 text-xs uppercase tracking-wider text-slate-400 font-semibold">
                                <th class="pb-3 pl-3">Status</th>
                                <th class="pb-3">Kategori</th>
                                <th class="pb-3">Aturan</th>
                                <th class="pb-3 pr-3">Hasil / Saran</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm align-top">
                            @foreach($result->checks_data as $check)
                            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 pl-3">
                                    @if($check['passed'] ?? ($check['status'] ?? 'passed') === 'passed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-medium text-xs border border-emerald-200/60">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Lulus
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {{ ($check['severity'] ?? '') === 'error' ? 'bg-red-50 text-red-700 border-red-200/60' : 'bg-amber-50 text-amber-700 border-amber-200/60' }} font-medium text-xs border">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            {{ ($check['severity'] ?? '') === 'error' ? 'Kritis' : 'Peringatan' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 text-slate-500 font-medium">{{ $check['category'] ?? '' }}</td>
                                <td class="py-4">
                                    <div class="font-semibold text-slate-700 mb-0.5">{{ $check['rule_name'] ?? $check['ruleName'] ?? '' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $check['rule_code'] ?? '' }}</div>
                                </td>
                                <td class="py-4 pr-3">
                                    @if($check['passed'] ?? ($check['status'] ?? 'passed') === 'passed')
                                        <div class="text-slate-600">
                                            <span class="text-xs text-slate-400 block mb-0.5">Ditemukan:</span>
                                            {{ Str::limit($check['actual_value'] ?? $check['actual'] ?? 'Sesuai', 100) }}
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            <div class="text-red-600 font-medium">{{ $check['issue'] ?? '' }}</div>
                                            @if(!empty($check['reason']))
                                                <div class="text-slate-500 text-xs">{{ $check['reason'] }}</div>
                                            @endif
                                            @if(!empty($check['recommendation']))
                                                <div class="text-indigo-600 text-xs font-medium flex gap-1 mt-1">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    {{ $check['recommendation'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endforeach
    </div>
</x-admin-layout>
