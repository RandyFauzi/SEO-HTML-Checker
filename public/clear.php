<?php
$cacheDir = __DIR__ . "/seo-app/bootstrap/cache";
$files = ["routes-v7.php", "views", "config.php", "events.php", "packages.php", "services.php"];

echo "Membersihkan cache...<br>";

foreach(glob($cacheDir . "/*.php") as $file) {
    if(is_file($file) && basename($file) !== ".gitignore") {
        unlink($file);
        echo "Dihapus: " . basename($file) . "<br>";
    }
}

// Clear views
$viewsDir = __DIR__ . "/seo-app/storage/framework/views";
foreach(glob($viewsDir . "/*.php") as $file) {
    if(is_file($file)) {
        unlink($file);
    }
}
echo "View cache dihapus.<br>";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache dihapus.<br>";
}
header("X-LiteSpeed-Purge: *");
echo "<strong>SELESAI! Silakan refresh dashboard Anda.</strong>";
