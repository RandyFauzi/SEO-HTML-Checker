<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">Dashboard</x-slot>

    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Overview</h2>
            <p class="text-sm text-slate-500 mt-2 font-medium">Welcome to your SEO Diagnostic Dashboard. Analytics and audit history will appear here.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50">
                <div class="flex items-center text-slate-500 mb-2">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="font-medium text-sm">Total Audits</span>
                </div>
                <div class="text-3xl font-bold text-slate-800">--</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50">
                <div class="flex items-center text-slate-500 mb-2">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium text-sm">Rules Passed</span>
                </div>
                <div class="text-3xl font-bold text-slate-800">--</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50">
                <div class="flex items-center text-slate-500 mb-2">
                    <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium text-sm">Rules Failed</span>
                </div>
                <div class="text-3xl font-bold text-slate-800">--</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <h3 class="text-lg font-bold text-slate-700 mb-2">No audit history yet</h3>
            <p class="text-slate-500 mb-6">Database logging for audit results is coming in Phase 3. For now, you can run real-time checks.</p>
            <a href="{{ route('seo.index') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-md transition-all">
                Go to Run Checker
            </a>
        </div>
    </div>
</x-admin-layout>
