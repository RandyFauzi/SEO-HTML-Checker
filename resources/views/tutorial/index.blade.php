<x-admin-layout>
    <x-slot name="title">Panduan Penggunaan</x-slot>
    <x-slot name="header">Panduan Penggunaan</x-slot>

    <div class="relative z-10 mx-auto space-y-8">
        <!-- Intro (Main Glass Card) -->
        <div class="bg-white/60 backdrop-blur-2xl border border-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.06)] rounded-[2rem] p-8 md:p-10 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-indigo-400 to-purple-400 rounded-full opacity-20 blur-2xl"></div>
            
            <h2 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-500 mb-4 tracking-tight">
                Selamat Datang di SEO Checker 👋
            </h2>
            <p class="text-slate-600 leading-relaxed text-lg font-medium">
                Tool ini membantu Anda menganalisis kesehatan SEO website secara otomatis. Cukup masukkan URL, dan sistem akan memeriksa puluhan aturan SEO standar dalam hitungan detik.
            </p>
        </div>

        <!-- Steps Grid -->
        <div class="grid gap-6">
            <!-- Step 1 -->
            <div class="group bg-white/50 hover:bg-white/70 backdrop-blur-xl border border-white/80 shadow-[0_4px_20px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 sm:p-8 flex flex-col sm:flex-row items-start gap-6 transition-all duration-300 hover:-translate-y-1">
                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-tr from-blue-500 to-cyan-400 text-white font-bold text-2xl rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30 rotate-3 group-hover:rotate-0 transition-transform">1</div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Masukkan URL Website</h3>
                    <p class="text-slate-600 mb-5 leading-relaxed">Buka menu <strong class="text-slate-800">Run Checker</strong>, lalu ketik atau <i>paste</i> URL halaman website yang ingin Anda cek. Anda bisa memasukkan hingga 10 URL sekaligus.</p>
                    <a href="{{ route('seo.index') }}" class="inline-flex items-center px-5 py-2.5 bg-white/80 border border-white shadow-sm text-sm font-bold text-blue-600 rounded-xl hover:bg-blue-50 hover:scale-105 transition-all">
                        Ke Halaman Checker &rarr;
                    </a>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="group bg-white/50 hover:bg-white/70 backdrop-blur-xl border border-white/80 shadow-[0_4px_20px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 sm:p-8 flex flex-col sm:flex-row items-start gap-6 transition-all duration-300 hover:-translate-y-1">
                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-tr from-indigo-500 to-purple-500 text-white font-bold text-2xl rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/30 -rotate-3 group-hover:rotate-0 transition-transform">2</div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Tunggu Proses Analisis</h3>
                    <p class="text-slate-600 leading-relaxed">Sistem akan secara aman mengunduh kode HTML dari website Anda dan mencocokkannya dengan aturan SEO yang ada di database. Proses ini hanya memakan waktu beberapa detik saja (secara asinkronus).</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="group bg-white/50 hover:bg-white/70 backdrop-blur-xl border border-white/80 shadow-[0_4px_20px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 sm:p-8 flex flex-col sm:flex-row items-start gap-6 transition-all duration-300 hover:-translate-y-1">
                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-tr from-emerald-400 to-teal-500 text-white font-bold text-2xl rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30 rotate-3 group-hover:rotate-0 transition-transform">3</div>
                <div class="w-full">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Baca Hasil & Lakukan Perbaikan</h3>
                    <div class="grid sm:grid-cols-3 gap-4 mb-5">
                        <div class="bg-white/60 p-4 rounded-2xl border border-white shadow-sm flex flex-col items-center text-center">
                            <span class="w-4 h-4 bg-emerald-500 rounded-full mb-2 shadow-[0_0_10px_rgba(16,185,129,0.8)]"></span> 
                            <span class="text-slate-800 font-bold mb-1">Passed</span>
                            <span class="text-xs text-slate-500">Sudah benar & sesuai standar SEO.</span>
                        </div>
                        <div class="bg-white/60 p-4 rounded-2xl border border-white shadow-sm flex flex-col items-center text-center">
                            <span class="w-4 h-4 bg-amber-500 rounded-full mb-2 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span> 
                            <span class="text-slate-800 font-bold mb-1">Warning</span>
                            <span class="text-xs text-slate-500">Kurang optimal, sebaiknya diperbaiki.</span>
                        </div>
                        <div class="bg-white/60 p-4 rounded-2xl border border-white shadow-sm flex flex-col items-center text-center">
                            <span class="w-4 h-4 bg-red-500 rounded-full mb-2 shadow-[0_0_10px_rgba(239,68,68,0.8)]"></span> 
                            <span class="text-slate-800 font-bold mb-1">Error</span>
                            <span class="text-xs text-slate-500">Kesalahan fatal, wajib diperbaiki!</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 p-4 rounded-2xl border border-blue-100/50 text-sm text-blue-800 font-medium flex gap-3 items-center backdrop-blur-sm">
                        <span class="text-xl">💡</span>
                        <p>Tips: Klik tombol "Detail" pada setiap aturan yang error untuk melihat potongan kode HTML asli yang salah.</p>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="group bg-white/50 hover:bg-white/70 backdrop-blur-xl border border-white/80 shadow-[0_4px_20px_rgb(0,0,0,0.04)] rounded-[2rem] p-6 sm:p-8 flex flex-col sm:flex-row items-start gap-6 transition-all duration-300 hover:-translate-y-1">
                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-tr from-orange-400 to-pink-500 text-white font-bold text-2xl rounded-2xl flex items-center justify-center shadow-lg shadow-pink-500/30 -rotate-3 group-hover:rotate-0 transition-transform">4</div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Kustomisasi Aturan (Khusus Admin)</h3>
                    <p class="text-slate-600 mb-5 leading-relaxed">Tidak setuju dengan standar SEO bawaan? Anda bebas mengubah, menonaktifkan, atau membuat aturan SEO baru melalui panel <strong class="text-slate-800">SEO Rules</strong> tanpa coding sedikitpun!</p>
                    <a href="{{ route('admin.rules.index') }}" class="inline-flex items-center px-5 py-2.5 bg-white/80 border border-white shadow-sm text-sm font-bold text-pink-600 rounded-xl hover:bg-pink-50 hover:scale-105 transition-all">
                        Kelola Aturan SEO &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
