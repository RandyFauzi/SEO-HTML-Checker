<?php
$f = 'resources/views/admin/rules/index.blade.php';
$c = file_get_contents($f);

$replace = <<<'EOT'
<div class="mb-4">
    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi / Tujuan Rule</label>
    <textarea name="description" x-model="formData.description" rows="2" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors" placeholder="Penjelasan mengenai rule ini digunakan untuk mengecek apa..."></textarea>
</div>
EOT;

if (strpos($c, 'name="description"') === false) {
    // Add before the Name input in the Alpine form
    $c = preg_replace('/(<div class="mb-4">\s*<label[^>]*>Nama Aturan)/i', $replace . "\n\n$1", $c);
    file_put_contents($f, $c);
    echo 'Added description to index slideover form';
} else {
    echo 'Already has description input in index form';
}
