<?php

namespace Tests\Feature;

use App\Enums\CheckStatus;
use App\Models\SeoRule;
use App\Services\RuleEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class RuleEngineCapabilitiesAuditTest extends TestCase
{
    use RefreshDatabase;

    protected RuleEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new RuleEngine;
    }

    public function test_generic_selectors_and_complex_css()
    {
        $html = '<!DOCTYPE html>
        <html lang="id">
        <head>
            <title>Title Text</title>
            <meta name="description" content="Desc">
            <meta property="og:title" content="OG Title">
            <meta property="og:description" content="OG Desc">
            <link rel="canonical" href="https://example.com">
            <script type="application/ld+json">{"@context":"https://schema.org","@type":"Article"}</script>
        </head>
        <body>
            <h1>Heading 1</h1>
            <h2>Heading 2</h2>
            <a href="https://example.com">Link</a>
            <img src="img.jpg" alt="Photo">
        </body>
        </html>';

        $crawler = new Crawler($html);

        $selectorsToTest = [
            'title',
            'h1',
            'h2',
            'meta[name="description"]',
            'link[rel="canonical"]',
            'script[type="application/ld+json"]',
            'a[href]',
            'img[alt]',
            'html[lang]',
            'meta[property^="og:"]', // complex starts-with selector
        ];

        foreach ($selectorsToTest as $selector) {
            $rule = new SeoRule([
                'name' => "Check {$selector}",
                'rule_type' => 'exist',
                'severity' => 'error',
                'config' => ['selector' => $selector],
            ]);

            $result = $this->engine->evaluate($crawler, $rule);
            $this->assertEquals(CheckStatus::Passed, $result->status, "Failed on selector: {$selector}");
        }
    }

    public function test_dynamic_attributes_support()
    {
        $html = '<html lang="en"><head>
            <meta name="description" content="Desc content">
            <meta property="og:site_name" content="Site">
            <link rel="canonical" href="https://example.com" type="text/html">
        </head>
        <body>
            <img src="/logo.png" alt="Company Logo">
            <a href="/about" rel="nofollow">About</a>
        </body></html>';

        $crawler = new Crawler($html);

        $attributeChecks = [
            ['selector' => 'link[rel="canonical"]', 'attribute' => 'href', 'expected' => 'https://example.com'],
            ['selector' => 'link[rel="canonical"]', 'attribute' => 'type', 'expected' => 'text/html'],
            ['selector' => 'link[rel="canonical"]', 'attribute' => 'rel', 'expected' => 'canonical'],
            ['selector' => 'img', 'attribute' => 'src', 'expected' => '/logo.png'],
            ['selector' => 'img', 'attribute' => 'alt', 'expected' => 'Company Logo'],
            ['selector' => 'a', 'attribute' => 'rel', 'expected' => 'nofollow'],
            ['selector' => 'meta[name="description"]', 'attribute' => 'name', 'expected' => 'description'],
            ['selector' => 'meta[name="description"]', 'attribute' => 'content', 'expected' => 'Desc content'],
            ['selector' => 'meta[property="og:site_name"]', 'attribute' => 'property', 'expected' => 'og:site_name'],
            ['selector' => 'html', 'attribute' => 'lang', 'expected' => 'en'],
        ];

        foreach ($attributeChecks as $item) {
            $rule = new SeoRule([
                'name' => "Attr test {$item['attribute']}",
                'rule_type' => 'attribute',
                'severity' => 'error',
                'config' => [
                    'selector' => $item['selector'],
                    'attribute' => $item['attribute'],
                    'condition' => 'equals',
                    'expected_value' => $item['expected'],
                ],
            ]);

            $result = $this->engine->evaluate($crawler, $rule);
            $this->assertEquals(CheckStatus::Passed, $result->status, "Failed on attribute {$item['attribute']} with selector {$item['selector']}");
        }
    }

    public function test_text_matching_operators_and_case_sensitivity()
    {
        $html = '<html><head><title>SEO Checker</title></head></html>';
        $crawler = new Crawler($html);

        // Case-insensitivity check: 'SEO' vs 'seo'
        $ruleUpper = new SeoRule([
            'name' => 'Contains SEO',
            'rule_type' => 'text_match',
            'severity' => 'error',
            'config' => ['selector' => 'title', 'operator' => 'contains', 'expected_value' => 'SEO'],
        ]);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($crawler, $ruleUpper)->status);

        $ruleLower = new SeoRule([
            'name' => 'Contains seo',
            'rule_type' => 'text_match',
            'severity' => 'error',
            'config' => ['selector' => 'title', 'operator' => 'contains', 'expected_value' => 'seo'],
        ]);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($crawler, $ruleLower)->status);

        // Operators: equals, starts_with, ends_with, not_contains
        $operators = [
            ['op' => 'equals', 'val' => 'seo checker', 'pass' => true],
            ['op' => 'equals', 'val' => 'different', 'pass' => false],
            ['op' => 'starts_with', 'val' => 'seo', 'pass' => true],
            ['op' => 'starts_with', 'val' => 'checker', 'pass' => false],
            ['op' => 'ends_with', 'val' => 'checker', 'pass' => true],
            ['op' => 'ends_with', 'val' => 'seo', 'pass' => false],
            ['op' => 'not_contains', 'val' => 'google', 'pass' => true],
            ['op' => 'not_contains', 'val' => 'checker', 'pass' => false],
        ];

        foreach ($operators as $item) {
            $rule = new SeoRule([
                'name' => "Op test {$item['op']}",
                'rule_type' => 'text_match',
                'severity' => 'warning',
                'config' => ['selector' => 'title', 'operator' => $item['op'], 'expected_value' => $item['val']],
            ]);

            $result = $this->engine->evaluate($crawler, $rule);
            $expectedStatus = $item['pass'] ? CheckStatus::Passed : CheckStatus::Warning;
            $this->assertEquals($expectedStatus, $result->status, "Failed on text_match operator: {$item['op']} with value: {$item['val']}");
        }
    }

    public function test_element_count_operators_and_counts()
    {
        $html = '<html><body>
            <h1>Main Title</h1>
            <h2>Section 1</h2>
            <h2>Section 2</h2>
            <h2>Section 3</h2>
        </body></html>';

        $crawler = new Crawler($html);

        $countTests = [
            // 0 elements
            ['selector' => 'h3', 'operator' => '=', 'expected' => 0, 'pass' => true],
            ['selector' => 'h3', 'operator' => '>', 'expected' => 0, 'pass' => false],
            // 1 element
            ['selector' => 'h1', 'operator' => '=', 'expected' => 1, 'pass' => true],
            ['selector' => 'h1', 'operator' => '!=', 'expected' => 1, 'pass' => false],
            // multiple elements
            ['selector' => 'h2', 'operator' => '=', 'expected' => 3, 'pass' => true],
            ['selector' => 'h2', 'operator' => '>=', 'expected' => 2, 'pass' => true],
            ['selector' => 'h2', 'operator' => '<', 'expected' => 2, 'pass' => false],
            ['selector' => 'h2', 'operator' => 'between', 'min' => 2, 'max' => 5, 'pass' => true],
        ];

        foreach ($countTests as $item) {
            $config = [
                'selector' => $item['selector'],
                'operator' => $item['operator'],
            ];
            if (isset($item['expected'])) {
                $config['expected'] = $item['expected'];
            }
            if (isset($item['min'])) {
                $config['min'] = $item['min'];
                $config['max'] = $item['max'];
            }

            $rule = new SeoRule([
                'name' => "Count test {$item['selector']}",
                'rule_type' => 'count',
                'severity' => 'error',
                'config' => $config,
            ]);

            $result = $this->engine->evaluate($crawler, $rule);
            $expectedStatus = $item['pass'] ? CheckStatus::Passed : CheckStatus::Error;
            $this->assertEquals($expectedStatus, $result->status, "Failed count: {$item['selector']} {$item['operator']}");
        }
    }

    public function test_length_on_element_text_and_attributes()
    {
        $html = '<html><head>
            <title>1234567890</title> <!-- 10 chars -->
            <meta name="description" content="123456789012345"> <!-- 15 chars -->
        </head></html>';

        $crawler = new Crawler($html);

        // 1. Element text length (exact, between, min, max)
        $ruleTitleExact = new SeoRule([
            'name' => 'Title exact 10',
            'rule_type' => 'length',
            'severity' => 'warning',
            'config' => ['selector' => 'title', 'operator' => '=', 'expected' => 10],
        ]);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($crawler, $ruleTitleExact)->status);

        $ruleTitleBetween = new SeoRule([
            'name' => 'Title between 5-15',
            'rule_type' => 'length',
            'severity' => 'warning',
            'config' => ['selector' => 'title', 'operator' => 'between', 'min' => 5, 'max' => 15],
        ]);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($crawler, $ruleTitleBetween)->status);

        // 2. Attribute length
        $ruleMetaLength = new SeoRule([
            'name' => 'Meta desc between 10-20',
            'rule_type' => 'length',
            'severity' => 'warning',
            'config' => [
                'selector' => 'meta[name="description"]',
                'attribute' => 'content',
                'operator' => 'between',
                'min' => 10,
                'max' => 20,
            ],
        ]);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($crawler, $ruleMetaLength)->status);
    }

    public function test_regex_evaluator_on_attributes_and_text()
    {
        $html = '<html><head>
            <link rel="canonical" href="https://example.com/page">
            <title>Product #1234 - Shop</title>
        </head></html>';

        $crawler = new Crawler($html);

        // 1. Regex on attribute href
        $ruleHttps = new SeoRule([
            'name' => 'Canonical HTTPS',
            'rule_type' => 'regex',
            'severity' => 'error',
            'config' => [
                'selector' => 'link[rel="canonical"]',
                'attribute' => 'href',
                'regex_pattern' => '^https:\/\/',
            ],
        ]);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($crawler, $ruleHttps)->status);

        // 2. Regex on element text
        $ruleIdInTitle = new SeoRule([
            'name' => 'Product ID in title',
            'rule_type' => 'regex',
            'severity' => 'warning',
            'config' => [
                'selector' => 'title',
                'regex_pattern' => '#\d{4}#',
            ],
        ]);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($crawler, $ruleIdInTitle)->status);

        // 3. Invalid regex safety (should not throw 500 error / unhandled exception)
        $ruleInvalidRegex = new SeoRule([
            'name' => 'Invalid Regex',
            'rule_type' => 'regex',
            'severity' => 'error',
            'config' => [
                'selector' => 'title',
                'regex_pattern' => '[unclosed-group',
            ],
        ]);
        $resInvalid = $this->engine->evaluate($crawler, $ruleInvalidRegex);
        $this->assertEquals(CheckStatus::Error, $resInvalid->status);
        $this->assertStringContainsString('gagal', strtolower($resInvalid->issue.' '.$resInvalid->reason));
    }

    public function test_missing_element_behavior()
    {
        $html = '<html><body></body></html>';
        $crawler = new Crawler($html);

        $rules = [
            new SeoRule(['name' => 'E1', 'rule_type' => 'exist', 'severity' => 'error', 'config' => ['selector' => 'nonexistent']]),
            new SeoRule(['name' => 'E2', 'rule_type' => 'length', 'severity' => 'warning', 'config' => ['selector' => 'nonexistent']]),
            new SeoRule(['name' => 'E3', 'rule_type' => 'text_match', 'severity' => 'warning', 'config' => ['selector' => 'nonexistent', 'expected_value' => 'abc']]),
            new SeoRule(['name' => 'E4', 'rule_type' => 'attribute', 'severity' => 'error', 'config' => ['selector' => 'nonexistent', 'attribute' => 'href']]),
            new SeoRule(['name' => 'E5', 'rule_type' => 'regex', 'severity' => 'error', 'config' => ['selector' => 'nonexistent', 'regex_pattern' => '.*']]),
        ];

        foreach ($rules as $rule) {
            $result = $this->engine->evaluate($crawler, $rule);
            $this->assertFalse($result->status === CheckStatus::Passed, "Rule {$rule->name} should not pass on missing element");
            $this->assertNotNull($result->issue);
            $this->assertNotNull($result->reason);
        }
    }
}
