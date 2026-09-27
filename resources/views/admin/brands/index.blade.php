<x-admin-layout>
    <x-slot name="title">Manage Brands & GTAGs</x-slot>
    
    <div class="space-y-8 relative z-10">
        <!-- Header Section -->
        <div class="flex justify-between items-end mb-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Brands & GTAGs</h1>
                <p class="text-slate-500 mt-1">Kelola daftar brand dan Google Tag ID untuk keperluan validasi SEO otomatis.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50/80 backdrop-blur-sm border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm" role="alert">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="block sm:inline font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Add Form Card -->
        <div class="bg-white/70 backdrop-blur-2xl border border-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.06)] rounded-[2rem] p-6 md:p-8 relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-indigo-400 to-purple-400 rounded-full opacity-20 blur-2xl transition-all duration-500 group-hover:opacity-30 group-hover:scale-110"></div>
            
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 relative z-10">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambahkan Brand Baru
            </h3>
            
            <form action="{{ route('admin.brands.store') }}" method="POST" class="flex flex-col md:flex-row gap-5 items-end relative z-10">
                @csrf
                <div class="w-full md:w-2/5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Brand</label>
                    <input type="text" name="brand_name" class="w-full bg-white/60 border border-slate-200 rounded-xl px-4 py-3 shadow-inner focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 focus:bg-white transition-all text-slate-800 font-medium placeholder:text-slate-400" placeholder="Contoh: Toko Baju ABC" required>
                </div>
                <div class="w-full md:w-2/5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">GTAG ID</label>
                    <input type="text" name="gtag_id" class="w-full bg-white/60 border border-slate-200 rounded-xl px-4 py-3 shadow-inner focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 focus:bg-white transition-all text-slate-800 font-medium placeholder:text-slate-400" placeholder="Contoh: G-XXXXXXXXXX" required>
                </div>
                <div class="w-full md:w-1/5">
                    <button type="submit" class="w-full justify-center bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold py-3 px-6 rounded-xl shadow-md hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-300 flex items-center gap-2">
                        Simpan Brand
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white/70 backdrop-blur-2xl border border-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 md:p-8">
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Daftar Brand Tersimpan
            </h3>
            
            @if($brands->isEmpty())
                <div class="bg-slate-50/50 border border-slate-200/60 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-.293-1.025l-3.5-5.5A2 2 0 0016.5 2H12a2 2 0 00-2 2"></path></svg>
                    </div>
                    <p class="text-slate-600 font-medium">Belum ada brand yang ditambahkan.</p>
                    <p class="text-sm text-slate-500 mt-1">Tambahkan brand di atas agar sistem SEO bisa mulai memvalidasi GTAG di website Anda.</p>
                </div>
            @else
                <div class="overflow-hidden bg-white/50 border border-slate-200/60 rounded-2xl shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200/80">
                            <thead class="bg-slate-100/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Brand</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">GTAG ID</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/80">
                                @foreach($brands as $index => $brand)
                                <tr class="hover:bg-white/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $brand->brand_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-indigo-700 border border-indigo-200/50">
                                            {{ $brand->gtag_id }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus brand {{ $brand->brand_name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Hapus Brand">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
