<x-admin-layout>
    <x-slot name="title">SEO Checker</x-slot>
    <x-slot name="header">SEO HTML Checker</x-slot>

    <div class="max-w-6xl mx-auto" x-data="{ hasCompareRule: {{ $hasCompareRule ? 'true' : 'false' }} }">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Run SEO Audit</h2>
                <p class="text-sm text-slate-500 mt-2 font-medium">Check multiple URLs against your active SEO rules and identify improvements instantly.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl relative mb-8 shadow-sm text-sm font-medium">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-lg shadow-slate-200/50 rounded-2xl border border-slate-100 p-8 mb-10 relative overflow-hidden">
            <!-- Decorative gradient orb -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <form action="{{ route('seo.process') }}" method="POST" class="relative z-10 space-y-8">
                @csrf
                
                <div class="grid grid-cols-1" :class="{ 'lg:grid-cols-2 gap-8': hasCompareRule }">
                    <div class="space-y-3">
                        <label for="lp_urls" class="flex items-center text-sm font-semibold text-slate-700">
                            Landing Page URLs
                            <span class="ml-2 text-xs font-normal text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Max 10</span>
                        </label>
                        <textarea name="lp_urls" id="lp_urls" rows="6" placeholder="https://example.com/page-1&#10;https://example.com/page-2" class="w-full border border-slate-200 rounded-xl p-4 text-sm font-mono text-slate-600 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 transition-all shadow-inner placeholder:text-slate-300" required>{{ old('lp_urls') }}</textarea>
                    </div>

                    <div x-show="hasCompareRule" x-cloak class="space-y-3">
                        <label for="amp_urls" class="flex items-center text-sm font-semibold text-slate-700">
                            AMP URLs
                            <span class="ml-2 text-xs font-normal text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Must match LP order</span>
                        </label>
                        <textarea name="amp_urls" id="amp_urls" rows="6" placeholder="https://amp.example.com/page-1&#10;https://amp.example.com/page-2" class="w-full border border-slate-200 rounded-xl p-4 text-sm font-mono text-slate-600 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 transition-all shadow-inner placeholder:text-slate-300" :required="hasCompareRule">{{ old('amp_urls') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-8 rounded-xl shadow-md shadow-indigo-200 transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-indigo-300 flex items-center">
                        <svg class="w-5 h-5 mr-2 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Run Checker
                    </button>
                </div>
            </form>
        </div>

        @if(isset($results))
            <div class="space-y-6">
                <h3 class="text-xl font-bold text-slate-800 border-b border-slate-200 pb-4 mb-6">Audit Results</h3>
                
                @foreach($results as $result)
                    <div class="bg-white border rounded-2xl overflow-hidden shadow-lg shadow-slate-200/40 {{ $result->errorMessage ? 'border-red-200' : 'border-slate-200' }} transition-all hover:border-slate-300">
                        
                        <div class="px-6 py-5 {{ $result->errorMessage ? 'bg-red-50/80' : 'bg-slate-50/80' }} border-b {{ $result->errorMessage ? 'border-red-100' : 'border-slate-100' }}">
                            @if($result->ampUrl)
                                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-3">Comparison Result</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div class="flex items-center"><span class="font-semibold text-slate-400 w-12 shrink-0">LP:</span> <a href="{{ $result->lpUrl }}" class="text-indigo-600 hover:text-indigo-700 hover:underline break-all font-medium" target="_blank">{{ $result->lpUrl }}</a></div>
                                    <div class="flex items-center"><span class="font-semibold text-slate-400 w-12 shrink-0">AMP:</span> <a href="{{ $result->ampUrl }}" class="text-indigo-600 hover:text-indigo-700 hover:underline break-all font-medium" target="_blank">{{ $result->ampUrl }}</a></div>
                                </div>
                            @else
                                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-3">Single URL Result</h4>
                                <div class="flex items-center text-sm"><span class="font-semibold text-slate-400 w-12 shrink-0">URL:</span> <a href="{{ $result->lpUrl }}" class="text-indigo-600 hover:text-indigo-700 hover:underline break-all font-medium" target="_blank">{{ $result->lpUrl }}</a></div>
                            @endif

                            @if(!empty($result->redirectChain))
                                <div class="mt-4 pt-4 border-t border-slate-200/60 text-xs text-slate-500 flex items-center flex-wrap gap-2">
                                    <span class="font-semibold uppercase tracking-wider text-slate-400 text-[10px]">Redirects:</span>
                                    @foreach($result->redirectChain as $hop)
                                        <span class="bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">{{ $hop }}</span>
                                        @if(!$loop->last) <span class="text-slate-300">→</span> @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="p-0">
                            @if($result->errorMessage)
                                <div class="p-6 text-red-600 flex items-start font-medium bg-white">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>{{ $result->errorMessage }}</span>
                                </div>
                            @else
                                @if(empty($result->checks))
                                    <div class="p-8 text-slate-400 italic text-center bg-white">No rules were executed.</div>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm text-left">
                                            <thead class="bg-white text-slate-400 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                                                <tr>
                                                    <th class="px-6 py-4">Rule Name</th>
                                                    <th class="px-6 py-4">Status</th>
                                                    <th class="px-6 py-4">Details</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                                                @foreach($result->checks as $check)
                                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                                        <td class="px-6 py-4 font-semibold text-slate-800 w-1/4">
                                                            {{ $check->ruleName }}
                                                        </td>
                                                        <td class="px-6 py-4 w-32">
                                                            @if($check->status->value === 'passed')
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-200/60 shadow-sm">
                                                                    Passed
                                                                </span>
                                                            @elseif($check->status->value === 'warning')
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-600 border border-amber-200/60 shadow-sm">
                                                                    Warning
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-600 border border-red-200/60 shadow-sm">
                                                                    Error
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 text-slate-600">
                                                            <div class="mb-1 leading-relaxed">{{ $check->details }}</div>
                                                            @if(!empty($check->htmlSnippet))
                                                                <div class="mt-3 text-[11px] bg-slate-900 text-emerald-400 p-3.5 rounded-xl overflow-x-auto font-mono shadow-inner border border-slate-800">
                                                                    <pre><code>{{ $check->htmlSnippet }}</code></pre>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>
