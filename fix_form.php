<?php
$f = 'resources/views/admin/rules/form.blade.php';
$c = file_get_contents($f);

$replace = <<<'EOT'
<div class="mb-4">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi & Tujuan Rule</label>
    <textarea name="description" id="description" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: Aturan ini bertujuan untuk memastikan ...">{{ old('description', $rule->description ?? '') }}</textarea>
</div>
EOT;

$c = preg_replace('/(<div class="mb-4">\s*<label for="description".+?<\/div>\s*){2,}/is', '$1', $c); // cleanup previous duplicates
if (strpos($c, 'name="description"') === false) {
    $c = preg_replace('/(<div class="mb-4">\s*<label for="name")/i', $replace . "\n\n$1", $c);
}
file_put_contents($f, $c);
