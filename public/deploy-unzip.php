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
    
    echo "BERHASIL: Web telah terupdate dalam sekejap!";
} else {
    http_response_code(500);
    echo "GAGAL: Tidak bisa mengekstrak ZIP.";
}
?>
