<?php

namespace Tests\Feature;

use App\Enums\CheckStatus;
use App\Models\SeoRule;
use App\Services\RuleEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class RuleEngineTest extends TestCase
{
    use RefreshDatabase;

    protected RuleEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new RuleEngine();
    }

    public function test_scenario_a_admin_rule_changes_take_effect_immediately()
    {
        // 1. Admin creates a rule in database
        $rule = SeoRule::create([
            'code' => 'TEST_TITLE_LENGTH',
            'name' => 'Test Title Length',
            'category' => 'Metadata',
            'rule_type' => 'length',
            'config' => [
                'selector' => 'title',
                'operator' => 'between',
                'min' => 30,
                'max' => 60
            ],
            'severity' => 'warning',
            'is_active' => true,
        ]);

        $html = '<!DOCTYPE html><html><head><title>This title is exactly 65 characters long, which is over the max limit</title></head><body></body></html>';
        $crawler = new Crawler($html);

        // 2. Run checker -> Should FAIL (Warning) because 65 > 60
        $result1 = $this->engine->evaluate($crawler, $rule);
        $this->assertEquals(CheckStatus::Warning, $result1->status);
        $this->assertEquals("69 karakter", $result1->actual);

        // 3. Admin edits rule config to max 70
        $rule->update([
            'config' => [
                'selector' => 'title',
                'operator' => 'between',
                'min' => 30,
                'max' => 70
            ]
        ]);

        // 4. Run checker again on same HTML -> Should PASS
        $result2 = $this->engine->evaluate($crawler, $rule->fresh());
        $this->assertEquals(CheckStatus::Passed, $result2->status);
        $this->assertEquals("69 karakter", $result2->actual);
    }

    public function test_special_evaluator_h2_hierarchy()
    {
        $rule = SeoRule::create([
            'code' => 'H2_HIERARCHY',
            'name' => 'Heading Hierarchy',
            'category' => 'Headings',
            'rule_type' => 'special',
            'config' => ['special_type' => 'h2_hierarchy'],
            'severity' => 'warning',
        ]);

        // Bad HTML: h3 before h2
        $badHtml = '<html><body><h1>Title</h1><h3>Sub 3</h3><h2>Sub 2</h2></body></html>';
        $crawlerBad = new Crawler($badHtml);
        $resultBad = $this->engine->evaluate($crawlerBad, $rule);
        $this->assertEquals(CheckStatus::Warning, $resultBad->status);

        // Good HTML: h2 before h3
        $goodHtml = '<html><body><h1>Title</h1><h2>Sub 2</h2><h3>Sub 3</h3></body></html>';
        $crawlerGood = new Crawler($goodHtml);
        $resultGood = $this->engine->evaluate($crawlerGood, $rule);
        $this->assertEquals(CheckStatus::Passed, $resultGood->status);
    }

    public function test_attribute_evaluator_supports_complex_conditions()
    {
        $rule = SeoRule::create([
            'code' => 'NOINDEX_DETECTED',
            'name' => 'Noindex Detected',
            'category' => 'Metadata',
            'rule_type' => 'attribute',
            'config' => [
                'selector' => 'meta[name="robots"]',
                'attribute' => 'content',
                'condition' => 'not_contains',
                'expected_value' => 'noindex'
            ],
            'severity' => 'warning',
        ]);

        $htmlWithNoindex = '<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>';
        $crawler = new Crawler($htmlWithNoindex);
        
        $result = $this->engine->evaluate($crawler, $rule);
        $this->assertEquals(CheckStatus::Warning, $result->status);
        $this->assertEquals("Tidak sesuai (pada beberapa elemen)", $result->actual);
    }
}
