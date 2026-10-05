<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI HTML Editor</title>
    <x-favicon />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen relative overflow-x-hidden antialiased text-slate-800">
    <!-- Header/Navbar -->
        <header x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 w-full z-50 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 shadow-sm">
        <div class="px-4 md:px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <img src="{{ asset('Logo.webp') }}" alt="Logo" class="w-8 h-8 object-contain">
                <span class="font-extrabold text-lg md:text-xl tracking-tight text-slate-800">SEO Checker</span>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-1">
                @auth
                    <a href="{{ route('seo.index') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100 px-4 py-2 rounded-lg transition-colors">SEO Checker</a>
                    <a href="{{ route('admin.history.index') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100 px-4 py-2 rounded-lg transition-colors">Riwayat Audit</a>
                    <a href="{{ route('admin.ai_editor.index') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100 px-4 py-2 rounded-lg transition-colors">AI HTML Editor</a>
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100 px-4 py-2 rounded-lg transition-colors">Panel Admin</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-indigo-600 bg-white border border-indigo-200 px-5 py-2.5 rounded-full hover:bg-indigo-50 transition-colors shadow-sm">Masuk</a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-slate-600 hover:text-slate-900 focus:outline-none p-2 bg-white rounded-lg border border-slate-200 shadow-sm">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <svg x-show="mobileMenuOpen" style="display: none;" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden bg-white border-t border-slate-100 shadow-lg absolute w-full left-0">
            <div class="px-4 py-3 flex flex-col gap-2">
                @auth
                    <a href="{{ route('seo.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors">SEO Checker</a>
                    <a href="{{ route('admin.history.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors">Riwayat Audit</a>
                    <a href="{{ route('admin.ai_editor.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors">AI HTML Editor</a>
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors">Panel Admin</a>
                @else
                    <a href="{{ route('login') }}" class="block text-center text-sm font-bold text-white bg-indigo-600 px-5 py-3 rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

<!-- Main Content -->
    <main class="relative z-0 w-full min-h-screen px-4 sm:px-6 lg:px-8 pt-32 lg:pt-36 pb-12 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-50 via-slate-50 to-white">
        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4 leading-tight">
                AI HTML Editor
            </h1>
            <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto">
                Otomatiskan perubahan kode HTML Landing Page atau AMP menggunakan instruksi cerdas dari AI.
            </p>
        </div>

        <div class="w-full max-w-[1800px] mx-auto">
            
    <!-- Main Editor Grid -->
    <div x-data="aiEditor()" class="w-full max-w-[1600px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 relative z-10 h-full">
        
        <!-- Left Panel: Form & Settings -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/40 p-6 relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-50 rounded-full blur-2xl opacity-60 pointer-events-none"></div>
                
                <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 relative z-10">
                    <div class="bg-indigo-100 text-indigo-600 p-2 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    Konfigurasi Editor
                </h2>

                <div class="flex gap-2 mb-6 p-1 bg-slate-100 rounded-xl relative z-10">
                    <button type="button" @click="editorMode = 'auto'" :class="editorMode === 'auto' ? 'bg-white shadow-sm text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 text-sm rounded-lg transition-all">Auto-Pilot (Template)</button>
                    <button type="button" @click="editorMode = 'manual'" :class="editorMode === 'manual' ? 'bg-white shadow-sm text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 text-sm rounded-lg transition-all">Manual Prompt</button>
                </div>
                
                <!-- Manual Mode Form -->
                <form x-show="editorMode === 'manual'" @submit.prevent="processForm" class="space-y-5 relative z-10">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">URL Target (LP / AMP)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            </div>
                            <input type="url" x-model="url" :required="editorMode === 'manual'" placeholder="https://example.com/lp" class="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white transition-colors">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex justify-between items-center">
                            <span>Instruksi AI (Prompt Bebas)</span>
                        </label>
                        <textarea x-model="prompt" :required="editorMode === 'manual'" rows="6" placeholder="Ketik perintah modifikasi HTML di sini..." class="w-full p-4 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white transition-colors resize-none"></textarea>
                    </div>
                    
                    <div x-show="errorMsg" style="display: none;" class="p-4 bg-red-50/80 text-red-700 rounded-xl text-sm border border-red-100 flex items-start gap-3 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="errorMsg" class="font-medium"></span>
                    </div>

                    <!-- Aurora Frosted Glass Button: AI Editor -->
                                                            <button type="submit" :disabled="loading" class="group relative inline-flex items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.22)] w-full mt-4">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-b from-white/90 via-white/40 to-white/70 p-[1px]"></span>
                        <span class="relative flex w-full items-center justify-center gap-2.5 rounded-full bg-white/75 backdrop-blur-xl px-6 py-4 text-sm font-bold text-slate-800 transition-all duration-300 group-hover:bg-white/85 group-hover:text-slate-900 border border-white/70 shadow-[inset_0_1px_2px_rgba(255,255,255,1),inset_0_-1px_2px_rgba(0,0,0,0.03)] overflow-hidden">
                            <!-- Glow effects -->
                            <span class="absolute -top-3 right-1/4 h-12 w-24 rounded-full bg-sky-400/50 blur-xl transition-all duration-500 group-hover:h-16 group-hover:w-32 group-hover:bg-sky-400/70 group-hover:scale-110"></span>
                            <span class="absolute -top-2 right-10 h-10 w-20 rounded-full bg-indigo-500/40 blur-lg transition-all duration-500 group-hover:scale-125 group-hover:bg-indigo-500/60"></span>
                            <span class="absolute -bottom-2 right-4 h-12 w-20 rounded-full bg-rose-400/30 blur-xl transition-all duration-500 group-hover:bg-rose-400/50 group-hover:scale-110"></span>
                            
                            <template x-if="loading">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-indigo-600 relative z-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                            <template x-if="!loading">
                                <svg class="w-5 h-5 text-indigo-600 relative z-10 group-hover:rotate-12 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2L14.2 7.8L20 10L14.2 12.2L12 18L9.8 12.2L4 10L9.8 7.8L12 2Z" />
                                </svg>
                            </template>
                            <span class="relative z-10" x-text="loading ? 'AI Sedang Bekerja...' : 'Jalankan Manual'"></span>
                        </span>
                    </button>
                </form>

                <!-- Auto-Pilot Form -->
                <form x-show="editorMode === 'auto'" @submit.prevent="processAutoForm" class="space-y-4 relative z-10" style="display: none;">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-semibold text-slate-700">Pilih Template (Maks 10)</label>
                            <button type="button" @click="fetchTemplates" class="text-xs text-indigo-600 hover:underline">Refresh</button>
                        </div>
                                                  <!-- CUSTOM DROPDOWN -->
                          <div x-data="{ openDropdown: false }" class="relative">
                              <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" type="button" class="w-full py-2.5 px-4 rounded-xl border border-slate-200 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 bg-slate-50 hover:bg-white transition-all text-sm flex justify-between items-center text-left cursor-pointer">
                                  <span x-text="autoForm.template_id ? dbTemplates.find(t => t.id == autoForm.template_id)?.name : '-- Pilih Template yang Tersimpan --'" :class="autoForm.template_id ? 'text-slate-800 font-semibold' : 'text-slate-500'" class="truncate"></span>
                                  <div class="flex items-center gap-2">
                                      <span x-show="autoForm.template_id" x-text="dbTemplates.find(t => t.id == autoForm.template_id)?.type" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 hidden sm:inline-block" style="display: none;"></span>
                                      <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-300" :class="{'rotate-180': openDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                  </div>
                              </button>
                              
                              <div x-show="openDropdown" 
                                   x-transition:enter="transition ease-out duration-200"
                                   x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                   x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                   x-transition:leave="transition ease-in duration-150"
                                   x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                   x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                   class="absolute z-[60] w-full mt-2 bg-white border border-slate-100 rounded-xl shadow-xl max-h-72 overflow-y-auto py-2 ring-1 ring-black/5" 
                                   style="display: none;">
                                  
                                  <div class="px-4 py-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider sticky top-0 bg-white/90 backdrop-blur-sm z-10 mb-1 border-b border-slate-50">Daftar Template Tersedia</div>
                                  
                                  <template x-for="tpl in dbTemplates" :key="tpl.id">
                                      <button type="button" @click="autoForm.template_id = tpl.id; openDropdown = false" class="w-full text-left px-4 py-3 text-sm hover:bg-indigo-50 hover:text-indigo-700 transition-colors flex items-center justify-between group border-l-2 border-transparent hover:border-indigo-500">
                                          <div class="flex flex-col gap-0.5">
                                              <span class="font-semibold text-slate-700 group-hover:text-indigo-700 transition-colors" x-text="tpl.name"></span>
                                          </div>
                                          <span class="text-xs font-bold px-2 py-1 rounded-md bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors shadow-sm" x-text="tpl.type"></span>
                                      </button>
                                  </template>
                                  
                                  <div x-show="dbTemplates.length === 0" class="px-4 py-6 text-sm text-slate-400 text-center flex flex-col items-center gap-2">
                                      <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                      <span>Belum ada template tersimpan</span>
                                  </div>
                              </div>
                          </div>
                          <!-- Hidden input to maintain required validation if needed -->
                          <select x-model="autoForm.template_id" :required="editorMode === 'auto'" class="hidden">
                              <option value=""></option>
                              <template x-for="tpl in dbTemplates" :key="tpl.id">
                                  <option :value="tpl.id"></option>
                              </template>
                          </select>
                        <button type="button" @click="showModal = true" class="mt-2 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-1.5 rounded-lg w-full transition-colors">+ Tambah Template Baru ke DB</button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Keyword Topik</label>
                            <input type="text" x-model="autoForm.keyword" placeholder="Cth: Top Up Game Mobile" :required="editorMode === 'auto'" class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Brand</label>
                            <input type="text" x-model="autoForm.brand" placeholder="Cth: ACONGSTORE" class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Link Canonical</label>
                            <input type="url" x-model="autoForm.canonical_url" placeholder="https://" class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 bg-slate-50 hover:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Link AMP</label>
                            <input type="url" x-model="autoForm.amp_url" placeholder="https://" class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 bg-slate-50 hover:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Link Favicon</label>
                            <input type="url" x-model="autoForm.favicon_url" placeholder="https://" class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 bg-slate-50 hover:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Link Logo</label>
                            <input type="url" x-model="autoForm.logo_url" placeholder="https://" class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 bg-slate-50 hover:bg-white">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Link Banners (Pisahkan enter)</label>
                            <textarea x-model="autoForm.banner_urls" rows="2" placeholder="https://.../banner1.png&#10;https://.../banner2.png" class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 bg-slate-50 hover:bg-white resize-none"></textarea>
                        </div>
                    </div>

                    <div x-show="errorMsg" style="display: none;" class="p-3 bg-red-50/80 text-red-700 rounded-lg text-xs border border-red-100 flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="errorMsg" class="font-medium"></span>
                    </div>

                                                            <button type="submit" :disabled="loading" class="group relative inline-flex items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.22)] w-full mt-4">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-b from-white/90 via-white/40 to-white/70 p-[1px]"></span>
                        <span class="relative flex w-full items-center justify-center gap-2.5 rounded-full bg-white/75 backdrop-blur-xl px-6 py-4 text-sm font-bold text-slate-800 transition-all duration-300 group-hover:bg-white/85 group-hover:text-slate-900 border border-white/70 shadow-[inset_0_1px_2px_rgba(255,255,255,1),inset_0_-1px_2px_rgba(0,0,0,0.03)] overflow-hidden">
                            <!-- Glow effects -->
                            <span class="absolute -top-3 right-1/4 h-12 w-24 rounded-full bg-sky-400/50 blur-xl transition-all duration-500 group-hover:h-16 group-hover:w-32 group-hover:bg-sky-400/70 group-hover:scale-110"></span>
                            <span class="absolute -top-2 right-10 h-10 w-20 rounded-full bg-indigo-500/40 blur-lg transition-all duration-500 group-hover:scale-125 group-hover:bg-indigo-500/60"></span>
                            <span class="absolute -bottom-2 right-4 h-12 w-20 rounded-full bg-rose-400/30 blur-xl transition-all duration-500 group-hover:bg-rose-400/50 group-hover:scale-110"></span>
                            
                            <template x-if="loading">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-indigo-600 relative z-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                            <template x-if="!loading">
                                <svg class="w-5 h-5 text-indigo-600 relative z-10 group-hover:rotate-12 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2L14.2 7.8L20 10L14.2 12.2L12 18L9.8 12.2L4 10L9.8 7.8L12 2Z" />
                                </svg>
                            </template>
                            <span class="relative z-10" x-text="loading ? 'AI Sedang Bekerja...' : 'Generate (Auto-Pilot)'"></span>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Panel: Work Area / Results -->
        <div class="lg:col-span-8 flex flex-col">
            <!-- Empty State -->
            <div x-show="!hasResult && !loading" class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/40 flex flex-col items-center justify-center p-12 text-center flex-1 min-h-[500px]">
                <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Area Kerja Editor</h3>
                <p class="text-slate-500 max-w-md mx-auto leading-relaxed">Masukkan URL dan instruksi di panel sebelah kiri, lalu tekan tombol Eksekusi untuk melihat keajaiban AI merombak kode HTML Anda secara presisi.</p>
            </div>
            
            <!-- Result State -->
            <div x-show="hasResult" style="display: none;" class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden flex flex-col flex-1 min-h-[600px]">
                <!-- Tabs Navigation -->
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-2">
                    <div class="flex overflow-x-auto">
                        <button @click="activeTab = 'preview'" :class="{'border-indigo-600 text-indigo-700 bg-white shadow-sm': activeTab === 'preview', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'preview'}" class="px-5 py-3.5 border-b-2 font-bold text-sm transition-all whitespace-nowrap rounded-t-xl mt-2 mx-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Split Preview
                            </div>
                        </button>
                        <button @click="activeTab = 'code'" :class="{'border-indigo-600 text-indigo-700 bg-white shadow-sm': activeTab === 'code', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'code'}" class="px-5 py-3.5 border-b-2 font-bold text-sm transition-all whitespace-nowrap rounded-t-xl mt-2 mx-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                Hasil Kode
                            </div>
                        </button>
                        <button @click="activeTab = 'ops'" :class="{'border-indigo-600 text-indigo-700 bg-white shadow-sm': activeTab === 'ops', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'ops'}" class="px-5 py-3.5 border-b-2 font-bold text-sm transition-all whitespace-nowrap rounded-t-xl mt-2 mx-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                Log Operasi DOM
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Tab Contents -->
                <div class="flex-1 bg-white relative">
                    
                    <!-- Preview Tab -->
                    <div x-show="activeTab === 'preview'" class="absolute inset-0 w-full h-full p-4 flex flex-col lg:flex-row gap-4">
                        <div class="flex-1 flex flex-col h-full">
                            <div class="bg-slate-100 text-slate-500 text-xs font-bold uppercase tracking-widest py-2 px-4 rounded-t-xl border border-b-0 border-slate-200">
                                Original (Sebelum)
                            </div>
                            <div class="flex-1 bg-white rounded-b-xl border border-slate-200 overflow-hidden relative">
                                <iframe :srcdoc="originalHtml" class="w-full h-full absolute inset-0" sandbox="allow-same-origin allow-scripts"></iframe>
                            </div>
                        </div>
                        <div class="flex-1 flex flex-col h-full">
                            <div class="bg-indigo-50 text-indigo-600 text-xs font-bold uppercase tracking-widest py-2 px-4 rounded-t-xl border border-b-0 border-indigo-200 flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                </span>
                                Modified (Sesudah)
                            </div>
                            <div class="flex-1 bg-white rounded-b-xl border border-indigo-200 overflow-hidden relative shadow-[inset_0_0_10px_rgba(99,102,241,0.1)]">
                                <iframe :srcdoc="modifiedHtml" class="w-full h-full absolute inset-0" sandbox="allow-same-origin allow-scripts"></iframe>
                            </div>
                        </div>
                    </div>

                    <!-- Code Tab -->
                    <div x-show="activeTab === 'code'" class="absolute inset-0 w-full h-full p-4 flex flex-col bg-slate-50">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm text-slate-500 font-medium">Kode HTML final yang sudah dimodifikasi oleh AI.</p>
                            <div class="flex gap-2">
                                <button @click="downloadCode" class="text-xs font-bold bg-slate-800 text-white hover:bg-slate-900 px-4 py-2 rounded-xl transition-all shadow-md flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download .html
                                </button>
                                <button @click="copyCode" class="text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-xl transition-all shadow-md flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    Salin Kode
                                </button>
                            </div>
                        </div>
                        <textarea readonly x-model="modifiedHtml" class="flex-1 w-full p-5 font-mono text-xs leading-relaxed text-slate-700 bg-white border border-slate-200 rounded-2xl resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner"></textarea>
                    </div>

                    <!-- Ops Tab -->
                    <div x-show="activeTab === 'ops'" class="absolute inset-0 w-full h-full p-6 overflow-y-auto bg-slate-50">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <template x-if="assembledPrompt">
                                <div class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                                    <h4 class="text-xs font-bold text-indigo-700 uppercase mb-2">Prompt Auto-Pilot (Dihasilkan Otomatis):</h4>
                                    <pre class="text-[10px] text-slate-700 font-mono whitespace-pre-wrap leading-relaxed" x-text="assembledPrompt"></pre>
                                </div>
                            </template>

                            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.963 11.963 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                Jejak Audit Operasi DOM
                            </h3>
                            <p class="text-sm text-slate-500 mb-6">Daftar manipulasi presisi yang dilakukan oleh AI berdasarkan CSS Selector. Tidak ada tag lain yang disentuh.</p>
                            
                            <div class="space-y-4">
                                <template x-for="(op, index) in operations" :key="index">
                                    <div class="flex gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:shadow-md transition-all">
                                        <div class="shrink-0">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs" x-text="index + 1"></div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <span class="font-bold text-[10px] uppercase tracking-wider bg-slate-800 text-white px-2 py-1 rounded-md" x-text="op.action"></span>
                                                <span class="font-mono text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded-md border border-indigo-100" x-text="op.selector"></span>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 bg-white p-3 rounded-lg border border-slate-100">
                                                <template x-if="op.attribute">
                                                    <div>
                                                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Target Atribut</span>
                                                        <span class="text-xs font-semibold text-slate-700" x-text="op.attribute"></span>
                                                    </div>
                                                </template>
                                                <template x-if="op.value">
                                                    <div :class="{'md:col-span-2': !op.attribute}">
                                                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nilai Baru (Value)</span>
                                                        <div class="text-xs text-slate-700 font-mono bg-slate-50 p-2 rounded border border-slate-100 break-all max-h-32 overflow-y-auto" x-text="op.value"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="operations.length === 0">
                                    <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                                        <span class="text-slate-500 font-medium">AI tidak menemukan elemen yang cocok atau tidak melakukan perubahan apapun.</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
        <!-- Add Template Modal -->
    <template x-teleport="body">
<div x-show="showModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
      
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!-- Modal panel -->
                <div x-show="showModal" @click.away="showModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                    
                    <!-- Modal Header -->
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2" id="modal-title">
                            <div class="bg-indigo-100 p-1.5 rounded-lg text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </div>
                            Simpan Template Baru
                        </h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Template</label>
                            <input type="text" x-model="newTemplate.name" placeholder="Misal: Template Top Up Mobile LP" class="w-full px-4 py-2.5 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Tipe Halaman</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label :class="newTemplate.type === 'LP' ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-slate-200 text-slate-600'" class="border rounded-xl p-3 flex items-center justify-center cursor-pointer font-bold transition-all hover:bg-slate-50">
                                    <input type="radio" x-model="newTemplate.type" value="LP" class="sr-only">
                                    Landing Page (LP)
                                </label>
                                <label :class="newTemplate.type === 'AMP' ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-slate-200 text-slate-600'" class="border rounded-xl p-3 flex items-center justify-center cursor-pointer font-bold transition-all hover:bg-slate-50">
                                    <input type="radio" x-model="newTemplate.type" value="AMP" class="sr-only">
                                    AMP
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">URL / Link Asli Template</label>
                            <input type="url" x-model="newTemplate.url" placeholder="https://..." class="w-full px-4 py-2.5 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-row-reverse gap-3">
                        <button type="button" @click="saveNewTemplate" :disabled="savingTemplate" class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 sm:w-auto disabled:opacity-50 transition-colors">
                            <span x-show="!savingTemplate">Simpan ke Database</span>
                            <span x-show="savingTemplate">Menyimpan...</span>
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </template>
<script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('aiEditor', () => ({
                editorMode: 'auto', // 'manual' or 'auto'
                showModal: false,
                savingTemplate: false,
                newTemplate: {
                    name: '',
                    type: 'LP',
                    url: ''
                },
                url: '',
                prompt: '',
                autoForm: {
                    template_id: '',
                    keyword: '',
                    brand: 'ACONGSTORE',
                    canonical_url: '',
                    amp_url: '',
                    favicon_url: '',
                    logo_url: '',
                    banner_urls: ''
                },
                dbTemplates: [],
                loading: false,
                errorMsg: '',
                hasResult: false,
                originalHtml: '',
                modifiedHtml: '',
                operations: [],
                assembledPrompt: '',
                activeTab: 'preview',

                init() {
                    this.fetchTemplates();
                },

                async fetchTemplates() {
                    try {
                        const res = await fetch('/admin/ai-templates');
                        this.dbTemplates = await res.json();
                    } catch (e) {
                        console.error('Failed to fetch templates');
                    }
                },

                                async saveNewTemplate() {
                    if (!this.newTemplate.name || !this.newTemplate.url) {
                        alert("Nama dan URL wajib diisi!");
                        return;
                    }
                    this.savingTemplate = true;
                    try {
                        const res = await fetch('/admin/ai-templates', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: this.newTemplate.name,
                                type: this.newTemplate.type,
                                url_or_html: this.newTemplate.url
                            })
                        });
                        if (res.ok) {
                            this.showModal = false;
                            this.newTemplate = {name: '', type: 'LP', url: ''};
                            await this.fetchTemplates();
                            
                            // Auto-select the newly added template
                            if (this.dbTemplates.length > 0) {
                                this.autoForm.template_id = this.dbTemplates[0].id;
                            }
                        } else {
                            const err = await res.json();
                            alert(err.error || "Gagal menyimpan");
                        }
                    } catch (e) {
                        alert("Error jaringan.");
                    } finally {
                        this.savingTemplate = false;
                    }
                },

                async processForm() {
                    this.loading = true;
                    this.errorMsg = '';
                    this.assembledPrompt = '';
                    
                    try {
                        const response = await fetch('/admin/ai-editor/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                url: this.url,
                                prompt: this.prompt
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.error || 'Terjadi kesalahan pada server');
                        }

                        this.originalHtml = data.original_html;
                        this.modifiedHtml = data.modified_html;
                        this.operations = data.operations;
                        
                        this.hasResult = true;
                        this.activeTab = 'preview';

                    } catch (error) {
                        this.errorMsg = error.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async processAutoForm() {
                    this.loading = true;
                    this.errorMsg = '';
                    this.assembledPrompt = '';
                    
                    try {
                        const response = await fetch('/admin/ai-editor/process-auto', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.autoForm)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.error || 'Terjadi kesalahan pada server');
                        }

                        this.originalHtml = data.original_html;
                        this.modifiedHtml = data.modified_html;
                        this.operations = data.operations;
                        this.assembledPrompt = data.assembled_prompt;
                        
                        this.hasResult = true;
                        this.activeTab = 'preview';

                    } catch (error) {
                        this.errorMsg = error.message;
                    } finally {
                        this.loading = false;
                    }
                },

                copyCode() {
                    navigator.clipboard.writeText(this.modifiedHtml);
                    alert('Kode HTML berhasil disalin ke clipboard!');
                },

                downloadCode() {
                    const blob = new Blob([this.modifiedHtml], { type: 'text/html' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    // Format filename based on url
                    const domainName = this.url ? new URL(this.url).hostname.replace('www.', '') : 'edited';
                    a.download = `optimized-${domainName}.html`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            }));
        });
    </script>
    

        </div>
    </main>
</body>
</html>
