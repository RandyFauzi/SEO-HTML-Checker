<?php

namespace App\Services\AI;

use App\Models\AuditRun;
use App\Models\SeoRule;
use App\Models\AuditRemediation;
use Illuminate\Support\Facades\Log;

class HtmlRemediationService
{
    protected OpenAIClientService $client;

    public function __construct(OpenAIClientService $client)
    {
        $this->client = $client;
    }

    public function generateFix(string $htmlSnippet, SeoRule $rule, string $issue, string $expected): string
    {
        $configJson = json_encode($rule->config);
        
        $systemPrompt = <<<EOF
Anda adalah SEO Expert dan HTML Developer senior.
Tugas Anda HANYA SATU: memperbaiki snippet HTML yang diberikan agar lolos dari aturan SEO berikut.
Aturan: {$rule->name}
Tipe Aturan: {$rule->rule_type}
Configurasi Aturan: {$configJson}
Error Saat Ini: {$issue}
Ekspektasi: {$expected}

PENTING:
1. Jawab HANYA dengan kode HTML yang sudah diperbaiki.
2. JANGAN sertakan blok markdown seperti `html ... `.
3. JANGAN berikan penjelasan apapun.
4. JANGAN merusak struktur tag atau isi konten lain di dalam snippet yang tidak relevan dengan error.
5. Jika snippet aslinya berantakan, kembalikan snippet yang rapi.
6. Jika error tidak mungkin diperbaiki hanya dari snippet, kembalikan snippet aslinya.
EOF;

        $prompt = $systemPrompt . "\n\nHTML Snippet Asli:\n" . $htmlSnippet;

        $model = env('OPENAI_REMEDIATION_MODEL', 'gpt-6-astra');
        
        return $this->client->generateText($model, $prompt);
    }
}
