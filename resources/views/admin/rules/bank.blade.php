<x-admin-layout>
    <x-slot name="title">Library Rules AI</x-slot>
    <x-slot name="header">Library Rules AI</x-slot>

    <!-- Main Container -->
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="{ searchQuery: '', activeCategory: 'all' }" @keydown.window.prevent.slash="$refs.searchInput.focus()" @keydown.window.prevent.ctrl.k="$refs.searchInput.focus()" @keydown.window.prevent.meta.k="$refs.searchInput.focus()">

        <!-- Hero Header -->
        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 text-white mb-8 shadow-2xl">
            <!-- Decorative AI Gradients -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
                <div class="absolute -top-1/2 -right-1/4 w-[1000px] h-[1000px] rounded-full bg-gradient-to-b from-indigo-500/20 to-purple-600/10 blur-3xl transform rotate-12"></div>
                <div class="absolute -bottom-1/2 -left-1/4 w-[800px] h-[800px] rounded-full bg-gradient-to-t from-blue-500/20 to-teal-400/10 blur-3xl transform -rotate-12"></div>
            </div>

            <div class="relative z-10 px-8 py-12 sm:px-12 sm:py-16 flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                <div class="max-w-2xl">
                    <div class="flex items-center gap-2 mb-4">
                        <a href="{{ route('admin.rules.index') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all backdrop-blur-sm mr-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        </a>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold tracking-wider uppercase backdrop-blur-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            SEO Engine AI
                        </span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4">
                        Rule Library<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">.AI</span>
                    </h1>
                    <p class="text-lg text-slate-300 leading-relaxed font-light max-w-xl">
                        Pustaka cerdas aturan SEO untuk sistem Anda. Jelajahi, pelajari fungsinya, dan instal aturan standar industri hanya dengan satu klik.
                    </p>
                </div>

                <!-- Search Bar inside Header -->
                <div class="w-full md:w-96 shrink-0 relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-500"></div>
                    <div class="relative flex items-center bg-slate-800/80 backdrop-blur-md rounded-xl border border-white/10 shadow-xl overflow-hidden focus-within:border-indigo-500/50 focus-within:ring-1 focus-within:ring-indigo-500/50 transition-all">
                        <div class="pl-4 pr-3 py-4 flex items-center justify-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" x-ref="searchInput" x-model.debounce.300ms="searchQuery" placeholder="Cari rule, selector, atau ID..." class="w-full bg-transparent border-none text-white placeholder-slate-400 focus:ring-0 py-4 pl-0 pr-12 text-sm">
                        
                        <!-- Clear Button -->
                        <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''; $refs.searchInput.focus()" class="absolute right-12 text-slate-400 hover:text-white transition-colors" style="display: none;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        
                        <!-- Keyboard Hint -->
                        <div class="absolute right-4 hidden sm:flex items-center pointer-events-none">
                            <kbd class="px-2.5 py-1 text-xs font-mono font-semibold text-slate-400 bg-slate-700/50 border border-slate-600 rounded-md">
                                /
                            </kbd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-2xl flex items-center shadow-sm">
            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-emerald-500/20 mr-4">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <span class="font-semibold text-sm">{{ session('success') }}</span>
        </div>
        @endif
        
        @if(session('error'))
        <div class="mb-8 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-700 dark:text-rose-400 rounded-2xl flex items-center shadow-sm">
            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-rose-500/20 mr-4">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <span class="font-semibold text-sm">{{ session('error') }}</span>
        </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <!-- Sidebar Categories (Glassmorphism style) -->
            <div class="w-full lg:w-72 shrink-0">
                <div class="sticky top-8 bg-white/60 backdrop-blur-xl p-6 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/50">
                    <div class="flex items-center gap-3 mb-6 px-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Kategori Rule</h3>
                    </div>
                    
                    <nav class="space-y-1.5 h-[65vh] overflow-y-auto pr-2 custom-scrollbar">
                        <button @click="activeCategory = 'all'" :class="{'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md shadow-indigo-500/20 translate-x-1': activeCategory === 'all', 'text-slate-600 hover:bg-slate-100 hover:text-slate-900': activeCategory !== 'all'}" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300">
                            <span>Semua Rule</span>
                            <span :class="{'bg-white/20 text-white': activeCategory === 'all', 'bg-slate-200 text-slate-500': activeCategory !== 'all'}" class="px-2 py-0.5 rounded-full text-xs">
                                {{ array_sum(array_map('count', $categorizedRules)) }}
                            </span>
                        </button>
                        
                        @foreach(array_keys($categorizedRules) as $cat)
                            <button @click="activeCategory = '{{ $cat }}'" :class="{'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md shadow-indigo-500/20 translate-x-1': activeCategory === '{{ $cat }}', 'text-slate-600 hover:bg-slate-100 hover:text-slate-900': activeCategory !== '{{ $cat }}'}" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 text-left">
                                <span class="uppercase tracking-wide">{{ $cat }}</span>
                                <span :class="{'bg-white/20 text-white': activeCategory === '{{ $cat }}', 'bg-slate-200 text-slate-500': activeCategory !== '{{ $cat }}'}" class="px-2 py-0.5 rounded-full text-xs">
                                    {{ count($categorizedRules[$cat]) }}
                                </span>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Rules List -->
            <div class="flex-1 space-y-10">
                @foreach($categorizedRules as $category => $rules)
                    <div x-show="activeCategory === 'all' || activeCategory === '{{ $category }}'" 
                         class="category-group"
                         data-category="{{ $category }}">
                         
                        <div class="mb-6 flex items-center gap-4">
                            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ $category }}</h2>
                            <div class="flex-1 h-px bg-gradient-to-r from-slate-200 to-transparent"></div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            @foreach($rules as $rule)
                                @php
                                    $isInstalled = in_array($rule['code'], $installedCodes);
                                @endphp
                                <div class="group bg-white rounded-3xl border {{ $isInstalled ? 'border-emerald-200/60 shadow-[0_4px_20px_rgba(16,185,129,0.08)]' : 'border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.08)] hover:border-indigo-100' }} p-6 transition-all duration-400 hover:-translate-y-1 relative rule-card flex flex-col h-full overflow-hidden"
                                     data-name="{{ strtolower($rule['name']) }}"
                                     data-code="{{ strtolower($rule['code']) }}">
                                    
                                    @if($isInstalled)
                                        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-100 to-transparent opacity-50 rounded-tr-3xl pointer-events-none"></div>
                                    @endif

                                    <div class="flex-1">
                                        <!-- Header Badges -->
                                        <div class="flex flex-wrap items-center gap-2 mb-4">
                                            <span class="px-2.5 py-1 text-[10px] font-mono font-bold tracking-wider rounded-lg {{ $isInstalled ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-50 text-slate-500 border border-slate-200' }}">
                                                {{ $rule['code'] }}
                                            </span>
                                            
                                            @if($rule['severity'] === 'error')
                                                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase rounded-lg bg-rose-50 text-rose-600 border border-rose-100 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Error
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Warning
                                                </span>
                                            @endif
                                            
                                            <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100">
                                                {{ str_replace('_', ' ', $rule['type']) }}
                                            </span>
                                        </div>
                                        
                                        <!-- Title -->
                                        <h3 class="text-[1.05rem] leading-snug font-bold text-slate-800 mb-4 group-hover:text-indigo-600 transition-colors">
                                            {{ $rule['name'] }}
                                        </h3>
                                        
                                        <!-- Code Snippet Box (Modern dark mode style) -->
                                        <div class="mb-5 bg-slate-900 rounded-xl p-3.5 shadow-inner border border-slate-800">
                                            <div class="flex items-center gap-2 mb-2 border-b border-slate-700/50 pb-2">
                                                <div class="flex gap-1.5">
                                                    <div class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></div>
                                                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></div>
                                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></div>
                                                </div>
                                                <span class="text-[10px] text-slate-500 font-medium uppercase tracking-wider ml-1">Target Selection</span>
                                            </div>
                                            <div class="font-mono text-xs leading-relaxed text-slate-300">
                                                <span class="text-slate-500">Selector:</span> <span class="text-teal-400 search-selector">{{ $rule['selector'] ?? '-' }}</span>
                                                @if(!empty($rule['attribute']))
                                                    <br><span class="text-slate-500">Attribute:</span> <span class="text-indigo-300">{{ $rule['attribute'] }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Descriptions -->
                                        @if(isset($rule['reason_template']) || isset($rule['recommendation']))
                                            <div class="space-y-4 mb-6">
                                                @if(isset($rule['reason_template']))
                                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                                                        <h4 class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            Tujuan Rule
                                                        </h4>
                                                        <p class="text-sm text-slate-600 leading-relaxed">{{ $rule['reason_template'] }}</p>
                                                    </div>
                                                @endif
                                                @if(isset($rule['recommendation']))
                                                    <div class="bg-emerald-50/50 rounded-xl p-4 border border-emerald-100/50">
                                                        <h4 class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            Cara Memperbaiki
                                                        </h4>
                                                        <p class="text-sm text-slate-600 leading-relaxed">{{ $rule['recommendation'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Action Button footer -->
                                    <div class="mt-auto pt-5 border-t border-slate-100">
                                        @if($isInstalled)
                                            <div class="flex items-center justify-center w-full py-2.5 px-4 bg-emerald-50 text-emerald-700 font-semibold text-sm rounded-xl border border-emerald-100">
                                                <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                Rule Telah Terpasang
                                            </div>
                                        @else
                                            <form action="{{ route('admin.rules.bank.install') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="code" value="{{ $rule['code'] }}">
                                                <button type="submit" class="w-full group/btn relative flex items-center justify-center gap-2 py-3 px-4 bg-slate-900 hover:bg-indigo-600 text-white font-medium text-sm rounded-xl transition-all duration-300 shadow-md hover:shadow-xl hover:shadow-indigo-500/20 overflow-hidden">
                                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover/btn:animate-[shimmer_1.5s_infinite]"></div>
                                                    <svg class="w-4 h-4 transition-transform group-hover/btn:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                    <span class="relative z-10">Pasang Rule</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Empty State for Search -->
                <div id="empty-state" class="hidden py-16 text-center bg-white/50 backdrop-blur-sm rounded-[2rem] border border-slate-200 border-dashed">
                    <div class="w-20 h-20 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Rule tidak ditemukan</h3>
                    <p class="text-slate-500 mt-2 max-w-sm mx-auto">Tidak ada rule yang cocok dengan kata kunci pencarian Anda. Coba gunakan kata kunci yang lebih umum.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Custom Keyframes -->
    <style>
        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background-color: #94a3b8;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                const root = document.querySelector('[x-data]');
                if (!root || !root.__x) return;
                const query = (root.__x.$data.searchQuery || '').toLowerCase();
                const activeCat = root.__x.$data.activeCategory || 'all';
                
                let hasVisible = false;
                const terms = query.split(' ').filter(t => t.trim() !== '');
                
                document.querySelectorAll('.category-group').forEach(group => {
                    const cat = group.getAttribute('data-category');
                    let groupVisible = false;
                    
                    if (activeCat === 'all' || activeCat === cat) {
                        group.querySelectorAll('.rule-card').forEach(card => {
                            const name = card.getAttribute('data-name');
                            const code = card.getAttribute('data-code');
                            const selectorNode = card.querySelector('.search-selector');
                            const selector = selectorNode ? selectorNode.textContent.toLowerCase() : '';
                            
                            const textToSearch = name + " " + code + " " + selector;
                            const isMatch = terms.length === 0 || terms.every(term => textToSearch.includes(term));
                            
                            if (isMatch) {
                                card.style.display = 'flex';
                                groupVisible = true;
                                hasVisible = true;
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    } else {
                        group.querySelectorAll('.rule-card').forEach(card => card.style.display = 'none');
                    }
                    
                    group.style.display = groupVisible ? 'block' : 'none';
                });
                
                document.getElementById('empty-state').style.display = hasVisible ? 'none' : 'block';
            });
        });
    </script>
</x-admin-layout>