@php
    $initialRows = [];
    $oldLpUrls = old('lp_urls');
    $oldAmpUrls = old('amp_urls');
    
    if ($oldLpUrls) {
        $lpLines = explode("\n", $oldLpUrls);
        $ampLines = $oldAmpUrls ? explode("\n", $oldAmpUrls) : [];
        foreach ($lpLines as $i => $lp) {
            $initialRows[] = [
                'lp' => trim($lp),
                'amp' => isset($ampLines[$i]) ? trim($ampLines[$i]) : ''
            ];
        }
    }
    if (empty($initialRows)) {
        $initialRows[] = ['lp' => '', 'amp' => ''];
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SEO Checker</title>
    <x-favicon />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-gray-100 to-purple-50 min-h-screen relative overflow-x-hidden antialiased text-slate-800">
    <!-- Header/Navbar -->
    <header class="absolute top-0 w-full p-6 flex justify-between items-center z-20">
        <div class="flex items-center gap-2">
            <img src="{{ asset('Logo.webp') }}" alt="Logo" class="w-8 h-8 object-contain">
            <span class="font-extrabold text-xl tracking-tight text-slate-800">SEO Checker</span>
        </div>
        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-purple-600 px-4 py-2">Panel Admin</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-bold text-purple-600 bg-white border border-purple-200 px-5 py-2.5 rounded-full hover:bg-purple-50 transition-colors shadow-sm">Masuk</a>
            @endauth
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 flex flex-col items-center justify-center min-h-screen px-4 pt-20">
        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto mb-10">
            <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight">
                SEO Checker
            </h1>
            <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto">
                Masukkan URL domain atau website untuk menjalankan audit SEO. Temukan masalah teknikal dan optimasi on-page, lalu dapatkan laporan lengkap cara memperbaikinya.
            </p>
        </div>

        <div class="w-full max-w-6xl mx-auto" 
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
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <input type="url" x-model="row.lp" placeholder="URL Landing Page (Contoh: https://example.com)" required
                                        class="w-full bg-slate-100/50 border-none rounded-xl sm:rounded-2xl px-4 py-3 sm:px-6 sm:py-4 text-sm focus:ring-2 focus:ring-blue-200 focus:bg-white transition-all shadow-inner placeholder:text-slate-400 text-slate-700 font-medium">
                                </div>
                                <div>
                                    <input type="url" x-model="row.amp" placeholder="URL AMP (Opsional)"
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
                        <span x-text="submitting ? 'Memproses...' : 'Jalankan Audit'"></span>
                    </button>
                </div>
            </form>
        </div>

        <div class="w-full max-w-6xl mx-auto mt-10 pb-20 text-left">
            @include('seo-checker.results')
        </div>
    </main>
</body>
</html>
