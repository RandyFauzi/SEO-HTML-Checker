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
        $this->merge([
            'lp_urls_array' => $this->cleanUrls($this->lp_urls),
            'amp_urls_array' => $this->cleanUrls($this->amp_urls),
        ]);
    }

    private function cleanUrls($input): array
    {
        if (! $input) {
            return [];
        }
        $lines = array_filter(array_map('trim', explode("\n", $input)));

        // Remove duplicates and re-index
        return array_values(array_unique($lines));
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
