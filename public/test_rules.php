<?php
require __DIR__.'/seo-app/vendor/autoload.php';
$app = require_once __DIR__.'/seo-app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "User: {$user->email}<br>";
    echo "Total Rules: " . $user->rules()->count() . "<br>";
    $activeAmp = $user->rules()->where('is_active', true)
            ->whereIn('rule_type', ['compare_amp', 'alternate'])
            ->count();
    echo "Active AMP Rules: " . $activeAmp . "<br>";
    if ($activeAmp == 0) {
        echo "Rules details:<br>";
        foreach ($user->rules()->whereIn('rule_type', ['compare_amp', 'alternate'])->get() as $r) {
            echo "- {$r->code} : is_active = {$r->is_active}<br>";
        }
    }
    echo "<hr>";
}
