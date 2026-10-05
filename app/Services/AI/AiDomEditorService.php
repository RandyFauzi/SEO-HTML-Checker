<?php

namespace App\Services\AI;

use App\DTO\DomEditOperation;
use App\Services\HtmlModifierService;
use Illuminate\Support\Facades\Log;

class AiDomEditorService
{
    public function __construct(
        protected AiClientInterface $aiClient,
        protected HtmlModifierService $htmlModifier
    ) {}

    /**
     * @param string $html The original HTML
     * @param string $prompt The user's prompt (e.g., "Change the hero image and title")
     * @return array{operations: DomEditOperation[], modified_html: string}
     */
    public function editHtml(string $html, string $prompt): array
    {
        $systemPrompt = <<<EOF
Anda adalah ahli manipulasi DOM tingkat lanjut.
Tugas Anda adalah membaca instruksi user dan menghasilkan daftar operasi (operations) untuk memodifikasi HTML.
Output HARUS berformat JSON array berisi daftar operasi. JANGAN BERIKAN TEKS LAIN SELAIN JSON.

Skema JSON yang diwajibkan (Array of Objects):
[
  {
    "selector": "string (CSS Selector valid untuk elemen yang akan diedit)",
    "action": "string (Hanya pilih salah satu: 'set_attr', 'set_text', 'replace_html', 'remove')",
    "attribute": "string (Nama atribut, contoh: 'src', 'href', 'alt'. WAJIB ada jika action = 'set_attr', biarkan kosong untuk action lain)",
    "value": "string (Nilai baru untuk atribut/teks/html)"
  }
]

Aturan:
1. Kembalikan HANYA JSON murni (array). Jangan pakai blok ```json.
2. Gunakan CSS selector yang paling spesifik.
3. Jaga validitas AMP jika ada instruksi terkait AMP.
EOF;

        $userPrompt = "Instruksi User:\n" . $prompt . "\n\nHTML Target:\n" . $html;

        // In a real app, dynamically resolve the model from config
        $model = env('OPENAI_REMEDIATION_MODEL', 'gpt-5');
        
        $jsonResponse = $this->aiClient->generateText($model, $systemPrompt . "\n\n" . $userPrompt);
        
        // Clean markdown block if the AI ignored rule 1
        $jsonResponse = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($jsonResponse));
        
        $operationsData = json_decode($jsonResponse, true);
        
        if (!is_array($operationsData)) {
            Log::error('AI DOM Editor failed to return valid JSON', ['response' => $jsonResponse]);
            return ['operations' => [], 'modified_html' => $html];
        }

        $operations = [];
        foreach ($operationsData as $op) {
            $operations[] = DomEditOperation::fromArray($op);
        }

        $modifiedHtml = $this->htmlModifier->applyEdits($html, $operations);

        return [
            'operations' => $operations,
            'modified_html' => $modifiedHtml
        ];
    }
}
