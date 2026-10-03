<?php
$f = 'resources/views/admin/rules/index.blade.php';
$c = file_get_contents($f);
$c = str_replace('<div class="font-bold text-gray-900 text-base leading-tight">{{ $rule->name }}</div>', '<div class="font-bold text-gray-900 text-base leading-tight mb-1">{{ $rule->name }}</div><div class="text-xs text-slate-500 mb-2 leading-relaxed">{{ $rule->description ?? \'Tidak ada deskripsi\' }}</div>', $c);
file_put_contents($f, $c);
