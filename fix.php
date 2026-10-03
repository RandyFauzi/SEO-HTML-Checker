<?php
$f = 'resources/views/admin/rules/index.blade.php';
$c = file_get_contents($f);
$replace = <<<EOT
<span class="font-semibold">Buat Manual</span>
                            </a>
                            <a href="{{ route('admin.rules.bank') }}" class="group flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition-colors border-b border-slate-100">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-600 mr-3 group-hover:bg-amber-200 group-hover:text-amber-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </span>
                                <span class="font-semibold">Bank Rules</span>
                            </a>
EOT;
$c = preg_replace('#<span class="font-semibold">Buat Manual</span>\s*</a>#i', $replace, $c);
file_put_contents($f, $c);
