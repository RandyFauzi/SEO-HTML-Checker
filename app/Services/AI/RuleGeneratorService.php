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
  "explanation": "Penjelasan ramah untuk user (bahasa Indonesia) tentang apa yang akan dicek oleh aturan ini.",
  "rule": {
    "name": "Nama aturan singkat",
    "code": "kode_aturan_snake_case",
    "category": "Kategori (contoh: Meta Tags, Typography, Links, Images)",
    "target_page": "all", // Bisa "all", "lp", atau "amp"
    "rule_type": "TIPE_ATURAN",
    "config": { ... },
    "issue_message": "Pesan error singkat",
    "reason_template": "Penjelasan detail kenapa gagal",
    "recommendation": "Saran perbaikan",
    "severity": "error" atau "warning"
  }
}

Daftar TIPE_ATURAN yang VALID (harus persis) dan contoh config-nya:
1. "exist" (Cek keberadaan tag, harus ada minimal 1)
   - config: {"selector": "h1"}
2. "not_exist" (Pastikan tag TIDAK ADA di HTML)
   - config: {"selector": "meta[name='keywords']"}
3. "count" (Cek jumlah tag)
   - config: {"selector": "h1", "min": 1, "max": 1}
4. "attribute" (Cek atribut tag)
   - config: {"selector": "img", "attribute": "alt", "must_exist": true}
4. "length" (Cek panjang teks/atribut)
   - config: {"selector": "title", "min": 10, "max": 60}
5. "regex" (Pencocokan Regex)
   - config: {"selector": "meta[name='robots']", "attribute": "content", "pattern": "/index, follow/i"}
6. "text_match" (Pencocokan Teks Persis)
   - config: {"selector": "title", "expected": "Judul Diharapkan"}
7. "compare_amp" (Bandingkan LP dan AMP)
   - config: {"selector": "title"}
8. "json_ld" (Cek schema.org JSON-LD)
   - config: {"type": "Product", "required_properties": ["name", "price"]}
9. "anchor_href_allowlist" (Cek href tombol/link)
   - config: {"selector": "a.btn-buy", "allowed_target_types": ["canonical", "amphtml", "https://trust.com"]}

MODIFIER CONFIG GLOBAL (Berlaku untuk semua tipe kecuali exist/not_exist/count):
- "skip_if_missing": boolean. Jika true, abaikan (lulus) jika elemen tidak ada di HTML. Berguna untuk elemen kondisional.
- "scope": "first" | "all" | "any". Default "first" (kecuali attribute). Gunakan "all" untuk mengecek SEMUA tag yang cocok.

PENTING: Jangan membuat TIPE_ATURAN selain dari daftar di atas! Gunakan tipe yang paling relevan.
Buatlah aturan yang paling tepat berdasarkan permintaan user berikut. Perhatikan apakah user meminta aturan untuk LP atau AMP. Jawab HANYA dengan JSON.
EOF;

        $provider = config('services.ai_provider', 'openai');
        $model = $provider === 'gemini' 
            ? env('GEMINI_RULE_BUILDER_MODEL', 'gemini-flash-latest')
            : env('OPENAI_RULE_BUILDER_MODEL', 'gpt-6-astra');
        
        $prompt = $systemPrompt . "\n\nPermintaan user: " . $userPrompt;

        return $this->client->generateStructuredOutput($model, $prompt);
    }
}
