<x-admin-layout>
    <x-slot name="title">Manajemen Aturan SEO</x-slot>
    <x-slot name="header">Manajemen Aturan</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Aturan Pengecekan SEO</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">Kelola semua aturan yang digunakan untuk mengecek landing page.</p>
            </div>
            <div class="flex w-full md:w-auto">
                <a href="{{ route('admin.rules.create') }}" class="w-full md:w-auto justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 md:py-2.5 px-6 rounded-[1.25rem] shadow-lg shadow-indigo-600/20 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-600/30 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Aturan Baru
                </a>
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
                    
                    <form action="{{ route('admin.rules.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-indigo-200/60 border-dashed rounded-[1.5rem] cursor-pointer bg-white/30 backdrop-blur-sm hover:bg-white/60 transition-all duration-300">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-indigo-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-1 text-sm text-slate-600"><span class="font-bold">Klik untuk unggah</span> atau seret file ke sini</p>
                                    <p class="text-xs text-slate-400 font-medium">Hanya menerima file JSON</p>
                                </div>
                                <input id="dropzone-file" type="file" name="json_file" accept=".json" required class="hidden" />
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-4 rounded-2xl shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                            Unggah & Simpan Aturan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
