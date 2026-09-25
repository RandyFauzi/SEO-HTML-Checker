<x-admin-layout>
    <x-slot name="title">{{ $rule->exists ? 'Edit Aturan' : 'Buat Aturan' }}</x-slot>
    <x-slot name="header">{{ $rule->exists ? 'Edit Aturan SEO' : 'Buat Aturan SEO Baru' }}</x-slot>

    <div class="max-w-7xl mx-auto" x-data="ruleForm()">
        
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ $rule->exists ? 'Edit Aturan' : 'Buat Aturan Baru' }}</h2>
            <a href="{{ route('admin.rules.index') }}" class="text-sm text-slate-500 hover:text-slate-800 font-bold flex items-center transition-colors bg-white/50 px-4 py-2 rounded-xl shadow-sm backdrop-blur-sm border border-white/60 hover:bg-white">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative mb-6 shadow-sm text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Form Section -->
            <div class="glass-panel rounded-[2.5rem] shadow-[0_8px_32px_rgba(0,0,0,0.04)] p-6 md:p-8 relative overflow-hidden">
                <form action="{{ $rule->exists ? route('admin.rules.update', $rule) : route('admin.rules.store') }}" method="POST" class="space-y-6 relative z-10" @submit="prepareSubmit">
                    @csrf
                    @if($rule->exists)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kode Aturan *</label>
                            <input type="text" name="code" x-model="formData.code" required class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm placeholder:text-slate-400 font-medium" placeholder="Contoh: CEK_JUDUL_HALAMAN">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Aturan *</label>
                            <input type="text" name="name" x-model="formData.name" required class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm placeholder:text-slate-400 font-medium" placeholder="Contoh: Cek Judul Halaman">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kategori *</label>
                            <input type="text" name="category" x-model="formData.category" required class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm placeholder:text-slate-400 font-medium" placeholder="Contoh: Meta Data">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tipe Aturan *</label>
                            @php
                            $ruleTypeLabels = [
                                'element_exists' => 'Elemen Harus Ada',
                                'element_count' => 'Jumlah Elemen',
                                'text_length' => 'Panjang Karakter',
                                'text_match' => 'Pencocokan Teks',
                                'url_match' => 'Pengecekan URL',
                                'link' => 'Validasi Tautan (Link)',
                                'compare_amp' => 'Bandingkan dengan AMP',
                                'schema_validation' => 'Validasi Schema (JSON-LD)'
                            ];
                            @endphp
                            <select name="rule_type" x-model="formData.rule_type" required class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm font-medium">
                                @foreach(\App\Enums\RuleType::cases() as $type)
                                    <option value="{{ $type->value }}">
                                        {{ $ruleTypeLabels[$type->value] ?? ucfirst(str_replace('_', ' ', $type->value)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Status Peringatan *</label>
                            <select name="severity" x-model="formData.severity" required class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm font-medium">
                                <option value="warning">Peringatan</option>
                                <option value="error">Kritis (Error)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mode Toggle -->
                    <div class="flex items-center justify-start mb-2 bg-slate-200/50 backdrop-blur-sm p-1 rounded-xl w-fit shadow-inner">
                        <button type="button" @click="setMode('basic')" :class="mode === 'basic' ? 'bg-white shadow-sm text-indigo-600 rounded-lg' : 'text-slate-500 hover:text-slate-700 rounded-lg'" class="px-4 py-2 text-xs font-bold transition-all">Mode Visual</button>
                        <button type="button" @click="setMode('advanced')" :class="mode === 'advanced' ? 'bg-white shadow-sm text-indigo-600 rounded-lg' : 'text-slate-500 hover:text-slate-700 rounded-lg'" class="px-4 py-2 text-xs font-bold transition-all">Mode JSON Lanjutan</button>
                    </div>

                    <!-- BASIC MODE (Visual Builder) -->
                    <div x-show="mode === 'basic'" x-cloak class="space-y-5 bg-indigo-50/50 backdrop-blur-sm p-6 rounded-2xl border border-indigo-100 mb-6">
                        <div>
                            <label class="block text-xs font-black text-indigo-900 uppercase tracking-wider mb-2">1. Elemen Target</label>
                            <select x-model="basic.target" @change="syncBasicToJson()" class="w-full bg-white/80 border border-indigo-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 text-sm font-medium">
                                <option value="title">Judul Halaman (&lt;title&gt;)</option>
                                <option value="meta_description">Meta Deskripsi</option>
                                <option value="h1">Heading Utama (H1)</option>
                                <option value="h2">Sub Heading (H2)</option>
                                <option value="canonical">Link Canonical</option>
                                <option value="canonical_match">Canonical vs URL Halaman</option>
                                <option value="page_url">URL Halaman Terperiksa</option>
                                <option value="links">Semua Tautan (&lt;a&gt;)</option>
                                <option value="custom">CSS Selector Kustom...</option>
                            </select>
                        </div>

                        <div x-show="basic.target === 'custom'" x-cloak>
                            <label class="block text-xs font-bold text-indigo-900 mb-1">CSS Selector Kustom</label>
                            <input type="text" x-model="basic.customSelector" @input.debounce="syncBasicToJson()" class="w-full bg-white/80 border border-indigo-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 text-sm font-medium" placeholder="Contoh: meta[property='og:title']">
                            
                            <label class="block text-xs font-bold text-indigo-900 mt-3 mb-1">Atribut yang diambil (Opsional)</label>
                            <input type="text" x-model="basic.attribute" @input.debounce="syncBasicToJson()" class="w-full bg-white/80 border border-indigo-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 text-sm font-medium" placeholder="Contoh: content, href, src">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-indigo-900 uppercase tracking-wider mb-2">2. Kondisi Aturan</label>
                            <select x-model="basic.condition" @change="syncBasicToJson()" class="w-full bg-white/80 border border-indigo-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 text-sm font-medium">
                                <option value="exist">Harus Ada di halaman</option>
                                <option value="count">Jumlah elemen harus tepat / antara</option>
                                <option value="length">Panjang karakter harus antara</option>
                                <option value="text_match">Teks harus cocok persis</option>
                                <option value="url_match">Cek URL (Protokol / Cocok / Regex)</option>
                                <option value="link">Validasi Tautan (Href / Target Blank)</option>
                            </select>
                        </div>

                        <!-- Dynamic Parameters based on Condition -->
                        <div x-show="basic.condition === 'length' || basic.condition === 'count'" x-cloak class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-indigo-900 mb-1">Nilai Minimal</label>
                                <input type="number" x-model="basic.min" @input.debounce="syncBasicToJson()" class="w-full bg-white/80 border border-indigo-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 text-sm font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-indigo-900 mb-1">Nilai Maksimal</label>
                                <input type="number" x-model="basic.max" @input.debounce="syncBasicToJson()" class="w-full bg-white/80 border border-indigo-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 text-sm font-medium">
                            </div>
                        </div>

                        <div x-show="basic.condition === 'text_match'" x-cloak>
                            <label class="block text-xs font-bold text-indigo-900 mb-1">Teks yang Diharapkan</label>
                            <input type="text" x-model="basic.expectedText" @input.debounce="syncBasicToJson()" class="w-full bg-white/80 border border-indigo-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 text-sm font-medium">
                        </div>
                        
                        <p class="text-xs text-indigo-600 flex items-center mt-2 font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Tipe Aturan dan JSON akan dibuat secara otomatis.
                        </p>
                    </div>

                    <!-- ADVANCED MODE (JSON) -->
                    <div x-show="mode === 'advanced'" x-cloak>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Konfigurasi JSON *</label>
                        <textarea x-model="configJson" rows="6" class="w-full bg-slate-900 text-emerald-400 border border-slate-700 rounded-xl p-4 font-mono text-sm focus:ring-2 focus:ring-indigo-400 shadow-inner" placeholder='{"selector": "h1"}'></textarea>
                        <input type="hidden" name="config" :value="configJson">
                        <p class="text-xs text-red-500 mt-1 font-bold" x-show="configError" x-text="configError"></p>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-slate-200/60">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Pesan Masalah (Apa yang salah)</label>
                            <input type="text" name="issue_message" x-model="formData.issue_message" class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm placeholder:text-slate-400 font-medium" placeholder="Contoh: Tag judul tidak ditemukan di halaman ini.">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Alasan (Mengapa ini penting)</label>
                            <input type="text" name="reason_template" x-model="formData.reason_template" class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm placeholder:text-slate-400 font-medium" placeholder="Contoh: Tag judul sangat penting untuk SEO dan pengunjung...">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Rekomendasi (Cara memperbaiki)</label>
                            <input type="text" name="recommendation" x-model="formData.recommendation" class="w-full bg-white/50 backdrop-blur-sm border border-white/60 rounded-xl p-3 focus:ring-2 focus:ring-indigo-300 focus:bg-white/80 transition-all shadow-sm placeholder:text-slate-400 font-medium" placeholder="Contoh: Tambahkan tag <title> di dalam elemen <head>.">
                        </div>
                    </div>

                    <div class="flex items-center pt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" x-model="formData.is_active" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded shadow-sm">
                        <label for="is_active" class="ml-3 block text-sm font-bold text-slate-700">Aktifkan Aturan Ini</label>
                    </div>

                    <div class="pt-6 border-t border-slate-200/60 flex gap-4">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-indigo-600/20 transition-all hover:scale-[1.02] hover:-translate-y-1">
                            {{ $rule->exists ? 'Update Aturan' : 'Simpan Aturan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Test Preview Section -->
            <div class="glass-panel bg-slate-900/90 backdrop-blur-2xl rounded-[2.5rem] shadow-[0_8px_32px_rgba(0,0,0,0.1)] border border-slate-700/50 p-6 md:p-8 text-white flex flex-col h-full relative overflow-hidden">
                <div class="absolute -top-20 -right-20 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Pratinjau & Uji Aturan
                </h3>
                <p class="text-sm text-slate-400 mb-4">Validasi logika aturan Anda sebelum menyimpan. Masukkan blok HTML contoh di bawah ini.</p>
                
                <div class="flex-1 flex flex-col min-h-[300px]">
                    <textarea x-model="testHtml" class="flex-1 w-full bg-slate-800 border border-slate-700 rounded-xl p-4 font-mono text-sm text-blue-200 focus:ring-blue-500 focus:border-blue-500 mb-4 resize-y" placeholder="<html>&#10;  <head>&#10;    <title>Hello World</title>&#10;  </head>&#10;</html>"></textarea>
                    
                    <button type="button" @click="runTest" :disabled="isTesting" class="w-full bg-slate-700/80 hover:bg-slate-600 text-white font-bold py-3.5 px-4 rounded-2xl shadow-sm transition-colors flex justify-center items-center gap-2 backdrop-blur-md">
                        <span x-show="!isTesting">Jalankan Uji Coba</span>
                        <span x-show="isTesting" class="animate-pulse">Sedang menguji...</span>
                    </button>
                </div>

                <!-- Test Results -->
                <div x-show="testResult !== null" class="mt-6 p-5 rounded-xl border" :class="testResult && testResult.passed ? 'bg-emerald-900/30 border-emerald-800' : 'bg-red-900/30 border-red-800'" x-cloak>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold" :class="testResult && testResult.passed ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'">
                            <svg x-show="testResult && testResult.passed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="testResult && !testResult.passed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        <h4 class="font-bold text-lg" :class="testResult && testResult.passed ? 'text-emerald-400' : 'text-red-400'" x-text="testResult && testResult.passed ? 'PASS' : 'FAIL'"></h4>
                    </div>

                    <div class="space-y-3 text-sm text-slate-300">
                        <template x-if="testResult && !testResult.passed && testResult.issue">
                            <div><span class="font-bold text-white">Issue:</span> <span x-text="testResult.issue"></span></div>
                        </template>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-if="testResult && testResult.expected">
                                <div><span class="font-bold text-white block">Expected:</span> <span x-text="testResult.expected"></span></div>
                            </template>
                            <template x-if="testResult && testResult.actual">
                                <div><span class="font-bold text-white block">Actual:</span> <span x-text="testResult.actual"></span></div>
                            </template>
                        </div>
                        <template x-if="testResult && testResult.snippet">
                            <div class="mt-2 pt-2 border-t border-slate-700/50">
                                <span class="font-bold text-white block mb-1">Snippet:</span> 
                                <pre class="bg-slate-950 p-3 rounded-lg overflow-x-auto text-xs text-slate-400"><code x-text="testResult.snippet"></code></pre>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function ruleForm() {
            return {
                mode: '{{ $rule->exists ? 'advanced' : 'basic' }}',
                basic: {
                    target: 'title',
                    customSelector: '',
                    attribute: '',
                    condition: 'exist',
                    min: '',
                    max: '',
                    expectedText: ''
                },
                formData: {
                    code: '{{ old('code', $rule->code) }}',
                    name: '{{ old('name', $rule->name) }}',
                    category: '{{ old('category', $rule->category ?? 'General') }}',
                    rule_type: '{{ old('rule_type', $rule->rule_type?->value ?? 'exist') }}',
                    severity: '{{ old('severity', $rule->severity ?? 'warning') }}',
                    issue_message: '{{ old('issue_message', $rule->issue_message) }}',
                    reason_template: '{{ old('reason_template', $rule->reason_template) }}',
                    recommendation: '{{ old('recommendation', $rule->recommendation) }}',
                    is_active: {{ old('is_active', $rule->is_active ?? true) ? 'true' : 'false' }},
                },
                configJson: `{!! addslashes(old('config', $rule->exists ? json_encode($rule->config, JSON_PRETTY_PRINT) : "{\n  \"selector\": \"\"\n}")) !!}`,
                configError: '',
                testHtml: '',
                isTesting: false,
                testResult: null,

                init() {
                    if (this.mode === 'basic') {
                        this.syncBasicToJson();
                    }
                },

                setMode(m) {
                    this.mode = m;
                    if (m === 'basic') {
                        if(confirm("Beralih ke Mode Visual akan menimpa konfigurasi JSON Anda saat ini. Lanjutkan?")) {
                            this.syncBasicToJson();
                        } else {
                            this.mode = 'advanced';
                        }
                    }
                },

                syncBasicToJson() {
                    let config = {};
                    
                    // Handle Target Selector
                    if (this.basic.target === 'title') {
                        config.selector = 'title';
                    } else if (this.basic.target === 'meta_description') {
                        config.selector = 'meta[name="description"]';
                        config.attribute = 'content';
                    } else if (this.basic.target === 'h1') {
                        config.selector = 'h1';
                    } else if (this.basic.target === 'h2') {
                        config.selector = 'h2';
                    } else if (this.basic.target === 'canonical') {
                        config.selector = 'link[rel="canonical"]';
                        config.attribute = 'href';
                    } else if (this.basic.target === 'canonical_match') {
                        config.target = 'canonical_match';
                        config.condition = 'canonical_match';
                        this.formData.rule_type = 'url_match';
                    } else if (this.basic.target === 'page_url') {
                        config.target = 'page_url';
                        config.condition = 'protocol';
                        config.expected = 'https';
                        this.formData.rule_type = 'url_match';
                    } else if (this.basic.target === 'links') {
                        config.selector = 'a';
                        config.condition = 'href_exists';
                        this.formData.rule_type = 'link';
                    } else {
                        config.selector = this.basic.customSelector;
                        if (this.basic.attribute) {
                            config.attribute = this.basic.attribute;
                        }
                    }

                    // Handle Condition (Updates Rule Type as well)
                    if (this.basic.condition === 'url_match') {
                        this.formData.rule_type = 'url_match';
                        if (!config.condition) config.condition = 'protocol';
                        if (!config.expected) config.expected = 'https';
                    } else if (this.basic.condition === 'link') {
                        this.formData.rule_type = 'link';
                        if (!config.condition) config.condition = 'href_exists';
                    } else if (this.basic.target !== 'canonical_match' && this.basic.target !== 'page_url') {
                        this.formData.rule_type = this.basic.condition;

                        if (this.basic.condition === 'length' || this.basic.condition === 'count') {
                            if (this.basic.min !== '') config.min = parseInt(this.basic.min);
                            if (this.basic.max !== '') config.max = parseInt(this.basic.max);
                        } else if (this.basic.condition === 'text_match') {
                            config.expected_text = this.basic.expectedText;
                            config.exact_match = true;
                        }
                    }

                    this.configJson = JSON.stringify(config, null, 2);
                },

                prepareSubmit(e) {
                    try {
                        JSON.parse(this.configJson);
                        this.configError = '';
                    } catch (err) {
                        e.preventDefault();
                        this.configError = 'JSON tidak valid: ' + err.message;
                    }
                },

                async runTest() {
                    try {
                        JSON.parse(this.configJson);
                        this.configError = '';
                    } catch (err) {
                        this.configError = 'JSON tidak valid: ' + err.message;
                        return;
                    }

                    if (!this.testHtml.trim()) {
                        alert('Harap masukkan HTML uji coba');
                        return;
                    }

                    this.isTesting = true;
                    this.testResult = null;

                    try {
                        const response = await fetch('{{ route('admin.rules.test') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                html: this.testHtml,
                                rule: {
                                    name: this.formData.name,
                                    rule_type: this.formData.rule_type,
                                    config: this.configJson
                                }
                            })
                        });

                        const data = await response.json();
                        this.testResult = data;
                    } catch (error) {
                        alert('Terjadi kesalahan saat menguji: ' + error.message);
                    } finally {
                        this.isTesting = false;
                    }
                }
            }
        }
    </script>
</x-admin-layout>
