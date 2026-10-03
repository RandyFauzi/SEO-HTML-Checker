<?php
$f = 'app/Http/Controllers/Admin/SeoRuleController.php';
$c = file_get_contents($f);
$c = str_replace("'name' => 'required|string|max:255',", "'name' => 'required|string|max:255',\n            'description' => 'nullable|string',", $c);
file_put_contents($f, $c);
