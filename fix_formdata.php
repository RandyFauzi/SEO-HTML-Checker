<?php
$f = 'resources/views/admin/rules/index.blade.php';
$c = file_get_contents($f);

// Initialize description in formData
$c = str_replace(
    'formData: { id: null, code: \'\', name: \'\',', 
    'formData: { id: null, code: \'\', name: \'\', description: \'\',', 
    $c
);

// Map AI response to formData
$c = str_replace(
    'this.formData.name = data.name || \'\';',
    "this.formData.name = data.name || '';\n                            this.formData.description = data.description || '';",
    $c
);

// Map Edit rule to formData
$c = str_replace(
    'name: rule.name,',
    "name: rule.name,\n                    description: rule.description,",
    $c
);

file_put_contents($f, $c);
echo 'Updated formData mapping in index.blade.php';
