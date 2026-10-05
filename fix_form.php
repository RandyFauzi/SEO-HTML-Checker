<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

// 1. Change the wrapper grid from `grid grid-cols-4 gap-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100`
// wait, let's find the exact wrapper class. Let's just update all inputs to bg-white.
$content = str_replace('bg-slate-50 hover:bg-white', 'bg-white', $content);
$content = str_replace('bg-slate-50 focus:bg-white', 'bg-white', $content);

// 2. Change the Tambah Template button
$oldAddBtn = '<button type="button" @click="showModal = true" class="mt-2 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-1.5 rounded-lg w-full transition-colors">+ Tambah Template Baru ke DB</button>';
$newAddBtn = '<button type="button" @click="showModal = true" class="mt-2 text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 hover:text-indigo-800 border border-indigo-100 shadow-sm px-3 py-2 rounded-lg w-full transition-colors flex items-center justify-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>Tambah Template Baru ke DB</button>';
$content = str_replace($oldAddBtn, $newAddBtn, $content);

// 3. Improve the dropdown button
$oldDropdown = "bg-slate-50 hover:bg-white transition-all text-sm flex justify-between items-center text-left cursor-pointer";
$newDropdown = "bg-white transition-all text-sm flex justify-between items-center text-left cursor-pointer";
$content = str_replace($oldDropdown, $newDropdown, $content);

// 4. Update the fields wrapper if it exists (e.g. grid grid-cols-4 gap-4 p-4 bg-slate-50/50)
$content = preg_replace('/<div class="grid grid-cols-2 gap-4 p-4 bg-slate-50\/50 rounded-2xl border border-slate-100">/', '<div class="grid grid-cols-2 gap-4 p-5 bg-white/60 backdrop-blur-md rounded-2xl border border-slate-200 shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">', $content);

file_put_contents($path, $content);
echo "Form styling improved.";
