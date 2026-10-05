<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

$beautifulButton1 = <<<'HTML'
                    <button type="submit" :disabled="loading" 
                        class="group relative inline-flex w-full items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.01] active:scale-[0.99] shadow-[0_4px_20px_rgba(99,102,241,0.2)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.35)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-r from-sky-400 via-indigo-500 to-rose-400 p-[1px]"></span>
                        <span class="relative flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-bold text-white transition-all duration-300 overflow-hidden">
                            <span class="absolute -top-2 right-20 h-12 w-16 rounded-full bg-indigo-500/60 blur-md transition-all duration-500 group-hover:scale-125"></span>
                            <span class="absolute -bottom-2 right-4 h-10 w-12 rounded-full bg-rose-400/50 blur-md"></span>
                            
                            <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white relative z-10" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            
                            <span class="relative z-10 tracking-tight flex items-center gap-2">
                                <svg x-show="!loading" class="h-4 w-4 text-sky-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2L14.2 7.8L20 10L14.2 12.2L12 18L9.8 12.2L4 10L9.8 7.8L12 2Z" />
                                </svg>
                                <span x-text="loading ? 'AI Sedang Bekerja...' : 'Jalankan Manual'"></span>
                            </span>
                        </span>
                    </button>
HTML;

$beautifulButton2 = <<<'HTML'
                    <button type="submit" :disabled="loading" 
                        class="mt-2 group relative inline-flex w-full items-center justify-center overflow-hidden rounded-full p-[1px] transition-all duration-300 hover:scale-[1.01] active:scale-[0.99] shadow-[0_4px_20px_rgba(99,102,241,0.2)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.35)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-r from-sky-400 via-indigo-500 to-rose-400 p-[1px]"></span>
                        <span class="relative flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-bold text-white transition-all duration-300 overflow-hidden">
                            <span class="absolute -top-2 right-20 h-12 w-16 rounded-full bg-indigo-500/60 blur-md transition-all duration-500 group-hover:scale-125"></span>
                            <span class="absolute -bottom-2 right-4 h-10 w-12 rounded-full bg-rose-400/50 blur-md"></span>
                            
                            <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white relative z-10" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            
                            <span class="relative z-10 tracking-tight flex items-center gap-2">
                                <svg x-show="!loading" class="h-4 w-4 text-sky-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2L14.2 7.8L20 10L14.2 12.2L12 18L9.8 12.2L4 10L9.8 7.8L12 2Z" />
                                </svg>
                                <span x-text="loading ? 'Sedang Merakit & Mengeksekusi...' : 'Generate (Auto-Pilot)'"></span>
                            </span>
                        </span>
                    </button>
HTML;

// Replace Manual Button
$content = preg_replace('/<button type="submit" :disabled="loading" class="group relative inline-flex items-center justify-center overflow-hidden rounded-full p-\[1px\] transition-all duration-300 hover:scale-\[1.02\] active:scale-\[0.98\] shadow-\[0_4px_20px_rgba\(0,0,0,0.06\)\] hover:shadow-\[0_8px_30px_rgba\(99,102,241,0.22\)\] w-full">.*?<\/button>/s', $beautifulButton1, $content);

// Replace Auto-Pilot Button
$content = preg_replace('/<button type="submit" :disabled="loading" class="group relative inline-flex items-center justify-center overflow-hidden rounded-full p-\[1px\] transition-all duration-300 hover:scale-\[1.02\] active:scale-\[0.98\] shadow-sm w-full mt-2">.*?<\/button>/s', $beautifulButton2, $content);


file_put_contents($path, $content);
echo "AI Buttons applied successfully.";
