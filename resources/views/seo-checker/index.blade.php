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

        <div class="bg-white/80 backdrop-blur-xl border border-white shadow-[0_20px_50px_-12px_rgba(0,0,0,0.05)] rounded-[2.5rem] p-8 md:p-12 mb-10 relative overflow-hidden">
            <!-- Decorative gradient orb -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <form action="{{ route('seo.process') }}" method="POST" @submit="syncTextareas(); submitting = true" class="relative z-10">
                @csrf
                <textarea name="lp_urls" id="lp_urls" class="hidden"></textarea>
                <textarea name="amp_urls" id="amp_urls" class="hidden"></textarea>

                <div class="space-y-4 mb-8">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex items-center gap-4 group">
                            <!-- Input Wrapper -->
                            <div class="flex-1 grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': hasCompareRule }">
                                <div>
                                    <input type="url" x-model="row.lp" placeholder="Landing Page URL (e.g. https://example.com)" required
                                        class="w-full bg-slate-100/50 border-none rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-blue-200 focus:bg-white transition-all shadow-inner placeholder:text-slate-400 text-slate-700 font-medium">
                                </div>
                                <div x-show="hasCompareRule" x-cloak>
                                    <input type="url" x-model="row.amp" placeholder="AMP URL (Optional)"
                                        class="w-full bg-slate-100/50 border-none rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-blue-200 focus:bg-white transition-all shadow-inner placeholder:text-slate-400 text-slate-700 font-medium">
                                </div>
                            </div>
                            <!-- Delete Button -->
                            <div class="w-12 flex justify-center">
                                <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                    class="rounded-full bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-500 transition-all p-3 opacity-0 group-hover:opacity-100 focus:opacity-100"
                                    title="Hapus baris">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-slate-100/60">
                    <div>
                        <button type="button" @click="addRow()" x-show="rows.length < 10"
                            class="border border-slate-200 text-slate-600 px-5 py-2.5 rounded-full hover:bg-slate-50 hover:scale-105 transition-transform duration-300 text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah URL
                        </button>
                    </div>

                    <button type="submit" :disabled="submitting"
                        class="bg-gradient-to-r from-blue-400 to-indigo-500 text-white font-semibold py-3.5 px-8 rounded-full shadow-md hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-300 flex items-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-md">
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
                                                            @if($check->status->value !== 'passed')
                                                                @php
                                                                    $isWarning = $check->status->value === 'warning';
                                                                    $bgClass = $isWarning ? 'bg-amber-50/50' : 'bg-red-50/50';
                                                                    $borderClass = $isWarning ? 'border-amber-100' : 'border-red-100';
                                                                @endphp
                                                                <div class="mb-3 space-y-2 text-sm {{ $bgClass }} p-4 rounded-xl border {{ $borderClass }}">
                                                                    @if($check->issue)
                                                                        <div><span class="font-bold text-slate-700 text-xs uppercase tracking-wider block mb-0.5">Masalah</span> <span class="text-slate-800">{{ $check->issue }}</span></div>
                                                                    @endif
                                                                    <div class="grid grid-cols-2 gap-4 pt-2">
                                                                        @if($check->expected)
                                                                            <div><span class="font-bold text-slate-700 text-xs uppercase tracking-wider block mb-0.5">Expected</span> <span class="text-slate-800">{{ $check->expected }}</span></div>
                                                                        @endif
                                                                        @if($check->actual)
                                                                            <div><span class="font-bold text-slate-700 text-xs uppercase tracking-wider block mb-0.5">Actual</span> <span class="text-slate-800">{{ $check->actual }}</span></div>
                                                                        @endif
                                                                    </div>
                                                                    @if($check->selector)
                                                                        <div class="pt-2">
                                                                            <span class="font-bold text-slate-700 text-xs uppercase tracking-wider block mb-0.5">Lokasi</span> 
                                                                            <code class="text-xs bg-white border border-slate-200 px-1.5 py-0.5 rounded text-indigo-600">{{ $check->selector }}</code>
                                                                            @if($check->attribute)
                                                                                <span class="text-xs text-slate-500"> Attribute: </span><code class="text-xs bg-white border border-slate-200 px-1.5 py-0.5 rounded text-indigo-600">{{ $check->attribute }}</code>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    @if($check->reason)
                                                                        <div class="pt-2"><span class="font-bold text-slate-700 text-xs uppercase tracking-wider block mb-0.5">Alasan</span> <span class="text-slate-600">{{ $check->reason }}</span></div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="mb-1 leading-relaxed text-sm">
                                                                    <span class="font-bold text-slate-700 text-[10px] uppercase tracking-wider block mb-1">Status: Sesuai Ekspektasi</span>
                                                                    @if($check->expected)
                                                                        <div class="text-xs text-slate-500">Expected: {{ $check->expected }}</div>
                                                                    @endif
                                                                    @if($check->actual)
                                                                        <div class="text-xs font-medium text-emerald-600 mt-0.5">Actual: {{ $check->actual }}</div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            
                                                            @if(!empty($check->htmlSnippet))
                                                                <div class="mt-3 text-[11px] bg-slate-900 text-emerald-400 p-3.5 rounded-xl overflow-hidden font-mono shadow-inner border border-slate-800">
                                                                    <pre class="whitespace-pre-wrap break-all overflow-hidden"><code>{{ $check->htmlSnippet }}</code></pre>
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
