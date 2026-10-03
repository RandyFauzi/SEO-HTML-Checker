<x-admin-layout>

    <x-slot name="title">Bank Rules SEO</x-slot>

    <x-slot name="header">Bank Rules SEO</x-slot>
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="{ searchQuery: '', activeCategory: 'all' }">

    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0 flex items-center">
            <a href="{{ route('admin.rules.index') }}" class="mr-4 text-slate-400 hover:text-slate-500 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold flex items-center">
                    <svg class="w-8 h-8 text-amber-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    Bank Rules SEO
                </h1>
                <p class="text-sm text-slate-500 mt-1">Pustaka lengkap aturan SEO. Jelajahi dan gunakan aturan yang sesuai untuk kebutuhan sistem Anda.</p>
            </div>
        </div>
        
        <div class="relative w-full sm:w-72">
            <input type="text" x-model="searchQuery" placeholder="Cari rule atau selector..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-lg flex items-center border border-emerald-200">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-lg flex items-center border border-rose-200">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Categories -->
        <div class="w-full md:w-64 shrink-0">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 sticky top-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-2">Kategori</h3>
                <nav class="space-y-1">
                    <button @click="activeCategory = 'all'" :class="{'bg-amber-50 text-amber-600 font-medium': activeCategory === 'all', 'text-slate-600 hover:bg-slate-50': activeCategory !== 'all'}" class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors">
                        Semua Rule
                    </button>
                    @foreach(array_keys($categorizedRules) as $cat)
                        <button @click="activeCategory = '{{ $cat }}'" :class="{'bg-amber-50 text-amber-600 font-medium': activeCategory === '{{ $cat }}', 'text-slate-600 hover:bg-slate-50': activeCategory !== '{{ $cat }}'}" class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors text-left uppercase">
                            {{ $cat }} ({{ count($categorizedRules[$cat]) }})
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Rules List -->
        <div class="flex-1 space-y-6">
            @foreach($categorizedRules as $category => $rules)
                <div x-show="activeCategory === 'all' || activeCategory === '{{ $category }}'" 
                     class="category-group"
                     data-category="{{ $category }}">
                     
                    <div class="mb-4 flex items-center space-x-2">
                        <h2 class="text-lg font-bold text-slate-800 uppercase">{{ $category }}</h2>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    <div class="grid gap-4">
                        @foreach($rules as $rule)
                            @php
                                $isInstalled = in_array($rule['code'], $installedCodes);
                            @endphp
                            <div class="bg-white p-5 rounded-xl border {{ $isInstalled ? 'border-emerald-200 bg-emerald-50/20' : 'border-slate-200' }} shadow-sm hover:shadow-md transition-shadow relative rule-card"
                                 data-name="{{ strtolower($rule['name']) }}"
                                 data-code="{{ strtolower($rule['code']) }}">
                                
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 text-xs font-mono font-medium rounded {{ $isInstalled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $rule['code'] }}
                                            </span>
                                            @if($rule['severity'] === 'error')
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-rose-100 text-rose-700">Error</span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Warning</span>
                                            @endif
                                            
                                            <span class="px-2 py-0.5 text-xs font-medium rounded border border-slate-200 text-slate-500 uppercase">
                                                {{ str_replace('_', ' ', $rule['type']) }}
                                            </span>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-800">{{ $rule['name'] }}</h3>
                                        
                                        <div class="mt-3 text-sm text-slate-600 font-mono bg-slate-50 p-2 rounded border border-slate-100">
                                            Selector: <span class="text-pink-600">{{ $rule['selector'] ?? '-' }}</span>
                                            @if(!empty($rule['attribute']))
                                                <br>Attribute: <span class="text-blue-600">{{ $rule['attribute'] }}</span>
                                            @endif
                                        </div>

                                        @if(isset($rule['reason_template']) || isset($rule['recommendation']))
                                            <div class="mt-4 space-y-3">
                                                @if(isset($rule['reason_template']))
                                                    <div>
                                                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kenapa Penting?</h4>
                                                        <p class="text-sm text-slate-600">{{ $rule['reason_template'] }}</p>
                                                    </div>
                                                @endif
                                                @if(isset($rule['recommendation']))
                                                    <div>
                                                        <h4 class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-1">Cara Memperbaiki</h4>
                                                        <p class="text-sm text-slate-600">{{ $rule['recommendation'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-start">
                                        @if($isInstalled)
                                            <span class="inline-flex items-center text-sm font-medium text-emerald-600">
                                                <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                Terpasang
                                            </span>
                                        @else
                                            <form action="{{ route('admin.rules.bank.install') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="code" value="{{ $rule['code'] }}">
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-sm font-medium rounded-lg text-slate-700 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-300 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                    Gunakan Rule
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Empty State for Search -->
            <div id="empty-state" class="hidden py-12 text-center bg-white rounded-xl border border-slate-200 border-dashed">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <h3 class="text-lg font-medium text-slate-800">Rule tidak ditemukan</h3>
                <p class="text-sm text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.effect(() => {
            const query = document.querySelector('[x-model="searchQuery"]').__x.$data.searchQuery.toLowerCase();
            const activeCat = document.querySelector('[x-model="searchQuery"]').__x.$data.activeCategory;
            
            let hasVisible = false;
            
            document.querySelectorAll('.category-group').forEach(group => {
                const cat = group.getAttribute('data-category');
                let groupVisible = false;
                
                if (activeCat === 'all' || activeCat === cat) {
                    group.querySelectorAll('.rule-card').forEach(card => {
                        const name = card.getAttribute('data-name');
                        const code = card.getAttribute('data-code');
                        const selector = card.querySelector('.text-pink-600').textContent.toLowerCase();
                        
                        if (name.includes(query) || code.includes(query) || selector.includes(query)) {
                            card.style.display = 'block';
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
