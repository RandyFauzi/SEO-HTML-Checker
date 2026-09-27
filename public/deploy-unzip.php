<?php
// Script Unzip Otomatis - Cepat & Ringan
$secret = 'RAHASIA_SUPER_KUAT_123';

if (!isset($_GET['token']) || $_GET['token'] !== $secret) {
    http_response_code(403);
    die('Akses Ditolak.');
}

$zipFile = __DIR__ . '/deploy.zip';

if (!file_exists($zipFile)) {
    http_response_code(404);
    die('File deploy.zip tidak ditemukan di server.');
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    // Ekstrak ke public_html (folder tempat file ini berada)
    $zip->extractTo(__DIR__);
    $zip->close();
    
    // Hapus file zip agar hosting tidak kepenuhan
    unlink($zipFile);
    
    echo "BERHASIL: File ZIP telah diekstrak!\n";

    // Boot Laravel untuk menjalankan migrasi
    echo "--- MENJALANKAN MIGRASI DATABASE ---\n";
    try {
        require __DIR__.'/seo-app/vendor/autoload.php';
        $app = require_once __DIR__.'/seo-app/bootstrap/app.php';

        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $status = $kernel->call('migrate', ['--force' => true]);
        echo $kernel->output();

        echo "\n--- MENERJEMAHKAN ATURAN ---\n";
        $kernel->call('rules:translate');
        echo $kernel->output();

        echo "\n--- MEMBERSIHKAN CACHE ---\n";
        $kernel->call('optimize:clear');
        echo $kernel->output();
        
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo "OPcache berhasil di-reset.\n";
        }

        if ($status === 0) {
            echo "\nBERHASIL: Database berhasil di-update!\n";
        } else {
            http_response_code(500);
            echo "\nGAGAL: Terjadi masalah saat migrasi database (Status Code: $status).\n";
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "\nGAGAL FATAL: " . $e->getMessage() . "\n";
    }

} else {
    http_response_code(500);
    echo "GAGAL: Tidak bisa mengekstrak ZIP.";
}
?>
