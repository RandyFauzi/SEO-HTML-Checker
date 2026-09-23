<x-admin-layout>
    <x-slot name="title">SEO Checker</x-slot>
    <x-slot name="header">SEO HTML Checker</x-slot>

@php
    $initialRows = [];
    $oldLp = old('lp_urls');
    if ($oldLp) {
        $lpLines = array_map('trim', explode("\n", $oldLp));
        $ampLines = array_map('trim', explode("\n", old('amp_urls') ?? ''));
        foreach ($lpLines as $i => $lp) {
            if ($lp) {
                $initialRows[] = [
                    'lp' => $lp,
                    'amp' => $ampLines[$i] ?? ''
                ];
            }
        }
    }
    if (empty($initialRows)) {
        $initialRows[] = ['lp' => '', 'amp' => ''];
    }
@endphp

    <div class="max-w-6xl mx-auto" 
         x-data="{ 
            hasCompareRule: {{ $hasCompareRule ? 'true' : 'false' }},
            rows: {{ json_encode($initialRows) }},
            submitting: false,
            addRow() {
                if (this.rows.length < 10) {
                    this.rows.push({ lp: '', amp: '' });
                }
            },
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            syncTextareas() {
                document.getElementById('lp_urls').value = this.rows.map(r => r.lp).join('\n');
                document.getElementById('amp_urls').value = this.rows.map(r => r.amp).join('\n');
            }
         }">
        
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

        <div class="bg-white/80 backdrop-blur-xl border border-white shadow-[0_20px_50px_-12px_rgba(0,0,0,0.05)] rounded-3xl md:rounded-[2.5rem] p-5 sm:p-8 md:p-12 mb-10 relative overflow-hidden">
            <!-- Decorative gradient orb -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <form action="{{ route('seo.process') }}" method="POST" @submit="syncTextareas(); submitting = true" class="relative z-10">
                @csrf
                <textarea name="lp_urls" id="lp_urls" class="hidden"></textarea>
                <textarea name="amp_urls" id="amp_urls" class="hidden"></textarea>

                <div class="space-y-4 mb-8">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex items-start sm:items-center gap-3 sm:gap-4 group">
                            <!-- Number Indicator -->
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mt-3 sm:mt-0 shrink-0 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 font-bold text-xs sm:text-sm shadow-inner border border-slate-200">
                                <span x-text="index + 1"></span>
                            </div>

                            <!-- Input Wrapper -->
                            <div class="flex-1 grid grid-cols-1 gap-3 sm:gap-4" :class="{ 'md:grid-cols-2': hasCompareRule }">
                                <div>
                                    <input type="url" x-model="row.lp" placeholder="Landing Page URL (e.g. https://example.com)" required
                                        class="w-full bg-slate-100/50 border-none rounded-xl sm:rounded-2xl px-4 py-3 sm:px-6 sm:py-4 text-sm focus:ring-2 focus:ring-blue-200 focus:bg-white transition-all shadow-inner placeholder:text-slate-400 text-slate-700 font-medium">
                                </div>
                                <div x-show="hasCompareRule" x-cloak>
                                    <input type="url" x-model="row.amp" placeholder="AMP URL (Optional)"
                                        class="w-full bg-slate-100/50 border-none rounded-xl sm:rounded-2xl px-4 py-3 sm:px-6 sm:py-4 text-sm focus:ring-2 focus:ring-blue-200 focus:bg-white transition-all shadow-inner placeholder:text-slate-400 text-slate-700 font-medium">
                                </div>
                            </div>
                            <!-- Delete Button -->
                            <div class="w-8 sm:w-12 mt-2 sm:mt-0 flex justify-center">
                                <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                    class="rounded-full bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-500 transition-all p-2 sm:p-3 sm:opacity-0 group-hover:opacity-100 focus:opacity-100"
                                    title="Hapus baris">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-slate-100/60">
                    <div class="w-full sm:w-auto">
                        <button type="button" @click="addRow()" x-show="rows.length < 10"
                            class="w-full sm:w-auto justify-center border border-slate-200 text-slate-600 px-5 py-3 sm:py-2.5 rounded-full hover:bg-slate-50 hover:scale-105 transition-transform duration-300 text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah URL
                        </button>
                    </div>

                    <button type="submit" :disabled="submitting"
                        class="w-full sm:w-auto justify-center bg-gradient-to-r from-blue-400 to-indigo-500 text-white font-semibold py-3 sm:py-3.5 px-8 rounded-full shadow-md hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-300 flex items-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-md">
                        <template x-if="!submitting">
                            <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                        <template x-if="submitting">
                            <svg class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="submitting ? 'Processing...' : 'Run Checker'"></span>
                    </button>
                </div>
            </form>
        </div>

        @if(isset($results))
            <div class="space-y-12">
                @foreach($results as $result)
                    <div class="bg-slate-50 border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
                        
                        <!-- Header (URL & Basic Info) -->
                        <div class="bg-white border-b border-slate-200 p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div>
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Check Page</h3>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    </div>
                                    <a href="{{ $result->lpUrl }}" class="text-xl font-bold text-slate-800 hover:text-indigo-600 transition-colors break-all" target="_blank">{{ $result->lpUrl }}</a>
                                </div>
                                @if($result->ampUrl)
                                    <div class="mt-3 flex items-center gap-2 text-sm text-slate-500 pl-13">
                                        <span class="font-semibold px-2 py-0.5 bg-slate-100 rounded text-xs">AMP</span>
                                        <a href="{{ $result->ampUrl }}" class="hover:text-indigo-600 break-all" target="_blank">{{ $result->ampUrl }}</a>
                                    </div>
                                @endif
                                
                                @if(!empty($result->redirectChain))
                                    <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 flex items-center flex-wrap gap-2">
                                        <span class="font-semibold uppercase tracking-wider text-slate-400 text-[10px]">Redirects:</span>
                                        @foreach($result->redirectChain as $hop)
                                            <span class="bg-slate-100 px-2 py-1 rounded-md">{{ $hop }}</span>
                                            @if(!$loop->last) <span class="text-slate-300">→</span> @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(!$result->errorMessage)
                                @php
                                    $totalChecks = count($result->checks);
                                    $passedChecks = collect($result->checks)->filter(fn($c) => $c->status->value === 'passed')->count();
                                    $score = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;
                                    
                                    $scoreColor = 'text-emerald-500';
                                    $scoreBg = 'border-emerald-500';
                                    if($score < 80) { $scoreColor = 'text-amber-500'; $scoreBg = 'border-amber-500'; }
                                    if($score < 50) { $scoreColor = 'text-red-500'; $scoreBg = 'border-red-500'; }
                                @endphp
                                <div class="flex items-center gap-6 md:border-l md:border-slate-100 md:pl-8">
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-slate-700">On-page score</div>
                                        <div class="text-xs font-medium text-slate-400 mt-0.5">Based on {{ $totalChecks }} checks</div>
                                    </div>
                                    <div class="relative w-20 h-20 flex items-center justify-center rounded-full border-4 border-slate-100 shadow-inner bg-slate-50">
                                        <!-- Fake circular progress using border coloring for visual effect -->
                                        <div class="absolute inset-0 rounded-full border-4 {{ $scoreBg }} opacity-80" style="clip-path: polygon(0 0, 100% 0, 100% {{ $score }}%, 0 {{ $score }}%);"></div>
                                        <span class="relative text-2xl font-black {{ $scoreColor }}">{{ $score }}<span class="text-sm">%</span></span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Body -->
                        <div class="p-6 md:p-10">
                            @if($result->errorMessage)
                                <!-- Fetch Error -->
                                <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-red-700 flex gap-4 shadow-sm">
                                    <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <div>
                                        <h4 class="font-bold text-lg mb-1">Failed to analyze page</h4>
                                        <p class="text-sm opacity-90">{{ $result->errorMessage }}</p>
                                    </div>
                                </div>
                            @else
                                <!-- TO-DO LIST (Tasks sorted by priority) -->
                                @php
                                    $issues = collect($result->checks)->filter(fn($c) => $c->status->value !== 'passed')->sortByDesc(fn($c) => $c->status->value === 'error' ? 2 : 1);
                                @endphp
                                
                                @if($issues->count() > 0)
                                    <div class="mb-14">
                                        <div class="flex items-center gap-3 mb-5">
                                            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">To-Do List</h3>
                                            <span class="bg-slate-200 text-slate-600 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $issues->count() }} Tasks</span>
                                        </div>
                                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                                            <table class="min-w-full text-sm text-left">
                                                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                                                    <tr>
                                                        <th class="px-6 py-4">To-Do Task</th>
                                                        <th class="px-6 py-4 w-32 text-center">Importance</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($issues as $issue)
                                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                                            <td class="px-6 py-4 font-semibold text-slate-700">
                                                                {{ $issue->issue ?? $issue->ruleName }}
                                                            </td>
                                                            <td class="px-6 py-4 text-center">
                                                                @if($issue->status->value === 'error')
                                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded bg-red-500 text-white text-[10px] font-bold uppercase tracking-wider shadow-sm w-full">Error</span>
                                                                @else
                                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded bg-amber-400 text-white text-[10px] font-bold uppercase tracking-wider shadow-sm w-full">Warning</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <!-- CATEGORIZED CHECKS -->
                                @php
                                    // Group by category, default to 'Others' if null
                                    $grouped = collect($result->checks)->groupBy(fn($c) => $c->category ?: 'Others');
                                @endphp

                                <div class="space-y-12">
                                    @foreach($grouped as $category => $checks)
                                        @php
                                            $catTotal = count($checks);
                                            $catPassed = collect($checks)->filter(fn($c) => $c->status->value === 'passed')->count();
                                            $catScore = $catTotal > 0 ? round(($catPassed / $catTotal) * 100) : 0;
                                        @endphp
                                        <div class="bg-slate-50">
                                            <!-- Category Header -->
                                            <div class="flex items-center justify-between mb-5 pb-3 border-b-2 border-slate-200/80">
                                                <h3 class="text-xl font-bold text-slate-800 capitalize">{{ $category }}</h3>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-24 md:w-48 h-2.5 bg-slate-200 rounded-full overflow-hidden shadow-inner">
                                                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $catScore }}%"></div>
                                                    </div>
                                                    <span class="text-sm font-black text-slate-700 w-10 text-right">{{ $catScore }}%</span>
                                                </div>
                                            </div>

                                            <!-- Rule Cards -->
                                            <div class="space-y-4">
                                                @foreach($checks as $check)
                                                    @php
                                                        $isPassed = $check->status->value === 'passed';
                                                        $isWarning = $check->status->value === 'warning';
                                                        
                                                        $cardBorder = $isPassed ? 'border-l-emerald-500' : ($isWarning ? 'border-l-amber-400' : 'border-l-red-500');
                                                        $iconColor = $isPassed ? 'text-emerald-500' : ($isWarning ? 'text-amber-500' : 'text-red-500');
                                                        $badgeClass = $isPassed ? 'bg-emerald-100 text-emerald-700' : ($isWarning ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                                                    @endphp
                                                    <div class="bg-white border border-slate-200 border-l-[6px] {{ $cardBorder }} rounded-xl shadow-sm overflow-hidden transition-all hover:shadow-md hover:border-slate-300">
                                                        <!-- Card Header -->
                                                        <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                            <div class="flex items-center gap-3">
                                                                <h4 class="font-bold text-slate-800">{{ $check->ruleName }}</h4>
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $badgeClass }}">{{ $check->status->value }}</span>
                                                            </div>
                                                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                                                                {{ $isPassed ? 'Good' : ($isWarning ? 'Important' : 'Very Important') }}
                                                            </div>
                                                        </div>

                                                        <!-- Card Body -->
                                                        <div class="p-6">
                                                            <div class="flex items-start gap-4">
                                                                <div class="mt-0.5 {{ $iconColor }}">
                                                                    @if($isPassed)
                                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                                    @elseif($isWarning)
                                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                                    @else
                                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                                    @endif
                                                                </div>
                                                                
                                                                <div class="flex-1 space-y-4">
                                                                    <!-- The primary message -->
                                                                    <div class="text-sm font-medium text-slate-700">
                                                                        @if($isPassed)
                                                                            The {{ strtolower($check->ruleName) }} is perfect.
                                                                            @if($check->actual) <span class="font-bold text-emerald-600 block mt-1">{{ $check->actual }}</span> @endif
                                                                        @else
                                                                            {{ $check->issue ?? 'Issue detected with this element.' }}
                                                                        @endif
                                                                    </div>

                                                                    <!-- Diagnostic Data (Only for warnings/errors) -->
                                                                    @if(!$isPassed && ($check->expected || $check->actual || $check->reason || $check->selector))
                                                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 text-sm grid grid-cols-1 md:grid-cols-2 gap-5 shadow-inner">
                                                                            @if($check->expected)
                                                                                <div><span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Expected</span> <span class="text-slate-800 font-medium">{{ $check->expected }}</span></div>
                                                                            @endif
                                                                            @if($check->actual)
                                                                                <div><span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Actual</span> <span class="text-slate-800 font-medium">{{ $check->actual }}</span></div>
                                                                            @endif
                                                                            @if($check->selector)
                                                                                <div>
                                                                                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Location</span> 
                                                                                    <code class="text-xs bg-white border border-slate-200 px-2 py-1 rounded text-indigo-600 shadow-sm">{{ $check->selector }}</code>
                                                                                    @if($check->attribute)
                                                                                        <span class="text-xs text-slate-400 mx-1">attr:</span><code class="text-xs bg-white border border-slate-200 px-2 py-1 rounded text-indigo-600 shadow-sm">{{ $check->attribute }}</code>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            @if($check->reason)
                                                                                <div class="md:col-span-2"><span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Reason</span> <span class="text-slate-600">{{ $check->reason }}</span></div>
                                                                            @endif
                                                                        </div>
                                                                    @endif

                                                                    <!-- HTML Snippet Evidence -->
                                                                    @if(!empty($check->htmlSnippet))
                                                                        <div class="mt-4 text-[11px] bg-slate-900 text-emerald-400 p-4 rounded-xl overflow-hidden font-mono shadow-inner border border-slate-800">
                                                                            <pre class="whitespace-pre-wrap break-all overflow-hidden leading-relaxed"><code>{{ $check->htmlSnippet }}</code></pre>
                                                                        </div>
                                                                    @endif

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>
