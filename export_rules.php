<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rules = App\Models\SeoRule::all();
file_put_contents('rules_export.json', $rules->toJson(JSON_PRETTY_PRINT));
echo "Exported " . $rules->count() . " rules.";
