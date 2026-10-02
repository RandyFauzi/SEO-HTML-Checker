<x-admin-layout>
    <x-slot name="title">Manajemen Aturan SEO</x-slot>
    <x-slot name="header">Manajemen Aturan</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4" x-data="{ showAiModal: false, aiPrompt: '', aiLoading: false, aiResult: null, aiError: null, generateAi() { this.aiLoading = true; this.aiError = null; fetch('{{ route('admin.rules.ai-generate') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ prompt: this.aiPrompt }) }).then(res => res.json()).then(data => { this.aiLoading = false; if(data.success) { this.aiResult = data.data; } else { this.aiError = data.message; } }).catch(e => { this.aiLoading = false; this.aiError = 'Terjadi kesalahan jaringan.'; }); } }">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Aturan Pengecekan SEO</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">Kelola semua aturan yang digunakan untuk mengecek landing page.</p>
            </div>
            <div class="flex flex-col md:flex-row w-full md:w-auto gap-3">
                @if($rules->count() > 0)
                <form action="{{ route('admin.rules.destroyAll') }}" method="POST" class="w-full md:w-auto" onsubmit="return confirm('Peringatan: Aksi ini akan menghapus semua aturan SEO milik Anda!\nAnda yakin ingin melanjutkannya?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full md:w-auto justify-center bg-red-50 hover:bg-red-500 text-red-600 hover:text-white font-bold py-3 md:py-2.5 px-6 rounded-[1.25rem] shadow-sm hover:-translate-y-1 hover:shadow-md transition-all border border-red-100 hover:border-red-500 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Semua
                    </button>
                </form>
                @endif
                <button @click="showAiModal = true" type="button" class="w-full md:w-auto justify-center bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-3 md:py-2.5 px-6 rounded-[1.25rem] shadow-lg shadow-purple-500/30 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-purple-500/40 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Buat dengan AI
                </button>
                <a href="{{ route('admin.rules.create') }}" class="w-full md:w-auto justify-center bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 md:py-2.5 px-6 rounded-[1.25rem] shadow-lg shadow-slate-800/20 transition-all hover:-translate-y-1 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Manual
                </a>
            </div>

            <!-- AI Modal -->
            <div x-show="showAiModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showAiModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showAiModal = false" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showAiModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-100">
                        <div class="bg-gradient-to-br from-purple-50 to-white px-6 pt-8 pb-6 sm:px-8 sm:pb-8 relative">
                            <button @click="showAiModal = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-purple-100 text-purple-600 sm:mx-0 sm:h-12 sm:w-12 shadow-inner border border-purple-200">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </div>
                                <div class="mt-4 text-center sm:mt-0 sm:ml-5 sm:text-left w-full">
                                    <h3 class="text-xl leading-6 font-extrabold text-slate-900" id="modal-title">AI Rule Builder</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-500 mb-4">Jelaskan aturan SEO yang Anda inginkan dengan bahasa sehari-hari. AI akan menerjemahkannya menjadi format sistem secara otomatis.</p>
                                        
                                        <template x-if="!aiResult">
                                            <div>
                                                <textarea x-model="aiPrompt" rows="4" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:border-purple-500 focus:ring-0 focus:outline-none transition-colors" placeholder="Contoh: 'Tolong pastikan semua gambar punya atribut alt, kalau nggak ada tampilkan pesan error yang bilang gambar harus punya deskripsi'"></textarea>
                                                
                                                <div x-show="aiError" class="mt-3 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-100 font-medium" x-text="aiError"></div>
                                                
                                                <div class="mt-5 flex justify-end">
                                                    <button @click="generateAi()" :disabled="aiLoading || aiPrompt.trim() === ''" class="bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all flex items-center">
                                                        <svg x-show="aiLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                        <span x-text="aiLoading ? 'Sedang meracik...' : 'Generate Rule'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <template x-if="aiResult">
                                            <div class="space-y-4">
                                                <div class="bg-green-50 text-green-700 p-3 rounded-lg border border-green-200 text-sm font-medium flex items-start">
                                                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Berhasil! AI telah merumuskan aturan berikut. Salin JSON ini, lalu tutup popup dan paste di menu "Import Aturan".
                                                </div>
                                                <div class="relative group">
                                                    <textarea readonly rows="8" class="w-full font-mono text-xs bg-slate-900 text-green-400 border border-slate-700 rounded-xl px-4 py-3 focus:outline-none" x-text="JSON.stringify([aiResult], null, 2)"></textarea>
                                                </div>
                                                <div class="mt-5 flex justify-end gap-3">
                                                    <button @click="aiResult = null" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-6 rounded-xl transition-all">Ubah Prompt</button>
                                                    <button @click="navigator.clipboard.writeText(JSON.stringify([aiResult], null, 2)); alert('JSON disalin! Silakan paste di kotak Import di halaman utama.'); showAiModal = false; aiResult = null;" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all">
                                                        Salin & Tutup
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50/80 backdrop-blur-md border border-green-200/50 text-green-700 px-5 py-4 rounded-2xl relative mb-6 shadow-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50/80 backdrop-blur-md border border-red-200/50 text-red-700 px-5 py-4 rounded-2xl relative mb-6 shadow-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="glass-panel rounded-[2rem] shadow-[0_8px_32px_rgba(0,0,0,0.04)] overflow-hidden mb-8">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-white/40 border-b border-white/60 text-slate-500 uppercase tracking-wider text-xs font-bold">
                        <tr>
                            <th class="px-6 py-5">Kode</th>
                            <th class="px-6 py-5">Nama Aturan</th>
                            <th class="px-6 py-5">Tipe</th>
                            <th class="px-6 py-5">Kategori</th>
                            <th class="px-6 py-5">Status</th>
                            <th class="px-6 py-5 text-center">Aktif</th>
                            <th class="px-6 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/40 text-slate-700">
                        @forelse($rules as $rule)
                            <tr class="hover:bg-white/50 transition-colors group">
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $rule->code ?: '#'.$rule->id }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $rule->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-medium border border-gray-200">{{ $rule->rule_type->value ?? $rule->rule_type }}</span>
                                </td>
                                <td class="px-6 py-4"><span class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-xs border border-indigo-100">{{ $rule->category }}</span></td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-semibold border {{ $rule->severity === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-yellow-50 text-yellow-700 border-yellow-200' }}">
                                        {{ $rule->severity === 'error' ? 'Kritis' : 'Peringatan' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div x-data="{ 
                                            isActive: {{ $rule->is_active ? 'true' : 'false' }}, 
                                            loading: false,
                                            toggleStatus() {
                                                this.loading = true;
                                                fetch('{{ route('admin.rules.toggle', $rule) }}', {
                                                    method: 'PATCH',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json'
                                                    }
                                                })
                                                .then(res => res.json())
                                                .then(data => {
                                                    this.isActive = data.is_active;
                                                    this.loading = false;
                                                })
                                                .catch(() => this.loading = false);
                                            }
                                        }">
                                        <button @click="toggleStatus" 
                                                :disabled="loading"
                                                :class="isActive ? 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'"
                                                class="px-3 py-1 rounded-full text-xs font-bold transition-colors w-24 text-center shadow-sm border">
                                            <span x-show="!loading" x-text="isActive ? 'Aktif' : 'Nonaktif'"></span>
                                            <span x-show="loading" class="animate-pulse">Tunggu...</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    <a href="{{ route('admin.rules.edit', $rule) }}" class="text-blue-600 hover:text-blue-900 font-medium transition-colors">Edit</a>
                                    <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium transition-colors">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                                    <p class="text-base font-medium text-gray-900">Belum ada aturan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-100">
                @forelse($rules as $rule)
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-xs font-mono text-gray-500 mb-1">{{ $rule->code ?: '#'.$rule->id }}</div>
                                <div class="font-bold text-gray-900 text-base leading-tight">{{ $rule->name }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $rule->severity === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-yellow-50 text-yellow-700 border-yellow-200' }}">
                                {{ $rule->severity === 'error' ? 'KRITIS' : 'PERINGATAN' }}
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200">{{ $rule->rule_type->value ?? $rule->rule_type }}</span>
                            <span class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100">{{ $rule->category }}</span>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-gray-50 mt-2">
                            <div x-data="{ 
                                    isActive: {{ $rule->is_active ? 'true' : 'false' }}, 
                                    loading: false,
                                    toggleStatus() {
                                        this.loading = true;
                                        fetch('{{ route('admin.rules.toggle', $rule) }}', {
                                            method: 'PATCH',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        }).then(res => res.json()).then(data => {
                                            this.isActive = data.is_active;
                                            this.loading = false;
                                        }).catch(() => this.loading = false);
                                    }
                                }">
                                <button @click="toggleStatus" 
                                        :disabled="loading"
                                        :class="isActive ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-200'"
                                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-colors w-20 text-center shadow-sm border">
                                    <span x-show="!loading" x-text="isActive ? 'AKTIF' : 'MATI'"></span>
                                    <span x-show="loading" class="animate-pulse">...</span>
                                </button>
                            </div>
                            
                            <div class="flex space-x-3 text-sm">
                                <a href="{{ route('admin.rules.edit', $rule) }}" class="text-blue-600 font-medium">Edit</a>
                                <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 font-medium">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">Belum ada aturan</div>
                @endforelse
            </div>
        </div>

        <!-- Import Section as a nice card -->
        <div class="glass-panel p-6 md:p-8 rounded-[2.5rem] shadow-[0_8px_32px_rgba(0,0,0,0.04)] max-w-xl">
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <div class="flex-shrink-0 bg-white/60 p-4 rounded-[1.25rem] text-indigo-600 shadow-sm border border-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
                <div class="flex-1 w-full">
                    <h3 class="text-xl font-extrabold text-slate-800">Impor Aturan (Format JSON)</h3>
                    <p class="text-sm text-slate-500 mb-5 mt-1 font-medium">Unggah file JSON untuk memasukkan banyak aturan SEO sekaligus.</p>
                    
                    <form action="{{ route('admin.rules.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5" x-data="{ fileName: '' }">
                        @csrf
                        <div class="flex items-center justify-center w-full relative">
                            <label for="dropzone-file" :class="fileName ? 'border-green-400 bg-green-50/50' : 'border-indigo-200/60 bg-white/30'" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-[1.5rem] cursor-pointer backdrop-blur-sm hover:bg-white/60 transition-all duration-300">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <template x-if="!fileName">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-8 h-8 mb-2 text-indigo-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                            </svg>
                                            <p class="mb-1 text-sm text-slate-600"><span class="font-bold">Klik untuk unggah</span> atau seret file ke sini</p>
                                            <p class="text-xs text-slate-400 font-medium">Hanya menerima file JSON</p>
                                        </div>
                                    </template>
                                    <template x-if="fileName">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-8 h-8 mb-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <p class="text-sm font-bold text-green-700" x-text="fileName"></p>
                                            <p class="text-xs text-green-600 mt-1">File siap diunggah</p>
                                        </div>
                                    </template>
                                </div>
                                <input id="dropzone-file" type="file" name="json_file" accept=".json" required class="hidden" @change="fileName = $event.target.files[0].name" />
                            </label>
                        </div>
                        <button type="submit" :disabled="!fileName" :class="!fileName ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-1 hover:shadow-xl'" class="w-full bg-slate-800 text-white font-bold py-3 px-4 rounded-2xl shadow-lg transition-all duration-300">
                            Unggah & Simpan Aturan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
