<x-admin-layout>
    <x-slot name="title">Bank Rules SEO</x-slot>
    <x-slot name="header">Bank Rules SEO</x-slot>

    <!-- Main Container with Alpine component -->
    <div class="w-full max-w-9xl mx-auto pb-12" x-data="ruleBank()">

        <!-- Sticky Top Bar (Header & Search) -->
        <div class="sticky top-0 z-40 bg-[#f8fafc]/90 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-4 mb-8 -mx-4 sm:-mx-6 lg:-mx-8 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <!-- Title Area -->
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.rules.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        Bank Rules
                    </h1>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="w-full md:w-[400px] relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" x-ref="searchInput" x-model="searchQuery" placeholder="Cari rule, selector, atau ID..." class="block w-full pl-10 pr-10 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 sm:text-sm transition-colors shadow-sm">
                
                <!-- Clear Button -->
                <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''; $refs.searchInput.focus()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" style="display: none;">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Alerts -->
            @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center">
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            @endif
            
            @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center">
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Categories -->
                <div class="w-full lg:w-64 shrink-0">
                    <!-- Adjusted top value to account for the sticky header -->
                    <div class="sticky top-[5.5rem] bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 px-3">Kategori</h3>
                        
                        <nav class="space-y-1 h-[60vh] overflow-y-auto pr-1 custom-scrollbar">
                            <button @click="activeCategory = 'all'" :class="{'bg-blue-50 text-blue-700 font-semibold': activeCategory === 'all', 'text-slate-600 hover:bg-slate-50': activeCategory !== 'all'}" class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg transition-colors">
                                <span>Semua Rule</span>
                                <span :class="{'bg-blue-100 text-blue-600': activeCategory === 'all', 'bg-slate-100 text-slate-500': activeCategory !== 'all'}" class="px-2 py-0.5 rounded-full text-xs">
                                    {{ array_sum(array_map('count', $categorizedRules)) }}
                                </span>
                            </button>
                            
                            @foreach(array_keys($categorizedRules) as $cat)
                                <button @click="activeCategory = '{{ $cat }}'" :class="{'bg-blue-50 text-blue-700 font-semibold': activeCategory === '{{ $cat }}', 'text-slate-600 hover:bg-slate-50': activeCategory !== '{{ $cat }}'}" class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg transition-colors text-left">
                                    <span class="uppercase">{{ $cat }}</span>
                                    <span :class="{'bg-blue-100 text-blue-600': activeCategory === '{{ $cat }}', 'bg-slate-100 text-slate-500': activeCategory !== '{{ $cat }}'}" class="px-2 py-0.5 rounded-full text-xs">
                                        {{ count($categorizedRules[$cat]) }}
                                    </span>
                                </button>
                            @endforeach
                        </nav>
                    </div>
                </div>

                <!-- Rules List -->
                <div class="flex-1 min-w-0">
                    @foreach($categorizedRules as $category => $rules)
                        <div x-show="showCategory('{{ $category }}')" class="mb-10">
                            
                            <h2 class="text-lg font-bold text-slate-800 uppercase tracking-tight mb-4 flex items-center gap-3">
                                {{ $category }}
                                <div class="h-px bg-slate-200 flex-1"></div>
                            </h2>

                            <div class="grid grid-cols-1 gap-4">
                                @foreach($rules as $rule)
                                    @php
                                        $isInstalled = in_array($rule['code'], $installedCodes);
                                        $safeName = htmlspecialchars(strtolower($rule['name']), ENT_QUOTES);
                                        $safeCode = htmlspecialchars(strtolower($rule['code']), ENT_QUOTES);
                                        $safeSelector = htmlspecialchars(strtolower($rule['selector'] ?? ''), ENT_QUOTES);
                                    @endphp
                                    <div x-show="matchesSearch('{{ $safeName }}', '{{ $safeCode }}', '{{ $safeSelector }}')" 
                                         class="bg-white rounded-2xl border {{ $isInstalled ? 'border-green-200 bg-green-50/30' : 'border-slate-200' }} p-5 shadow-sm transition-shadow hover:shadow-md flex flex-col md:flex-row gap-5">
                                        
                                        <!-- Content Left -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Badges -->
                                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                                <span class="px-2 py-0.5 text-[10px] font-mono font-medium rounded bg-slate-100 text-slate-600 border border-slate-200">
                                                    {{ $rule['code'] }}
                                                </span>
                                                
                                                @if($rule['severity'] === 'error')
                                                    <span class="px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase rounded bg-red-100 text-red-700 border border-red-200">
                                                        Error
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase rounded bg-orange-100 text-orange-700 border border-orange-200">
                                                        Warning
                                                    </span>
                                                @endif
                                                
                                                <span class="px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase rounded bg-blue-50 text-blue-600 border border-blue-100">
                                                    {{ str_replace('_', ' ', $rule['type']) }}
                                                </span>
                                            </div>
                                            
                                            <!-- Title -->
                                            <h3 class="text-base font-bold text-slate-900 mb-3">
                                                {{ $rule['name'] }}
                                            </h3>
                                            
                                            <!-- Code Snippet Box (Minimal Light Mode) -->
                                            <div class="mb-4 bg-slate-50 rounded-lg p-3 border border-slate-200/60 font-mono text-xs text-slate-600">
                                                <span class="text-slate-400">Selector:</span> <span class="text-blue-600 font-semibold">{{ $rule['selector'] ?? '-' }}</span>
                                                @if(!empty($rule['attribute']))
                                                    <br><span class="text-slate-400">Attribute:</span> <span class="text-purple-600 font-semibold">{{ $rule['attribute'] }}</span>
                                                @endif
                                            </div>

                                            <!-- Descriptions -->
                                            @if(isset($rule['reason_template']) || isset($rule['recommendation']))
                                                <div class="space-y-3">
                                                    @if(isset($rule['reason_template']))
                                                        <div>
                                                            <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kenapa Penting?</h4>
                                                            <p class="text-sm text-slate-700 leading-relaxed">{{ $rule['reason_template'] }}</p>
                                                        </div>
                                                    @endif
                                                    @if(isset($rule['recommendation']))
                                                        <div>
                                                            <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cara Memperbaiki</h4>
                                                            <p class="text-sm text-slate-700 leading-relaxed">{{ $rule['recommendation'] }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Action Right -->
                                        <div class="md:w-48 shrink-0 flex flex-col justify-center border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-5">
                                            @if($isInstalled)
                                                <div class="flex items-center justify-center w-full py-2.5 px-4 bg-green-50 text-green-700 font-medium text-sm rounded-lg border border-green-200 text-center">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                    Telah Terpasang
                                                </div>
                                            @else
                                                <form action="{{ route('admin.rules.bank.install') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="code" value="{{ $rule['code'] }}">
                                                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-colors shadow-sm">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                        Pasang Rule
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Empty State -->
                    <div x-show="isEmpty()" class="py-12 text-center bg-white rounded-2xl border border-slate-200" style="display: none;">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <h3 class="text-lg font-medium text-slate-900">Tidak ada rule ditemukan</h3>
                        <p class="text-slate-500 mt-1 text-sm">Coba gunakan kata kunci pencarian yang lain.</p>
                        <button @click="searchQuery = ''; $refs.searchInput.focus()" class="mt-4 text-blue-600 hover:text-blue-700 text-sm font-medium">Clear Pencarian</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ruleBank', () => ({
                searchQuery: '',
                activeCategory: 'all',
                
                init() {
                    // Keyboard shortcuts for search focus
                    window.addEventListener('keydown', (e) => {
                        if (e.key === '/' || (e.key === 'k' && (e.ctrlKey || e.metaKey))) {
                            if (document.activeElement !== this.$refs.searchInput) {
                                e.preventDefault();
                                this.$refs.searchInput.focus();
                            }
                        }
                    });
                },

                matchesSearch(name, code, selector) {
                    if (this.searchQuery.trim() === '') return true;
                    const terms = this.searchQuery.toLowerCase().split(' ').filter(t => t.trim() !== '');
                    const text = (name + ' ' + code + ' ' + selector).toLowerCase();
                    return terms.every(term => text.includes(term));
                },

                showCategory(category) {
                    if (this.activeCategory !== 'all' && this.activeCategory !== category) return false;
                    
                    // If no search query, show category normally
                    if (this.searchQuery.trim() === '') return true;
                    
                    // If searching, check if AT LEAST ONE rule in this category matches
                    // We can do this cleanly by getting the DOM elements, or just returning true
                    // and letting the empty state handle "no results overall".
                    // For performance, we'll return true if it matches the activeCategory,
                    // the individual rules inside will hide themselves.
                    return true;
                },

                isEmpty() {
                    if (this.searchQuery.trim() === '') return false;
                    
                    // Very simple check: are there any visible rule cards?
                    // We use Alpine's nextTick effectively by relying on DOM inspection for the empty state
                    // This is a common pattern when filtering large lists in Alpine.
                    const visibleCards = document.querySelectorAll('.grid > div[style!="display: none;"]');
                    return visibleCards.length === 0;
                }
            }));
        });
    </script>
</x-admin-layout>