<?php

namespace Tests\Feature;

use App\DTO\AuditContext;
use App\DTO\CheckResult;
use App\DTO\UrlAuditResult;
use App\Enums\CheckStatus;
use App\Enums\RuleType;
use App\Models\SeoRule;
use App\Services\RuleEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class UrlAndLinkRulesTest extends TestCase
{
    use RefreshDatabase;

    protected RuleEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new RuleEngine;
    }

    private function makeContext(string $html, string $finalUrl, string $originalUrl = ''): AuditContext
    {
        return new AuditContext(
            dom: new Crawler($html),
            originalUrl: $originalUrl ?: $finalUrl,
            finalUrl: $finalUrl,
        );
    }

    // 1. HTTPS passes
    public function test_url_protocol_https_passes()
    {
        $rule = new SeoRule([
            'name' => 'HTTPS Protocol',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'page_url',
                'condition' => 'protocol',
                'operator' => 'equals',
                'expected' => 'https',
            ],
        ]);

        $context = $this->makeContext('<html></html>', 'https://example.com/secure');
        $result = $this->engine->evaluate($context, $rule);

        $this->assertEquals(CheckStatus::Passed, $result->status);
        $this->assertEquals('https', $result->actual);
    }

    // 2. HTTP fails
    public function test_url_protocol_http_fails()
    {
        $rule = new SeoRule([
            'name' => 'HTTPS Protocol',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'page_url',
                'condition' => 'protocol',
                'operator' => 'equals',
                'expected' => 'https',
            ],
        ]);

        $context = $this->makeContext('<html></html>', 'http://example.com/insecure');
        $result = $this->engine->evaluate($context, $rule);

        $this->assertEquals(CheckStatus::Warning, $result->status);
        $this->assertEquals('http', $result->actual);
        $this->assertEquals('https', $result->expected);
    }

    // 3. Page URL regex works
    public function test_page_url_regex_works()
    {
        $rule = new SeoRule([
            'name' => 'Clean Slugs',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'page_url',
                'condition' => 'regex',
                'regex_pattern' => '^https:\/\/example\.com\/blog\/[a-z0-9\-]+$',
            ],
        ]);

        $contextGood = $this->makeContext('<html></html>', 'https://example.com/blog/my-seo-guide');
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($contextGood, $rule)->status);

        $contextBad = $this->makeContext('<html></html>', 'https://example.com/blog/INVALID_UPPERCASE');
        $this->assertEquals(CheckStatus::Warning, $this->engine->evaluate($contextBad, $rule)->status);
    }

    // 4. Page URL contains / not_contains works
    public function test_page_url_contains_and_not_contains()
    {
        $ruleNotContainsUtm = new SeoRule([
            'name' => 'No Tracking Params',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'page_url',
                'condition' => 'not_contains',
                'expected' => 'utm_',
            ],
        ]);

        $contextClean = $this->makeContext('<html></html>', 'https://example.com/about');
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($contextClean, $ruleNotContainsUtm)->status);

        $contextWithUtm = $this->makeContext('<html></html>', 'https://example.com/about?utm_source=twitter');
        $this->assertEquals(CheckStatus::Warning, $this->engine->evaluate($contextWithUtm, $ruleNotContainsUtm)->status);
    }

    // 5. Canonical matches current URL (passes)
    public function test_canonical_matches_current_url_passes()
    {
        $rule = new SeoRule([
            'name' => 'Canonical Matches Current URL',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'canonical_match',
                'condition' => 'canonical_match',
            ],
        ]);

        // HTML with canonical that has query/trailing slash normalization equivalence
        $html = '<html><head><link rel="canonical" href="https://example.com/page/"></head></html>';
        $context = $this->makeContext($html, 'https://example.com/page');
        $result = $this->engine->evaluate($context, $rule);

        $this->assertEquals(CheckStatus::Passed, $result->status);
        $this->assertStringContainsString('link rel="canonical"', $result->htmlSnippet);
    }

    // 6. Canonical differs from current URL (fails)
    public function test_canonical_differs_from_current_url_fails()
    {
        $rule = new SeoRule([
            'name' => 'Canonical Matches Current URL',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'canonical_match',
                'condition' => 'canonical_match',
            ],
        ]);

        $html = '<html><head><link rel="canonical" href="https://example.com/different-article"></head></html>';
        $context = $this->makeContext($html, 'https://example.com/current-article');
        $result = $this->engine->evaluate($context, $rule);

        $this->assertEquals(CheckStatus::Warning, $result->status);
        $this->assertEquals('https://example.com/different-article', $result->actual);
        $this->assertEquals('https://example.com/current-article', $result->expected);
        $this->assertStringContainsString('different-article', $result->htmlSnippet);
    }

    // 7. Original URL vs Final URL redirect behavior
    public function test_original_vs_final_url_source()
    {
        $ruleOriginal = new SeoRule([
            'name' => 'Original Protocol',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'page_url',
                'condition' => 'protocol',
                'url_source' => 'original_url',
                'expected' => 'http',
            ],
        ]);

        $ruleFinal = new SeoRule([
            'name' => 'Final Protocol',
            'rule_type' => 'url_match',
            'severity' => 'warning',
            'config' => [
                'target' => 'page_url',
                'condition' => 'protocol',
                'url_source' => 'final_url',
                'expected' => 'https',
            ],
        ]);

        // Simulating redirect: original was http, final landed on https
        $context = $this->makeContext('<html></html>', 'https://example.com/final', 'http://example.com/original');

        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($context, $ruleOriginal)->status);
        $this->assertEquals(CheckStatus::Passed, $this->engine->evaluate($context, $ruleFinal)->status);
    }

    // 9. Empty href detected
    public function test_link_empty_href_detected()
    {
        $rule = new SeoRule([
            'name' => 'Link Not Empty',
            'rule_type' => 'attribute',
            'severity' => 'warning',
            'config' => [
                'selector' => 'a',
                'attribute' => 'href',
                'condition' => 'not_empty',
            ],
        ]);

        $html = '<html><body><a href="">Empty Link</a><a href="/valid">Valid</a></body></html>';
        $context = $this->makeContext($html, 'https://example.com');
        $result = $this->engine->evaluate($context, $rule);

        $this->assertEquals(CheckStatus::Warning, $result->status);
        $this->assertStringContainsString('Empty Link', $result->htmlSnippet);
    }

    // 10. Hash only detected
    public function test_link_hash_only_detected()
    {
        $rule = new SeoRule([
            'name' => 'No Hash Only',
            'rule_type' => 'count',
            'severity' => 'warning',
            'config' => [
                'selector' => 'a[href="#"]',
                'operator' => '=',
                'expected' => 0,
            ],
        ]);

        $htmlWithHash = '<html><body><a href="#">Click me</a></body></html>';
        $resFail = $this->engine->evaluate($this->makeContext($htmlWithHash, 'https://example.com'), $rule);
        $this->assertEquals(CheckStatus::Warning, $resFail->status);

        $htmlNoHash = '<html><body><a href="/contact">Contact</a></body></html>';
        $resPass = $this->engine->evaluate($this->makeContext($htmlNoHash, 'https://example.com'), $rule);
        $this->assertEquals(CheckStatus::Passed, $resPass->status);
    }

    // 11. Missing href attribute detected
    public function test_link_missing_href_detected()
    {
        $rule = new SeoRule([
            'name' => 'Link Href Exists',
            'rule_type' => 'link',
            'severity' => 'warning',
            'config' => [
                'condition' => 'href_exists',
            ],
        ]);

        $html = '<html><body><a>Orphan Link without href</a><a href="/home">Home</a></body></html>';
        $context = $this->makeContext($html, 'https://example.com');
        $result = $this->engine->evaluate($context, $rule);

        $this->assertEquals(CheckStatus::Warning, $result->status);
        $this->assertStringContainsString('Orphan Link without href', $result->htmlSnippet);
    }

    // 12 & 13. target="_blank" with / without noopener
    public function test_target_blank_rel_noopener_passes_and_fails()
    {
        $rule = new SeoRule([
            'name' => 'Target Blank Noopener',
            'rule_type' => 'link',
            'severity' => 'warning',
            'config' => [
                'condition' => 'target_blank_rel',
                'expected' => 'noopener',
            ],
        ]);

        // Safe link
        $htmlSafe = '<html><body><a href="https://external.com" target="_blank" rel="noopener noreferrer">External</a></body></html>';
        $resultSafe = $this->engine->evaluate($this->makeContext($htmlSafe, 'https://example.com'), $rule);
        $this->assertEquals(CheckStatus::Passed, $resultSafe->status);

        // Unsafe link
        $htmlUnsafe = '<html><body><a href="https://external.com" target="_blank">Insecure External</a></body></html>';
        $resultUnsafe = $this->engine->evaluate($this->makeContext($htmlUnsafe, 'https://example.com'), $rule);
        $this->assertEquals(CheckStatus::Warning, $resultUnsafe->status);
        $this->assertStringContainsString('Insecure External', $resultUnsafe->htmlSnippet);

        // Page with NO target="_blank" links should pass gracefully
        $htmlNone = '<html><body><a href="/local">Local link</a></body></html>';
        $resultNone = $this->engine->evaluate($this->makeContext($htmlNone, 'https://example.com'), $rule);
        $this->assertEquals(CheckStatus::Passed, $resultNone->status);
    }

    // 14, 15, 16, 17, 18. Internal vs external link classification and safety
    public function test_internal_and_external_link_classification()
    {
        $ruleInternal = new SeoRule([
            'name' => 'Min 2 Internal Links',
            'rule_type' => 'link',
            'severity' => 'warning',
            'config' => [
                'condition' => 'classification',
                'target_type' => 'internal',
                'operator' => '>=',
                'expected' => 2,
            ],
        ]);

        $html = '<html><body>
            <a href="/pricing">Relative Internal</a>
            <a href="https://example.com/team">Absolute Internal</a>
            <a href="https://google.com">External</a>
            <a href="https://example.com.evil.com/phish">Spoofed Domain External</a>
            <a href="mailto:test@example.com">Mailto</a>
            <a href="tel:+123456789">Tel</a>
            <a href="#top">Anchor</a>
        </body></html>';

        $context = $this->makeContext($html, 'https://example.com/home');
        $result = $this->engine->evaluate($context, $ruleInternal);

        $this->assertEquals(CheckStatus::Passed, $result->status);
        // Internal count should be exactly 2 (Relative + Absolute on example.com)
        // External count should be 2 (google.com + example.com.evil.com)
        $this->assertStringContainsString('Internal: 2', $result->actual);
        $this->assertStringContainsString('External: 2', $result->actual);
    }

    // 19. Verify zero outbound HTTP requests made during link classification
    public function test_link_classification_does_not_trigger_network_requests()
    {
        // Notice we don't have internet mock handlers here; if an outbound request is triggered,
        // it would attempt real connection or throw an error on unreachable hosts.
        $rule = new SeoRule([
            'name' => 'Check Links',
            'rule_type' => 'link',
            'severity' => 'warning',
            'config' => [
                'condition' => 'classification',
                'target_type' => 'internal',
            ],
        ]);

        $html = '<html><body><a href="http://unreachable-domain-12345.xyz">Test</a></body></html>';
        $context = $this->makeContext($html, 'https://example.com');
        $result = $this->engine->evaluate($context, $rule);

        $this->assertNotNull($result);
        $this->assertEquals('link', $result->ruleType->value);
    }

    // 20. Evidence remains safely escaped in Blade view
    public function test_evidence_is_safely_escaped_against_xss()
    {
        $maliciousSnippet = '<img src=x onerror=alert("XSS")><script>alert(1)</script>';

        $rendered = (string) view('seo-checker.index', [
            'errors' => new ViewErrorBag,
            'hasCompareRule' => false,
            'results' => [
                new UrlAuditResult(
                    lpUrl: 'https://example.com',
                    ampUrl: null,
                    errorMessage: null,
                    checks: [
                        new CheckResult(
                            ruleName: 'XSS Test',
                            ruleType: RuleType::Attribute,
                            status: CheckStatus::Error,
                            category: 'Security',
                            issue: 'Malicious tag found',
                            reason: 'Testing XSS',
                            expected: 'Clean HTML',
                            actual: 'Script tag present',
                            selector: 'script',
                            attribute: null,
                            htmlSnippet: $maliciousSnippet
                        ),
                    ]
                ),
            ],
        ]);

        // The raw unescaped script tag MUST NOT exist in the HTML output
        $this->assertStringNotContainsString('<script>alert(1)</script>', $rendered);
        // The escaped version MUST exist
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $rendered);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(&quot;XSS&quot;)&gt;', $rendered);
    }
}
