<?php
$c = file_get_contents("app/Services/DefaultRules.php");
$m = [
    "Title Tag Exists" => "Tag Title Ada",
    "Title Tag Length is Optimal" => "Panjang Title Optimal",
    "Only One Title Tag" => "Hanya Satu Tag Title",
    "Meta Description Exists" => "Meta Description Ada",
    "Meta Description Length is Optimal" => "Panjang Meta Description Optimal",
    "Viewport Meta Tag Exists" => "Tag Viewport Ada",
    "Canonical Tag Exists" => "Tag Canonical Ada",
    "H1 Tag Exists" => "Tag H1 Ada",
    "Only One H1 Tag" => "Hanya Satu Tag H1",
    "Images Have Alt Attribute" => "Gambar Memiliki Atribut Alt",
    "Image Alt Attributes Not Empty" => "Atribut Alt Gambar Tidak Kosong",
    "Avoid Hash Only Links" => "Hindari Link dengan Hash (#) Saja",
    "Links Have Href Attribute" => "Link Memiliki Atribut Href",
    "Target Blank Links Use Noopener" => "Link Target Blank Menggunakan Noopener",
    "Page Uses HTTPS Protocol" => "Halaman Menggunakan HTTPS",
    "Canonical Matches Current Page URL" => "Canonical Sesuai URL Halaman",
    "Hreflang Exists" => "Tag Hreflang Ada",
    "Hreflang Has Href" => "Tag Hreflang Memiliki Href",
    "Title matches AMP" => "Title Sama dengan AMP",
    "Meta Desc matches AMP" => "Meta Description Sama dengan AMP",
    "GTAG matches Brand ID" => "GTAG Sesuai dengan ID Brand",
    "Valid AMP Alternate Link" => "Link Alternate AMP Valid"
];
foreach($m as $e => $i){
    $c = str_replace("'name' => '{$e}'", "'name' => '{$i}'", $c);
}
file_put_contents("app/Services/DefaultRules.php", $c);
echo "DefaultRules.php translated.\n";

// Update database for existing rules!
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach($m as $e => $i) {
    $updated = \App\Models\SeoRule::where('name', $e)->update(['name' => $i]);
    echo "Updated $updated rules for $e -> $i\n";
}
