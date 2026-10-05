<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

$beautifulButton1 = <<<'HTML'
                    <button type="submit" :disabled="loading" 
                        class="mt-4 group relative inline-flex w-full items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.01] active:scale-[0.99] shadow-[0_4px_20px_rgba(99,102,241,0.2)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.35)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-r from-sky-400 via-indigo-500 to-rose-400 p-[1px]"></span>
                        <span class="relative flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-bold text-white transition-all duration-300 overflow-hidden">
                            <span class="absolute -top-2 right-20 h-12 w-16 rounded-full bg-indigo-500/60 blur-md transition-all duration-500 group-hover:scale-125"></span>
                            <span class="absolute -bottom-2 right-4 h-10 w-12 rounded-full bg-rose-400/50 blur-md"></span>
                            
                            <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white relative z-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            
                            <span class="relative z-10 tracking-tight flex items-center gap-2">
                                <svg x-show="!loading" class="w-5 h-5 text-sky-300 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                <span x-text="loading ? 'AI Sedang Bekerja...' : 'Jalankan Manual'"></span>
                            </span>
                        </span>
                    </button>
HTML;

$beautifulButton2 = <<<'HTML'
                    <button type="submit" :disabled="loading" 
                        class="mt-4 group relative inline-flex w-full items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.01] active:scale-[0.99] shadow-[0_4px_20px_rgba(99,102,241,0.2)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.35)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-r from-sky-400 via-indigo-500 to-rose-400 p-[1px]"></span>
                        <span class="relative flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-bold text-white transition-all duration-300 overflow-hidden">
                            <span class="absolute -top-2 right-20 h-12 w-16 rounded-full bg-indigo-500/60 blur-md transition-all duration-500 group-hover:scale-125"></span>
                            <span class="absolute -bottom-2 right-4 h-10 w-12 rounded-full bg-rose-400/50 blur-md"></span>
                            
                            <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white relative z-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            
                            <span class="relative z-10 tracking-tight flex items-center gap-2">
                                <svg x-show="!loading" class="w-5 h-5 text-sky-300 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span x-text="loading ? 'AI Sedang Bekerja...' : 'Generate (Auto-Pilot)'"></span>
                            </span>
                        </span>
                    </button>
HTML;


$content = preg_replace('/<button type="submit" :disabled="loading" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition-colors">.*?<\/button>/s', $beautifulButton1, $content, 1);

$content = preg_replace('/<button type="submit" :disabled="loading" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition-colors">.*?<\/button>/s', $beautifulButton2, $content, 1);


file_put_contents($path, $content);
echo "Buttons redesigned.";
