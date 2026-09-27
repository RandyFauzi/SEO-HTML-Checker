<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$run = App\Models\AuditRun::first();
if ($run) {
    echo $run->getRouteKey();
} else {
    echo "No run found";
}
