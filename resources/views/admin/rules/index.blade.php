<x-admin-layout>
    <x-slot name="title">Manajemen Aturan SEO</x-slot>
    <x-slot name="header">Manajemen Aturan</x-slot>

    <div class="max-w-7xl mx-auto"
        x-data="{ 
            showAiModal: false, 
            showImportModal: false,
            importMode: 'file',
            dropdownOpen: false,
            aiPrompt: '', 
            aiLoading: false, 
            aiResult: null, 
            aiError: null,
            showToast: false,
            toastMessage: '',
            confirmModal: {
                show: false,
                title: '',
                message: '',
                confirmText: 'Ya, Lanjutkan',
                targetForm: null,
                isSubmitting: false
            },
            openConfirm(title, message, formElement, confirmText = 'Ya, Lanjutkan') {
                this.confirmModal.title = title;
                this.confirmModal.message = message;
                this.confirmModal.targetForm = formElement;
                this.confirmModal.confirmText = confirmText;
                this.confirmModal.isSubmitting = false;
                this.confirmModal.show = true;
            },
            executeConfirm() {
                if (this.confirmModal.targetForm) {
                    this.confirmModal.isSubmitting = true;
                    this.confirmModal.targetForm.submit();
                }
            },
            generateAi() { 
                this.aiLoading = true; this.aiError = null; 
                fetch('{{ route('admin.rules.ai-generate') }}', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, 
                    body: JSON.stringify({ prompt: this.aiPrompt }) 
                })
                .then(res => res.json())
                .then(data => { this.aiLoading = false; if(data.success) { this.aiResult = data.data; } else { this.aiError = data.message; } })
                .catch(e => { this.aiLoading = false; this.aiError = 'Terjadi kesalahan jaringan.'; }); 
            } 
        }">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">

            <!-- Toast Notification -->
            <div x-show="showToast" x-transition.opacity.duration.300ms class="fixed top-6 right-6 bg-slate-800 text-white px-5 py-3 rounded-xl shadow-2xl z-[100] flex items-center gap-3 border border-slate-700" style="display: none;">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="toastMessage" class="text-sm font-medium"></span>
            </div>
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Aturan Pengecekan SEO</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">Kelola semua aturan yang digunakan untuk mengecek landing page.</p>
            </div>
            <div class="flex flex-col md:flex-row w-full md:w-auto gap-3 items-stretch md:items-center">
                @if($rules->count() > 0)
                <form action="{{ route('admin.rules.destroyAll') }}" method="POST" class="w-full md:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="button" 
                        @click="openConfirm('Hapus Semua Aturan SEO?', 'Peringatan: Aksi ini akan menghapus semua aturan SEO milik Anda secara permanen. Anda yakin ingin melanjutkannya?', $event.currentTarget.closest('form'), 'Ya, Hapus Semua')"
                        class="w-full md:w-auto justify-center bg-red-50 hover:bg-red-500 text-red-600 hover:text-white font-bold py-3 md:py-2.5 px-6 rounded-full shadow-sm hover:-translate-y-1 hover:shadow-md transition-all border border-red-100 hover:border-red-500 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Semua
                    </button>
                </form>
                @endif

                <!-- Aurora Frosted Glass Button: Create Rules With AI -->
                <button type="button" 
                    @click="showAiModal = true" 
                    class="group relative inline-flex items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.22)] w-full md:w-auto">
                    
                    <!-- Glass Outline Highlight -->
                    <span class="absolute inset-0 rounded-full bg-gradient-to-b from-white/90 via-white/40 to-white/70 p-[1px]"></span>

                    <!-- Frosted Glass Pill Body -->
                    <span class="relative flex w-full items-center justify-center gap-2.5 rounded-full bg-white/75 backdrop-blur-xl px-6 py-3 md:py-2.5 text-sm font-bold text-slate-800 transition-all duration-300 group-hover:bg-white/85 group-hover:text-slate-900 border border-white/70 shadow-[inset_0_1px_2px_rgba(255,255,255,1),inset_0_-1px_2px_rgba(0,0,0,0.03)] overflow-hidden">
                        
                        <!-- Aurora Glow 1: Sky / Cyan Light Diffusion -->
                        <span class="absolute -top-3 right-6 h-12 w-16 rounded-full bg-sky-400/60 blur-lg transition-all duration-500 group-hover:h-16 group-hover:w-20 group-hover:bg-sky-400/80 group-hover:scale-110"></span>
                        
                        <!-- Aurora Glow 2: Deep Indigo / Violet Diffusion -->
                        <span class="absolute -top-2 right-14 h-10 w-14 rounded-full bg-indigo-500/50 blur-md transition-all duration-500 group-hover:scale-125 group-hover:bg-indigo-500/70"></span>
                        
                        <!-- Aurora Glow 3: Warm Peach / Rose Accent -->
                        <span class="absolute -bottom-2 right-3 h-8 w-12 rounded-full bg-rose-400/50 blur-md transition-all duration-500 group-hover:scale-125"></span>

                        <!-- AI Sparkle Icon -->
                        <svg class="relative z-10 h-4 w-4 text-indigo-600 transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L14.2 7.8L20 10L14.2 12.2L12 18L9.8 12.2L4 10L9.8 7.8L12 2Z" />
                            <path d="M19 16L20.1 18.9L23 20L20.1 21.1L19 24L17.9 21.1L15 20L17.9 18.9L19 16Z" opacity="0.75"/>
                        </svg>

                        <!-- Text -->
                        <span class="relative z-10 tracking-tight text-slate-800 font-extrabold group-hover:text-indigo-950 transition-colors">Create Rules With AI</span>
                    </span>
                </button>
                
                <div class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="w-full md:w-auto justify-center bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 md:py-2.5 px-6 rounded-full shadow-lg shadow-slate-800/20 transition-all hover:-translate-y-1 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Aturan
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="dropdownOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] bg-white ring-1 ring-black ring-opacity-5 z-50 overflow-hidden divide-y divide-gray-100 border border-slate-100" 
                         style="display: none;">
                        <div class="py-1">
                            <button @click="showAiModal = true; dropdownOpen = false" class="group flex items-center w-full px-4 py-3 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 mr-3 group-hover:bg-indigo-200 group-hover:text-indigo-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </span>
                                <span class="font-semibold">Buat dengan AI</span>
                            </button>
                            <a href="{{ route('admin.rules.create') }}" class="group flex items-center w-full px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 mr-3 group-hover:bg-slate-200 group-hover:text-slate-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                </span>
                                <span class="font-semibold">Buat Manual</span>
                            </a>
                            <button @click="showImportModal = true; dropdownOpen = false" class="group flex items-center w-full px-4 py-3 text-sm text-slate-700 hover:bg-green-50 hover:text-green-700 transition-colors">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 text-green-600 mr-3 group-hover:bg-green-200 group-hover:text-green-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                </span>
                                <span class="font-semibold">Impor dari JSON</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import JSON Modal -->
            <div x-show="showImportModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showImportModal = false" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-100">
                        <div class="bg-gradient-to-br from-indigo-50 to-white px-6 pt-8 pb-6 sm:px-8 sm:pb-8 relative">
                            <button @click="showImportModal = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 sm:mx-0 sm:h-12 sm:w-12 shadow-inner border border-indigo-200">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                </div>
                                <div class="mt-4 text-center sm:mt-0 sm:ml-5 sm:text-left w-full">
                                    <h3 class="text-xl leading-6 font-extrabold text-slate-900" id="modal-title">Impor Aturan JSON</h3>
                                    
                                    <form action="{{ route('admin.rules.import') }}" method="POST" enctype="multipart/form-data" class="mt-4" x-data="{ fileName: '' }">
                                        @csrf
                                        
                                        <!-- Tabs -->
                                        <div class="flex space-x-1 mb-4 bg-slate-100 p-1 rounded-xl">
                                            <button type="button" @click="importMode = 'file'" :class="importMode === 'file' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'" class="w-1/2 py-2 text-sm font-medium rounded-lg transition-all">Unggah File</button>
                                            <button type="button" @click="importMode = 'text'" :class="importMode === 'text' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'" class="w-1/2 py-2 text-sm font-medium rounded-lg transition-all">Tempel Teks</button>
                                        </div>

                                        <!-- Mode: File Upload -->
                                        <div x-show="importMode === 'file'" class="flex items-center justify-center w-full relative mb-5">
                                            <label for="dropzone-file" :class="fileName ? 'border-green-400 bg-green-50/50' : 'border-indigo-200/60 bg-white/30'" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-[1.5rem] cursor-pointer backdrop-blur-sm hover:bg-white/60 transition-all duration-300">
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <template x-if="!fileName">
                                                        <div class="flex flex-col items-center">
                                                            <svg class="w-8 h-8 mb-2 text-indigo-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                                            </svg>
                                                            <p class="mb-1 text-sm text-slate-600"><span class="font-bold">Klik untuk unggah</span></p>
                                                            <p class="text-xs text-slate-400 font-medium">Hanya file JSON</p>
                                                        </div>
                                                    </template>
                                                    <template x-if="fileName">
                                                        <div class="flex flex-col items-center">
                                                            <svg class="w-8 h-8 mb-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            <p class="text-sm font-bold text-green-700" x-text="fileName"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                                <input id="dropzone-file" type="file" name="json_file" accept=".json" class="hidden" @change="fileName = $event.target.files[0].name" />
                                            </label>
                                        </div>

                                        <!-- Mode: Text Paste -->
                                        <div x-show="importMode === 'text'" class="mb-5">
                                            <textarea name="json_text" rows="5" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-sm font-mono text-slate-700 focus:border-indigo-500 focus:ring-0 focus:outline-none transition-colors" placeholder='[{"name": "Aturan Baru", "rule_type": "element_exists"...}]'></textarea>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all flex items-center">
                                                Simpan Aturan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Sidebar (Slide-over) -->
            <div x-show="showAiModal" style="display: none;" class="fixed inset-0 z-[100] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
                <div class="absolute inset-0 overflow-hidden">
                    <!-- Backdrop -->
                    <div x-show="showAiModal" 
                         x-transition.opacity.duration.300ms 
                         class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                         @click="showAiModal = false" aria-hidden="true"></div>

                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                        <!-- Slide-over panel -->
                        <div x-show="showAiModal" 
                             x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                             x-transition:enter-start="translate-x-full" 
                             x-transition:enter-end="translate-x-0" 
                             x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                             x-transition:leave-start="translate-x-0" 
                             x-transition:leave-end="translate-x-full" 
                             class="pointer-events-auto w-screen max-w-md flex h-full flex-col overflow-y-scroll bg-white shadow-[0_0_40px_rgba(0,0,0,0.1)] border-l border-slate-200">
                             
                            <!-- Header -->
                            <div class="bg-gradient-to-br from-purple-50 to-white px-6 py-6 border-b border-purple-100 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-purple-100 text-purple-600 shadow-inner border border-purple-200">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    </div>
                                    <h3 class="text-lg font-extrabold text-slate-900" id="slide-over-title">AI Rule Builder</h3>
                                </div>
                                <button @click="showAiModal = false" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-100">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="p-6 flex-1 bg-slate-50/50">
                                <p class="text-sm text-slate-500 mb-6">Jelaskan aturan SEO yang Anda inginkan dengan bahasa sehari-hari. AI akan menerjemahkannya menjadi format sistem secara otomatis.</p>
                                
                                <template x-if="!aiResult">
                                    <div>
                                        <textarea x-model="aiPrompt" rows="5" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none transition-colors" placeholder="Contoh: 'Tolong pastikan semua gambar punya atribut alt, kalau nggak ada tampilkan pesan error yang bilang gambar harus punya deskripsi'"></textarea>
                                        
                                        <div x-show="aiError" class="mt-3 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-100 font-medium" x-text="aiError"></div>
                                        
                                        <div class="mt-5">
                                            <button @click="generateAi()" :disabled="aiLoading || aiPrompt.trim() === ''" 
                                                class="group relative inline-flex w-full items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.01] active:scale-[0.99] shadow-[0_4px_20px_rgba(99,102,241,0.2)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.35)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                                                <span class="absolute inset-0 rounded-full bg-gradient-to-r from-sky-400 via-indigo-500 to-rose-400 p-[1px]"></span>
                                                <span class="relative flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-bold text-white transition-all duration-300 overflow-hidden">
                                                    <!-- Internal Aurora Glow (from image 1 & 2) -->
                                                    <span class="absolute -top-3 right-10 h-14 w-20 rounded-full bg-sky-400/50 blur-lg transition-all duration-500 group-hover:scale-125"></span>
                                                    <span class="absolute -top-2 right-20 h-12 w-16 rounded-full bg-indigo-500/60 blur-md transition-all duration-500 group-hover:scale-125"></span>
                                                    <span class="absolute -bottom-2 right-4 h-10 w-12 rounded-full bg-rose-400/50 blur-md"></span>
                                                    
                                                    <svg x-show="aiLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white relative z-10" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    <span class="relative z-10 tracking-tight flex items-center gap-2">
                                                        <svg x-show="!aiLoading" class="h-4 w-4 text-sky-300" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M12 2L14.2 7.8L20 10L14.2 12.2L12 18L9.8 12.2L4 10L9.8 7.8L12 2Z" />
                                                        </svg>
                                                        <span x-text="aiLoading ? 'Sedang meracik...' : 'Create Rules With AI'"></span>
                                                    </span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="aiResult">
                                    <div class="space-y-5" x-data="{ showJson: false }">
                                        <div class="bg-indigo-50 text-indigo-900 p-4 rounded-xl border border-indigo-100 text-sm font-medium flex items-start shadow-sm">
                                            <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div x-text="aiResult.explanation" class="leading-relaxed"></div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <button @click="showJson = !showJson" class="text-xs text-slate-500 hover:text-indigo-600 underline font-medium transition-colors">Lihat Detail Teknis (JSON)</button>
                                        </div>
                                        
                                        <div x-show="showJson" style="display:none;" class="relative group mt-2">
                                            <textarea readonly rows="8" class="w-full font-mono text-xs bg-slate-900 text-green-400 border border-slate-700 rounded-xl px-4 py-3 shadow-inner focus:outline-none" x-text="JSON.stringify([aiResult.rule], null, 2)"></textarea>
                                        </div>
                                        
                                        <div class="mt-6 flex flex-col gap-3">
                                            <button @click="
                                                const f = document.createElement('form');
                                                f.method = 'POST';
                                                f.action = '{{ route('admin.rules.import') }}';
                                                const c = document.createElement('input'); c.name = '_token'; c.value = '{{ csrf_token() }}'; c.type = 'hidden';
                                                const t = document.createElement('input'); t.name = 'json_text'; t.value = JSON.stringify([aiResult.rule]); t.type = 'hidden';
                                                f.appendChild(c); f.appendChild(t);
                                                document.body.appendChild(f);
                                                f.submit();
                                            " class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                                Simpan & Terapkan Aturan
                                            </button>
                                            <button @click="aiResult = null" class="w-full bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl transition-all text-sm shadow-sm">Tulis Ulang Prompt</button>
                                        </div>
                                    </div>
                                </template>
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
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-white/40 border-b border-white/60 text-slate-500 uppercase tracking-wider text-xs font-bold">
                        <tr>
                            <th class="px-6 py-5 whitespace-nowrap">Aturan</th>
                            <th class="px-6 py-5 whitespace-nowrap">Tipe</th>
                            <th class="px-6 py-5 whitespace-nowrap">Kategori</th>
                            <th class="px-6 py-5 whitespace-nowrap">Status</th>
                            <th class="px-6 py-5 text-center whitespace-nowrap">Aktif</th>
                            <th class="px-6 py-5 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/40 text-slate-700">
                        @forelse($rules as $rule)
                            <tr class="hover:bg-white/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 mb-1 max-w-xs break-words">{{ $rule->name }}</div>
                                    <div class="text-slate-500 font-mono text-[10px] bg-slate-100/50 inline-block px-1.5 py-0.5 rounded border border-slate-200/50">{{ $rule->code ?: '#'.$rule->id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-medium border border-gray-200">{{ $rule->rule_type->value ?? $rule->rule_type }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1 items-start">
                                        <span class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-xs border border-indigo-100">{{ $rule->category }}</span>
                                        <span class="text-slate-600 bg-slate-100 px-2 py-1 rounded text-[10px] font-medium border border-slate-200">
                                            {{ $rule->target_page === 'lp' ? 'Landing Page' : ($rule->target_page === 'amp' ? 'AMP' : 'LP & AMP') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('admin.rules.edit', $rule) }}" class="text-blue-600 hover:text-blue-900 font-medium transition-colors">Edit</a>
                                    <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                            @click="openConfirm('Hapus Aturan?', 'Apakah Anda yakin ingin menghapus aturan &quot;{{ addslashes($rule->name) }}&quot;?', $event.currentTarget.closest('form'), 'Ya, Hapus')"
                                            class="text-red-500 hover:text-red-700 font-medium transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
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
                            <span class="text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200">{{ $rule->target_page === 'lp' ? 'LP' : ($rule->target_page === 'amp' ? 'AMP' : 'LP & AMP') }}</span>
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
                                <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                        @click="openConfirm('Hapus Aturan?', 'Apakah Anda yakin ingin menghapus aturan &quot;{{ addslashes($rule->name) }}&quot;?', $event.currentTarget.closest('form'), 'Ya, Hapus')"
                                        class="text-red-500 font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">Belum ada aturan</div>
                @endforelse
            </div>
        </div>

        <!-- Custom Confirmation Modal (Modern Glassmorphic / Soft Neumorphic) -->
        <div x-show="confirmModal.show" 
             style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="confirm-modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="if(confirmModal.show && !confirmModal.isSubmitting) confirmModal.show = false">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <!-- Backdrop -->
                <div x-show="confirmModal.show" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                     @click="if(!confirmModal.isSubmitting) confirmModal.show = false" 
                     aria-hidden="true"></div>

                <!-- Modal Dialog Card -->
                <div x-show="confirmModal.show" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative transform overflow-hidden rounded-[2.5rem] bg-white/95 backdrop-blur-2xl text-left shadow-[0_25px_60px_-15px_rgba(0,0,0,0.25)] transition-all sm:my-8 w-full sm:max-w-md border border-white/80 p-6 sm:p-8">
                    
                    <!-- Ambient Glow Orbs -->
                    <div class="absolute -top-16 -right-16 w-36 h-36 bg-red-100/70 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-16 -left-16 w-36 h-36 bg-amber-100/60 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Close Button -->
                    <button type="button" 
                            @click="confirmModal.show = false" 
                            :disabled="confirmModal.isSubmitting"
                            class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 p-2 rounded-full hover:bg-slate-100/80 transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Icon -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 border border-red-100 text-red-500 shadow-inner mb-5 relative group">
                        <div class="absolute inset-0 rounded-2xl bg-red-400/20 animate-ping opacity-30"></div>
                        <svg class="h-8 w-8 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    <!-- Title & Message -->
                    <div class="text-center">
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight" id="confirm-modal-title" x-text="confirmModal.title"></h3>
                        <p class="mt-3 text-sm text-slate-500 font-medium leading-relaxed px-1" x-text="confirmModal.message"></p>
                    </div>

                    <!-- Warning Callout -->
                    <div class="mt-5 p-3.5 rounded-2xl bg-amber-50/90 border border-amber-200/70 text-amber-900 text-xs font-semibold flex items-center gap-2.5 shadow-sm">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Tindakan ini tidak dapat dibatalkan setelah diproses.</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
                        <button type="button" 
                                @click="confirmModal.show = false" 
                                :disabled="confirmModal.isSubmitting"
                                class="w-full sm:w-1/2 py-3 px-5 rounded-[1.25rem] bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition-all focus:outline-none">
                            Batal
                        </button>
                        <button type="button" 
                                @click="executeConfirm()" 
                                :disabled="confirmModal.isSubmitting"
                                class="w-full sm:w-1/2 py-3 px-5 rounded-[1.25rem] bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold text-sm shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 focus:outline-none disabled:opacity-75 disabled:cursor-not-allowed">
                            <template x-if="confirmModal.isSubmitting">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="confirmModal.confirmText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-admin-layout>
