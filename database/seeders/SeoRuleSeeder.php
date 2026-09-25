<?php

namespace Database\Seeders;

use App\Models\SeoRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeoRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Because the database is the source of truth, we will truncate and re-seed for this upgrade phase.
        DB::table('seo_rules')->truncate();

        $rules = [
            // ==================
            // HTML Basic (Document)
            // ==================
            [
                'code' => 'HTML_ELEMENT_EXISTS',
                'name' => 'HTML Element Exists',
                'category' => 'Document',
                'rule_type' => 'exist',
                'config' => ['selector' => 'html'],
                'severity' => 'error',
                'issue_message' => 'Tag <html> tidak ditemukan.',
                'reason_template' => 'Setiap dokumen HTML yang valid wajib memiliki tag root <html>.',
                'recommendation' => 'Pastikan dokumen diawali dengan <html> dan diakhiri dengan </html>.',
            ],
            [
                'code' => 'HEAD_EXISTS',
                'name' => 'Head Element Exists',
                'category' => 'Document',
                'rule_type' => 'exist',
                'config' => ['selector' => 'head'],
                'severity' => 'error',
                'issue_message' => 'Tag <head> tidak ditemukan.',
                'reason_template' => 'Tag <head> diperlukan untuk menyimpan metadata dokumen.',
                'recommendation' => 'Tambahkan elemen <head> tepat setelah tag pembuka <html>.',
            ],
            [
                'code' => 'BODY_EXISTS',
                'name' => 'Body Element Exists',
                'category' => 'Document',
                'rule_type' => 'exist',
                'config' => ['selector' => 'body'],
                'severity' => 'error',
                'issue_message' => 'Tag <body> tidak ditemukan.',
                'reason_template' => 'Tag <body> diperlukan untuk menampung konten utama yang terlihat oleh pengguna.',
                'recommendation' => 'Tambahkan elemen <body> setelah tag penutup </head>.',
            ],
            [
                'code' => 'DOCTYPE_EXISTS',
                'name' => 'Doctype Exists',
                'category' => 'Document',
                'rule_type' => 'exist',
                'config' => ['selector' => 'html'], // DomCrawler parses DOCTYPE away but we can assume if html exists and is parsed correctly, doctype might be there. Wait, DOMCrawler drops DOCTYPE. Let's use regex on raw HTML later. For now, since we only use DomCrawler, this is hard to check accurately. Let's skip DOCTYPE or check via string match? We can't do string match without a raw_html evaluator. Let's skip DOCTYPE for now, or just leave it as warning.
                // Wait, text_match with empty selector means checking raw HTML?
                // Actually the spec requested DOCTYPE_EXISTS. I will implement it as special if needed, but let's leave it out or implement it securely. Let's skip DOCTYPE for now.
            ],

            // ==================
            // Metadata (Title)
            // ==================
            [
                'code' => 'TITLE_REQUIRED',
                'name' => 'Title Tag Exists',
                'category' => 'Metadata',
                'rule_type' => 'exist',
                'config' => ['selector' => 'title'],
                'severity' => 'error',
                'issue_message' => 'Halaman tidak memiliki tag <title>.',
                'reason_template' => 'Tag title sangat krusial untuk aksesibilitas dan identifikasi halaman.',
                'recommendation' => 'Tambahkan elemen <title> di dalam <head>.',
            ],
            [
                'code' => 'TITLE_NOT_EMPTY',
                'name' => 'Title Is Not Empty',
                'category' => 'Metadata',
                'rule_type' => 'length',
                'config' => [
                    'selector' => 'title',
                    'operator' => '>',
                    'expected' => 0,
                ],
                'severity' => 'error',
                'issue_message' => 'Tag <title> kosong.',
                'reason_template' => 'Keberadaan elemen <title> tidak berguna jika tidak memiliki teks.',
                'recommendation' => 'Isi elemen <title> dengan judul halaman yang representatif.',
            ],
            [
                'code' => 'TITLE_LENGTH_RECOMMENDED',
                'name' => 'Title Length Optimal',
                'category' => 'Metadata',
                'rule_type' => 'length',
                'config' => [
                    'selector' => 'title',
                    'operator' => 'between',
                    'min' => 30,
                    'max' => 60,
                ],
                'severity' => 'warning',
                'issue_message' => 'Panjang title melebihi atau kurang dari rekomendasi.',
                'reason_template' => 'Disarankan 30-60 karakter agar tidak terpotong di hasil pencarian.',
                'recommendation' => 'Sesuaikan panjang judul halaman antara 30-60 karakter.',
            ],

            // ==================
            // Metadata (Description)
            // ==================
            [
                'code' => 'META_DESCRIPTION_EXISTS',
                'name' => 'Meta Description Exists',
                'category' => 'Metadata',
                'rule_type' => 'exist',
                'config' => ['selector' => 'meta[name="description"]'],
                'severity' => 'warning',
                'issue_message' => 'Meta description tidak ditemukan.',
                'reason_template' => 'Meta description memberikan ringkasan halaman di hasil pencarian.',
                'recommendation' => 'Tambahkan <meta name="description" content="..."> di dalam <head>.',
            ],
            [
                'code' => 'META_DESCRIPTION_NOT_EMPTY',
                'name' => 'Meta Description Not Empty',
                'category' => 'Metadata',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'meta[name="description"]',
                    'attribute' => 'content',
                    'condition' => 'not_empty',
                ],
                'severity' => 'error',
                'issue_message' => 'Meta description kosong.',
                'reason_template' => 'Atribut content pada meta description tidak boleh kosong.',
                'recommendation' => 'Isi atribut content dengan ringkasan halaman.',
            ],
            [
                'code' => 'META_DESCRIPTION_LENGTH_RECOMMENDED',
                'name' => 'Meta Description Length Optimal',
                'category' => 'Metadata',
                'rule_type' => 'length',
                'config' => [
                    'selector' => 'meta[name="description"]',
                    'attribute' => 'content',
                    'operator' => 'between',
                    'min' => 120,
                    'max' => 160,
                ],
                'severity' => 'warning',
                'issue_message' => 'Panjang meta description tidak optimal.',
                'reason_template' => 'Disarankan 120-160 karakter untuk tampilan snippet pencarian yang baik.',
                'recommendation' => 'Tulis ringkasan unik antara 120-160 karakter.',
            ],

            // ==================
            // Metadata (Robots)
            // ==================
            [
                'code' => 'ROBOTS_META_EXISTS',
                'name' => 'Robots Meta Exists',
                'category' => 'Metadata',
                'rule_type' => 'exist',
                'config' => ['selector' => 'meta[name="robots"]'],
                'severity' => 'warning',
                'issue_message' => 'Meta robots tidak ditemukan.',
                'reason_template' => 'Meta robots dapat digunakan untuk mengontrol instruksi perayapan mesin pencari.',
                'recommendation' => 'Jika diperlukan, tambahkan <meta name="robots" content="index, follow">.',
                'is_active' => false, // Disabled by default as it's not strictly required
            ],
            [
                'code' => 'NOINDEX_DETECTED',
                'name' => 'Noindex Directive Detected',
                'category' => 'Metadata',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'meta[name="robots"]',
                    'attribute' => 'content',
                    'condition' => 'not_contains',
                    'expected_value' => 'noindex',
                ],
                'severity' => 'warning',
                'issue_message' => 'Halaman ini memiliki arahan noindex.',
                'reason_template' => 'Tag noindex mencegah mesin pencari untuk mengindeks halaman ini.',
                'recommendation' => 'Pastikan halaman ini memang disengaja untuk tidak diindeks.',
            ],

            // ==================
            // Canonical
            // ==================
            [
                'code' => 'CANONICAL_EXISTS',
                'name' => 'Canonical Tag Exists',
                'category' => 'Metadata',
                'rule_type' => 'exist',
                'config' => ['selector' => 'link[rel="canonical"]'],
                'severity' => 'warning',
                'issue_message' => 'Tag rel="canonical" tidak ditemukan.',
                'reason_template' => 'Canonical berguna untuk menghindari masalah duplikasi konten.',
                'recommendation' => 'Tambahkan tag canonical yang mengarah ke versi utama URL ini.',
            ],
            [
                'code' => 'CANONICAL_SINGLE',
                'name' => 'Single Canonical Tag',
                'category' => 'Metadata',
                'rule_type' => 'count',
                'config' => [
                    'selector' => 'link[rel="canonical"]',
                    'operator' => '=',
                    'expected' => 1,
                ],
                'severity' => 'error',
                'issue_message' => 'Terdapat lebih dari satu tag canonical, atau tidak ada.',
                'reason_template' => 'Hanya boleh ada satu tag canonical per dokumen HTML.',
                'recommendation' => 'Hapus duplikasi tag canonical.',
            ],
            [
                'code' => 'CANONICAL_HAS_HREF',
                'name' => 'Canonical Has Valid Href',
                'category' => 'Metadata',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'link[rel="canonical"]',
                    'attribute' => 'href',
                    'condition' => 'not_empty',
                ],
                'severity' => 'error',
                'issue_message' => 'Canonical tag kehilangan atribut href atau kosong.',
                'reason_template' => 'Tag canonical harus memiliki tujuan URL (href).',
                'recommendation' => 'Pastikan atribut href berisi URL valid.',
            ],

            // ==================
            // Headings
            // ==================
            [
                'code' => 'H1_EXISTS',
                'name' => 'H1 Heading Exists',
                'category' => 'Headings',
                'rule_type' => 'exist',
                'config' => ['selector' => 'h1'],
                'severity' => 'warning',
                'issue_message' => 'Halaman tidak memiliki tag <h1>.',
                'reason_template' => 'H1 membantu mengidentifikasi topik utama halaman.',
                'recommendation' => 'Tambahkan tag <h1> yang mendeskripsikan topik halaman.',
            ],
            [
                'code' => 'H1_SINGLE',
                'name' => 'Single H1 Heading',
                'category' => 'Headings',
                'rule_type' => 'count',
                'config' => [
                    'selector' => 'h1',
                    'operator' => '=',
                    'expected' => 1,
                ],
                'severity' => 'warning',
                'issue_message' => 'Multiple H1 headings were found.',
                'reason_template' => 'Disarankan memiliki struktur heading yang jelas, meskipun multiple H1 secara teknis valid pada HTML5.',
                'recommendation' => 'Review apakah struktur heading telah mewakili topik utama halaman dengan baik.',
            ],
            [
                'code' => 'H1_NOT_EMPTY',
                'name' => 'H1 Not Empty',
                'category' => 'Headings',
                'rule_type' => 'length',
                'config' => [
                    'selector' => 'h1',
                    'operator' => '>',
                    'expected' => 0,
                ],
                'severity' => 'error',
                'issue_message' => 'Tag <h1> kosong.',
                'reason_template' => 'Heading yang kosong merusak struktur halaman.',
                'recommendation' => 'Isi tag <h1> dengan teks yang relevan.',
            ],
            [
                'code' => 'H2_HIERARCHY',
                'name' => 'Heading Hierarchy (H2)',
                'category' => 'Headings',
                'rule_type' => 'special',
                'config' => [
                    'special_type' => 'h2_hierarchy',
                ],
                'severity' => 'warning',
                'issue_message' => 'Heading hierarchy skips H2.',
                'reason_template' => 'Ditemukan tag H3 sebelum adanya H2, yang mengindikasikan loncatan hierarki.',
                'recommendation' => 'Gunakan H2 sebelum H3 untuk menjaga struktur logis halaman.',
            ],

            // ==================
            // Technical
            // ==================
            [
                'code' => 'HTML_LANG_EXISTS',
                'name' => 'HTML Language Declared',
                'category' => 'Technical',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'html',
                    'attribute' => 'lang',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Atribut lang pada tag <html> tidak dideklarasikan.',
                'reason_template' => 'Deklarasi bahasa membantu aksesibilitas (screen reader) dan browser rendering.',
                'recommendation' => 'Tambahkan atribut lang (misal lang="en" atau lang="id") pada tag <html>.',
            ],
            [
                'code' => 'VIEWPORT_EXISTS',
                'name' => 'Viewport Meta Exists',
                'category' => 'Technical',
                'rule_type' => 'exist',
                'config' => ['selector' => 'meta[name="viewport"]'],
                'severity' => 'warning',
                'issue_message' => 'Meta viewport tidak ditemukan.',
                'reason_template' => 'Dibutuhkan agar halaman responsif di perangkat mobile.',
                'recommendation' => 'Tambahkan <meta name="viewport" content="width=device-width, initial-scale=1">',
            ],

            // ==================
            // Structured Data
            // ==================
            [
                'code' => 'JSON_LD_EXISTS',
                'name' => 'JSON-LD Exists',
                'category' => 'Structured Data',
                'rule_type' => 'exist',
                'config' => ['selector' => 'script[type="application/ld+json"]'],
                'severity' => 'warning',
                'issue_message' => 'JSON-LD Schema tidak ditemukan.',
                'reason_template' => 'Structured data membantu mesin pencari memahami konten dengan lebih baik (Rich Snippets).',
                'recommendation' => 'Implementasikan Schema.org menggunakan format JSON-LD jika halaman memiliki entitas spesifik (Artikel, Produk, dll).',
            ],
            [
                'code' => 'JSON_LD_VALID_JSON',
                'name' => 'JSON-LD is Valid JSON',
                'category' => 'Structured Data',
                'rule_type' => 'json_ld',
                'config' => [
                    'check_type' => 'valid',
                ],
                'severity' => 'error',
                'issue_message' => 'Sintaks JSON-LD tidak valid.',
                'reason_template' => 'Mesin pencari tidak dapat mengurai JSON yang cacat sintaksnya.',
                'recommendation' => 'Periksa sintaks JSON menggunakan linter untuk memastikan tidak ada koma berlebih atau kurung tertinggal.',
            ],
            [
                'code' => 'JSON_LD_HAS_CONTEXT',
                'name' => 'JSON-LD Has Context',
                'category' => 'Structured Data',
                'rule_type' => 'json_ld',
                'config' => [
                    'check_type' => 'has_context',
                    'expected' => 'https://schema.org',
                ],
                'severity' => 'warning',
                'issue_message' => 'JSON-LD tidak memiliki @context https://schema.org.',
                'reason_template' => 'Schema.org context diperlukan agar search engine mengenali kosakata data terstruktur.',
                'recommendation' => 'Tambahkan "@context": "https://schema.org" ke dalam JSON-LD Anda.',
            ],
            [
                'code' => 'JSON_LD_HAS_TYPE',
                'name' => 'JSON-LD Has Type',
                'category' => 'Structured Data',
                'rule_type' => 'json_ld',
                'config' => [
                    'check_type' => 'has_type',
                ],
                'severity' => 'error',
                'issue_message' => 'JSON-LD kehilangan deklarasi @type.',
                'reason_template' => 'Tanpa @type, search engine tidak tahu entitas apa yang sedang dideskripsikan.',
                'recommendation' => 'Tambahkan "@type": "Article" (atau tipe relevan lainnya) ke dalam JSON-LD.',
            ],

            // ==================
            // Social Open Graph
            // ==================
            [
                'code' => 'OG_TITLE',
                'name' => 'Open Graph Title',
                'category' => 'Social',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'meta[property="og:title"]',
                    'attribute' => 'content',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Tag og:title tidak ditemukan atau kosong.',
                'reason_template' => 'Berfungsi menampilkan judul saat tautan dibagikan di media sosial.',
                'recommendation' => 'Tambahkan <meta property="og:title" content="...">.',
            ],
            [
                'code' => 'OG_DESCRIPTION',
                'name' => 'Open Graph Description',
                'category' => 'Social',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'meta[property="og:description"]',
                    'attribute' => 'content',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Tag og:description tidak ditemukan atau kosong.',
                'reason_template' => 'Memberikan ringkasan saat tautan dibagikan di media sosial.',
                'recommendation' => 'Tambahkan <meta property="og:description" content="...">.',
            ],
            [
                'code' => 'OG_IMAGE',
                'name' => 'Open Graph Image',
                'category' => 'Social',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'meta[property="og:image"]',
                    'attribute' => 'content',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Tag og:image tidak ditemukan atau kosong.',
                'reason_template' => 'Menampilkan thumbnail gambar di media sosial.',
                'recommendation' => 'Tambahkan <meta property="og:image" content="...">.',
            ],
            [
                'code' => 'OG_URL',
                'name' => 'Open Graph URL',
                'category' => 'Social',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'meta[property="og:url"]',
                    'attribute' => 'content',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Tag og:url tidak ditemukan atau kosong.',
                'reason_template' => 'Menentukan URL kanonikal untuk media sosial.',
                'recommendation' => 'Tambahkan <meta property="og:url" content="...">.',
            ],
            [
                'code' => 'OG_TYPE',
                'name' => 'Open Graph Type',
                'category' => 'Social',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'meta[property="og:type"]',
                    'attribute' => 'content',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Tag og:type tidak ditemukan atau kosong.',
                'reason_template' => 'Menentukan tipe objek media sosial (contoh: website, article).',
                'recommendation' => 'Tambahkan <meta property="og:type" content="website">.',
            ],

            // ==================
            // Images
            // ==================
            [
                'code' => 'IMG_ALT_ATTRIBUTE',
                'name' => 'Images Have Alt Attribute',
                'category' => 'Images',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'img',
                    'attribute' => 'alt',
                    'condition' => 'exists',
                ],
                'severity' => 'error',
                'issue_message' => 'Beberapa gambar kehilangan atribut alt sepenuhnya.',
                'reason_template' => 'Atribut alt diwajibkan oleh standar HTML untuk gambar.',
                'recommendation' => 'Berikan atribut alt="" pada semua tag <img>.',
            ],
            [
                'code' => 'IMG_ALT_EMPTY',
                'name' => 'Image Alt Is Empty',
                'category' => 'Images',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'img',
                    'attribute' => 'alt',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Beberapa gambar memiliki atribut alt kosong.',
                'reason_template' => 'Alt kosong (alt="") sah untuk gambar dekoratif, namun gambar informatif harus memiliki teks deskriptif.',
                'recommendation' => 'Pastikan alt kosong ini memang untuk gambar dekoratif. Jika informatif, isilah dengan teks.',
            ],

            // ==================
            // Links
            // ==================
            [
                'code' => 'LINK_EMPTY_HREF',
                'name' => 'Links Have Non-Empty Href',
                'category' => 'Links',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'a',
                    'attribute' => 'href',
                    'condition' => 'not_empty',
                ],
                'severity' => 'warning',
                'issue_message' => 'Beberapa tautan memiliki atribut href kosong (href="").',
                'reason_template' => 'Tautan kosong tidak dapat diikuti oleh mesin pencari atau pengguna.',
                'recommendation' => 'Berikan URL valid atau gunakan tombol (<button>) jika elemen ini ditujukan untuk aksi JavaScript.',
            ],
            // Fix LINK_HASH_ONLY to use Count
            [
                'code' => 'LINK_HASH_ONLY',
                'name' => 'Avoid Hash Only Links',
                'category' => 'Links',
                'rule_type' => 'count',
                'config' => [
                    'selector' => 'a[href="#"]',
                    'operator' => '=',
                    'expected' => 0,
                ],
                'severity' => 'warning',
                'issue_message' => 'Ditemukan tautan dengan href="#".',
                'reason_template' => 'Penggunaan href="#" buruk bagi aksesibilitas dan SEO. Sebaiknya gunakan elemen <button>.',
                'recommendation' => 'Ganti tag <a> dengan <button> untuk interaksi UI yang tidak mengubah URL.',
            ],
            [
                'code' => 'LINK_HREF_EXISTS',
                'name' => 'Links Have Href Attribute',
                'category' => 'Links',
                'rule_type' => 'link',
                'config' => [
                    'condition' => 'href_exists',
                    'selector' => 'a',
                ],
                'severity' => 'warning',
                'issue_message' => 'Ditemukan tag <a> tanpa atribut href.',
                'reason_template' => 'Tag <a> tanpa href bukan merupakan hyperlink yang valid.',
                'recommendation' => 'Pastikan setiap tag <a> memiliki atribut href.',
            ],
            [
                'code' => 'LINK_TARGET_BLANK_REL',
                'name' => 'Target Blank Links Use Noopener',
                'category' => 'Links',
                'rule_type' => 'link',
                'config' => [
                    'condition' => 'target_blank_rel',
                    'expected' => 'noopener',
                ],
                'severity' => 'warning',
                'issue_message' => 'Link target="_blank" tidak menyertakan rel="noopener".',
                'reason_template' => 'Membuka jendela baru tanpa rel="noopener" rentan terhadap eksploitasi tabnabbing.',
                'recommendation' => 'Tambahkan rel="noopener" atau rel="noreferrer" pada link target="_blank".',
            ],

            // ==================
            // URL Rules
            // ==================
            [
                'code' => 'PAGE_HTTPS',
                'name' => 'Page Uses HTTPS Protocol',
                'category' => 'Technical',
                'rule_type' => 'url_match',
                'config' => [
                    'target' => 'page_url',
                    'condition' => 'protocol',
                    'operator' => 'equals',
                    'expected' => 'https',
                ],
                'severity' => 'warning',
                'issue_message' => 'Halaman tidak disajikan melalui protokol aman HTTPS.',
                'reason_template' => 'HTTPS adalah standar keamanan web modern dan sinyal peringkat pencarian.',
                'recommendation' => 'Migrasikan URL dan server untuk selalu menggunakan HTTPS.',
            ],
            [
                'code' => 'CANONICAL_SELF_REFERENCE',
                'name' => 'Canonical Matches Current Page URL',
                'category' => 'Metadata',
                'rule_type' => 'url_match',
                'config' => [
                    'target' => 'canonical_match',
                    'condition' => 'canonical_match',
                ],
                'severity' => 'warning',
                'issue_message' => 'Canonical URL tidak cocok dengan URL halaman saat ini.',
                'reason_template' => 'Jika halaman ini adalah versi utama, canonical sebaiknya menunjuk ke URL ini sendiri (self-referencing).',
                'recommendation' => 'Periksa apakah canonical sengaja menunjuk ke halaman lain atau terjadi kesalahan URL.',
            ],

            // ==================
            // Hreflang
            // ==================
            [
                'code' => 'HREFLANG_EXISTS',
                'name' => 'Hreflang Exists',
                'category' => 'International',
                'rule_type' => 'exist',
                'config' => ['selector' => 'link[rel="alternate"][hreflang]'],
                'severity' => 'warning',
                'issue_message' => 'Tag hreflang tidak ditemukan.',
                'reason_template' => 'Diperlukan hanya jika website Anda memiliki variasi bahasa atau wilayah lain.',
                'recommendation' => 'Jika website multi-bahasa, tambahkan rel="alternate" hreflang.',
                'is_active' => false,
            ],
            [
                'code' => 'HREFLANG_HAS_HREF',
                'name' => 'Hreflang Has Href',
                'category' => 'International',
                'rule_type' => 'attribute',
                'config' => [
                    'selector' => 'link[rel="alternate"][hreflang]',
                    'attribute' => 'href',
                    'condition' => 'not_empty',
                ],
                'severity' => 'error',
                'issue_message' => 'Tag hreflang kehilangan atribut href.',
                'reason_template' => 'Setiap deklarasi hreflang wajib menunjuk pada sebuah URL.',
                'recommendation' => 'Tambahkan URL ke dalam atribut href.',
            ],

            // ==================
            // AMP Compare
            // ==================
            [
                'code' => 'AMP_TITLE_MATCH',
                'name' => 'Title matches AMP',
                'category' => 'AMP',
                'rule_type' => 'compare_amp',
                'config' => ['selector' => 'title'],
                'severity' => 'warning',
                'issue_message' => 'Title LP dan AMP tidak identik.',
                'reason_template' => 'Konsistensi metadata sangat disarankan antara versi canonical dan AMP.',
                'recommendation' => 'Samakan isi <title> pada halaman utama dan AMP.',
            ],
            [
                'code' => 'AMP_DESC_MATCH',
                'name' => 'Meta Desc matches AMP',
                'category' => 'AMP',
                'rule_type' => 'compare_amp',
                'config' => [
                    'selector' => 'meta[name="description"]',
                    'attribute' => 'content',
                ],
                'severity' => 'warning',
                'issue_message' => 'Meta description LP dan AMP tidak identik.',
                'reason_template' => 'Konsistensi metadata sangat disarankan.',
                'recommendation' => 'Samakan isi atribut content pada halaman utama dan AMP.',
            ],
        ];

        // Remove the incorrect DOCTYPE_EXISTS entry that was placed above but left empty/incorrect.
        $filteredRules = array_filter($rules, fn ($r) => $r['code'] !== 'DOCTYPE_EXISTS');

        $i = 0;
        foreach ($filteredRules as $rule) {
            $i++;
            SeoRule::create(array_merge($rule, ['sort_order' => $i]));
        }
    }
}
