<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">Dashboard</x-slot>

    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Overview</h2>
            <p class="text-sm text-slate-500 mt-2 font-medium">Welcome to your SEO Diagnostic Dashboard. Analytics and audit history will appear here.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-panel p-6 rounded-[2rem] shadow-[0_4px_24px_rgba(0,0,0,0.02)] transition-all hover:-translate-y-1">
                <div class="flex items-center text-slate-500 mb-2">
                    <svg class="w-5 h-5 mr-2 text-indigo-500 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="font-bold text-sm">Total Audits</span>
                </div>
                <div class="text-4xl font-extrabold text-slate-800">{{ number_format($totalAudits) }}</div>
            </div>
            <div class="glass-panel p-6 rounded-[2rem] shadow-[0_4px_24px_rgba(0,0,0,0.02)] transition-all hover:-translate-y-1">
                <div class="flex items-center text-slate-500 mb-2">
                    <svg class="w-5 h-5 mr-2 text-emerald-500 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm">Rules Passed</span>
                </div>
                <div class="text-4xl font-extrabold text-emerald-600">{{ number_format($rulesPassed) }}</div>
            </div>
            <div class="glass-panel p-6 rounded-[2rem] shadow-[0_4px_24px_rgba(0,0,0,0.02)] transition-all hover:-translate-y-1">
                <div class="flex items-center text-slate-500 mb-2">
                    <svg class="w-5 h-5 mr-2 text-red-500 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm">Rules Failed</span>
                </div>
                <div class="text-4xl font-extrabold text-red-600">{{ number_format($rulesFailed) }}</div>
            </div>
        </div>

        <div class="glass-panel rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-12 text-center relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-300/30 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-pink-300/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 text-left">
                @if(isset($recentRuns) && $recentRuns->isNotEmpty())
                    <h3 class="text-xl font-extrabold text-slate-800 mb-4 tracking-tight">Riwayat Audit Terakhir</h3>
                    <table class="min-w-full divide-y divide-gray-200 bg-white/50 rounded-lg overflow-hidden shadow">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL Utama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($recentRuns as $run)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $run->created_at->format('d M Y, H:i') }}</td>
                                  <td class="px-6 py-4 whitespace-nowrap"><a href="{{ $run->results->first()->lp_url ?? '#' }}" target="_blank" class="text-indigo-600 hover:underline">{{ Str::limit($run->results->first()->lp_url ?? '-', 40) }}</a></td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $run->total_urls }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.history.show', $run) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Lihat Detail</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-6 text-center">
                        <a href="{{ route('admin.history.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Lihat Semua Riwayat &rarr;</a>
                    </div>
                @else
                    <div class="text-center">
                        <svg class="w-20 h-20 mx-auto text-slate-300 mb-6 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <h3 class="text-2xl font-extrabold text-slate-800 mb-3 tracking-tight">Belum ada riwayat audit</h3>
                        <p class="text-slate-500 mb-8 max-w-md mx-auto leading-relaxed">Anda belum melakukan scan URL apapun.</p>
                        <a href="{{ route('seo.index') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-2xl shadow-lg shadow-indigo-600/20 transition-all hover:scale-105 hover:-translate-y-1">
                            Mulai Scan Baru
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
