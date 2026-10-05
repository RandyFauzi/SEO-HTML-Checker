<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

$beautifulButton1 = <<<'HTML'
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
HTML;

$beautifulButton2 = <<<'HTML'
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
HTML;

// Replace the dark buttons we just added with the light glowing buttons
$content = preg_replace('/<button type="submit" :disabled="loading"\s*class="[^"]*group relative inline-flex w-full items-center justify-center overflow-hidden rounded-full p-\[1px\] transition-all duration-300 hover:scale-\[1.01\] active:scale-\[0.99\][^"]*".*?<\/button>/s', $beautifulButton1, $content, 1);
$content = preg_replace('/<button type="submit" :disabled="loading"\s*class="[^"]*group relative inline-flex w-full items-center justify-center overflow-hidden rounded-full p-\[1px\] transition-all duration-300 hover:scale-\[1.01\] active:scale-\[0.99\][^"]*".*?<\/button>/s', $beautifulButton2, $content, 1);

file_put_contents($path, $content);
echo "Buttons redesigned back to light glassmorphism.";
