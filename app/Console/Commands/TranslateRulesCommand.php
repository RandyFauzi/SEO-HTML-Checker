<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SeoRule;

class TranslateRulesCommand extends Command
{
    protected $signature = 'rules:translate';
    protected $description = 'Translate existing rule names to Indonesian';

    public function handle()
    {
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

        $total = 0;
        foreach($m as $e => $i) {
            $updated = SeoRule::where('name', $e)->update(['name' => $i]);
            if ($updated > 0) {
                $this->info("Updated $updated rules: $e -> $i");
                $total += $updated;
            }
        }
        
        $this->info("Translation completed. Total updated: $total");
    }
}
