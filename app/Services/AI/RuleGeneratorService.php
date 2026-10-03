<?php

namespace App\Services\AI;

class RuleGeneratorService
{
    protected AiClientInterface $client;

    public function __construct(AiClientInterface $client)
    {
        $this->client = $client;
    }

    public function generate(string $userPrompt): array
    {
        $systemPrompt = <<<EOF
Anda adalah SEO Engineer ahli. Tugas Anda adalah menerjemahkan instruksi user menjadi JSON aturan SEO yang valid untuk sistem kami.
Output harus HANYA berupa JSON object murni.

Struktur JSON yang diharapkan:
{
  "name": "Nama aturan singkat",
  "code": "kode_aturan_snake_case",
  "category": "Kategori (contoh: Meta Tags, Typography, Links, Images)",
  "target_page": "all", // Bisa "all" (semua), "lp" (Landing Page), atau "amp" (Halaman AMP)
  "rule_type": "TIPE_ATURAN",
  "config": { ... },
  "issue_message": "Pesan error singkat",
  "reason_template": "Penjelasan detail kenapa gagal",
  "recommendation": "Saran perbaikan",
  "severity": "error" atau "warning"
}

Daftar TIPE_ATURAN dan contoh config-nya:
1. "tag_presence"
   - config: {"selector": "h1", "must_exist": true}
   - PENTING untuk multi tag (seperti alternate hreflang): Jangan gunakan selector generik seperti "link[rel='alternate']" jika ingin mengecek banyak bahasa spesifik. Gunakan atribut yang tepat: {"selector": "link[rel='alternate'][hreflang='id-id']", "must_exist": true}
2. "tag_count"
   - config: {"selector": "h1", "min": 1, "max": 1}
3. "attribute_presence"
   - config: {"selector": "img", "attribute": "alt", "must_exist": true}
4. "text_length"
   - config: {"selector": "title", "min_length": 10, "max_length": 60}
5. "anchor_href_allowlist"
   - config: {"selector": "a.btn-buy", "allowed_target_types": ["canonical", "amphtml", "https://trust.com"]}
6. "regex_match"
   - config: {"selector": "meta[name='robots']", "attribute": "content", "pattern": "/index, follow/i"}

Buatlah aturan yang paling tepat berdasarkan permintaan user berikut. Perhatikan apakah user meminta aturan untuk LP atau AMP. Jawab HANYA dengan JSON.
EOF;

        $provider = config('services.ai_provider', 'openai');
        $model = $provider === 'gemini' 
            ? env('GEMINI_RULE_BUILDER_MODEL', 'gemini-1.5-flash')
            : env('OPENAI_RULE_BUILDER_MODEL', 'gpt-6-astra');
        
        $prompt = $systemPrompt . "\n\nPermintaan user: " . $userPrompt;

        return $this->client->generateStructuredOutput($model, $prompt);
    }
}
