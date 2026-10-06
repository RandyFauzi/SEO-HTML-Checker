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
Output HARUS berformat JSON object yang memiliki key "operations" berisi daftar operasi.

Skema JSON yang diwajibkan:
{
  "operations": [
    {
      "selector": "string (CSS Selector valid untuk elemen yang akan diedit)",
      "action": "string (Hanya pilih salah satu: 'set_attr', 'set_text', 'replace_html', 'remove', 'append_html', 'prepend_html', 'insert_before', 'insert_after')",
      "attribute": "string (Nama atribut, contoh: 'src', 'href', 'alt'. WAJIB ada jika action = 'set_attr', biarkan kosong untuk action lain)",
      "value": "string (Nilai baru untuk atribut/teks/html. Gunakan HTML valid jika menambah/mengubah elemen)"
    }
  ]
}

Aturan:
1. Gunakan CSS selector yang paling spesifik.
2. Jaga validitas AMP jika ada instruksi terkait AMP.
EOF;

        $userPrompt = "Instruksi User:\n" . $prompt . "\n\nHTML Target:\n" . $html;

        // In a real app, dynamically resolve the model from config
        $model = env('OPENAI_REMEDIATION_MODEL', 'gpt-4o-mini');
        
        try {
            $operationsData = $this->aiClient->generateStructuredOutput($model, $systemPrompt . "\n\n" . $userPrompt);
        } catch (\Exception $e) {
            Log::error('AI DOM Editor generateStructuredOutput failed', ['error' => $e->getMessage()]);
            // Re-throw so the controller can send it to the UI
            throw new \RuntimeException('AI Error: ' . $e->getMessage());
        }
        
        if (!is_array($operationsData) || !isset($operationsData['operations'])) {
            Log::error('AI DOM Editor failed to return valid JSON operations', ['response' => $operationsData]);
            return ['operations' => [], 'modified_html' => $html];
        }

        $operations = [];
        foreach ($operationsData['operations'] as $op) {
            $operations[] = DomEditOperation::fromArray($op);
        }

        $modifiedHtml = $this->htmlModifier->applyEdits($html, $operations);

        return [
            'operations' => $operations,
            'modified_html' => $modifiedHtml
        ];
    }
}
