<?php

namespace Database\Seeders;

use App\Models\SeoRule;
use Illuminate\Database\Seeder;

class SeoRuleSeeder extends Seeder
{
    public function run(): void
    {
        SeoRule::create([
            'name' => 'Check H1 Exist',
            'target_selector' => 'h1',
            'rule_type' => 'exist',
            'severity' => 'error',
        ]);

        SeoRule::create([
            'name' => 'Single H1 Count',
            'target_selector' => 'h1',
            'rule_type' => 'count',
            'expected_value' => '1',
            'severity' => 'warning',
        ]);

        SeoRule::create([
            'name' => 'Title Length Max 60',
            'target_selector' => 'title',
            'rule_type' => 'length_max',
            'expected_value' => '60',
            'severity' => 'warning',
        ]);

        SeoRule::create([
            'name' => 'Compare Title with AMP',
            'target_selector' => 'title',
            'rule_type' => 'compare_amp',
            'severity' => 'error',
        ]);

        SeoRule::create([
            'name' => 'Compare Meta Description with AMP',
            'target_selector' => 'meta[name="description"]',
            'rule_type' => 'compare_amp',
            'severity' => 'error',
        ]);
    }
}
