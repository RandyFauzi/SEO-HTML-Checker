<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $lpLines = $this->parseLines($this->lp_urls);
        $ampLines = $this->parseLines($this->amp_urls);

        // Make sure ampLines array has the same size as lpLines by padding with null if needed
        $ampLines = array_pad($ampLines, count($lpLines), null);

        $seenPairs = [];
        $uniqueLp = [];
        $uniqueAmp = [];

        foreach ($lpLines as $i => $lp) {
            $amp = $ampLines[$i] ?? null;
            // Treat empty string as null
            if ($amp === '') {
                $amp = null;
            }

            if (!$lp) {
                continue; // skip completely empty lines
            }

            $pairKey = $lp . '|' . ($amp ?? '');
            if (!isset($seenPairs[$pairKey])) {
                $seenPairs[$pairKey] = true;
                $uniqueLp[] = $lp;
                if ($amp !== null) {
                    $uniqueAmp[] = $amp;
                }
            }
        }

        $this->merge([
            'lp_urls_array' => $uniqueLp,
            'amp_urls_array' => $uniqueAmp,
        ]);
    }

    private function parseLines(?string $input): array
    {
        if (! $input) {
            return [];
        }
        return array_map('trim', explode("\n", $input));
    }

    public function rules(): array
    {
        return [
            'lp_urls' => 'required|string',
            'lp_urls_array' => 'required|array|max:10',
            'lp_urls_array.*' => 'url',

            'amp_urls' => 'nullable|string',
            'amp_urls_array' => 'nullable|array|max:10',
            'amp_urls_array.*' => 'url',
        ];
    }

    public function messages(): array
    {
        return [
            'lp_urls_array.max' => 'You can only check a maximum of 10 URLs at a time.',
            'amp_urls_array.max' => 'You can only check a maximum of 10 AMP URLs at a time.',
            'lp_urls_array.*.url' => 'One or more Landing Page URLs are invalid.',
            'amp_urls_array.*.url' => 'One or more AMP URLs are invalid.',
        ];
    }
}
