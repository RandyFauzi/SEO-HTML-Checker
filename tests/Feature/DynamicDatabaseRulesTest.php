<?php

namespace Tests\Feature;

use App\DTO\UrlAuditResult;
use App\Enums\CheckStatus;
use App\Models\SeoRule;
use App\Services\HtmlFetcher;
use App\Services\RuleEngine;
use App\Services\SeoAuditService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicDatabaseRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function createMockAuditService(string $html): SeoAuditService
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/html'], $html),
            new Response(200, ['Content-Type' => 'text/html'], $html),
            new Response(200, ['Content-Type' => 'text/html'], $html),
            new Response(200, ['Content-Type' => 'text/html'], $html),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $fetcher = new HtmlFetcher;
        $fetcher->setClient($client);

        return new SeoAuditService($fetcher, new RuleEngine);
    }

    public function test_active_rules_are_executed_and_inactive_rules_are_excluded()
    {
        SeoRule::create([
            'code' => 'ACTIVE_RULE_1',
            'name' => 'Active Rule 1',
            'category' => 'Document',
            'rule_type' => 'exist',
            'config' => ['selector' => 'h1'],
            'severity' => 'error',
            'is_active' => true,
        ]);

        SeoRule::create([
            'code' => 'INACTIVE_RULE_2',
            'name' => 'Inactive Rule 2',
            'category' => 'Document',
            'rule_type' => 'exist',
            'config' => ['selector' => 'h2'],
            'severity' => 'warning',
            'is_active' => false,
        ]);

        $auditService = $this->createMockAuditService('<html><body><h1>Hello World</h1></body></html>');
        $results = $auditService->audit(['https://example.com/page1']);

        /** @var UrlAuditResult $auditResult */
        $auditResult = $results[0];

        // Should only contain 1 check (the active one)
        $this->assertCount(1, $auditResult->checks);
        $this->assertEquals('Active Rule 1', $auditResult->checks[0]->ruleName);
    }

    public function test_editing_rule_in_database_changes_checker_result_without_code_changes()
    {
        // 1. Initial configuration: max 60 chars
        $rule = SeoRule::create([
            'code' => 'DYNAMIC_TITLE_LEN',
            'name' => 'Dynamic Title Length',
            'category' => 'Metadata',
            'rule_type' => 'length',
            'config' => [
                'selector' => 'title',
                'operator' => '<=',
                'max' => 60,
            ],
            'severity' => 'warning',
            'is_active' => true,
        ]);

        // HTML with a 65-char title
        $title65 = 'This title is 65 characters long for dynamic testing 123456789012';
        $html = "<html><head><title>{$title65}</title></head><body></body></html>";

        // Audit A with max 60 -> should produce Warning
        $auditServiceA = $this->createMockAuditService($html);
        $resultsA = $auditServiceA->audit(['https://example.com/test-a']);
        $this->assertEquals(CheckStatus::Warning, $resultsA[0]->checks[0]->status);

        // 2. Admin edits rule in database to max 70
        $rule->update([
            'config' => [
                'selector' => 'title',
                'operator' => '<=',
                'max' => 70,
            ],
        ]);

        // Audit B with updated config (max 70) -> should produce Passed
        $auditServiceB = $this->createMockAuditService($html);
        $resultsB = $auditServiceB->audit(['https://example.com/test-b']);
        $this->assertEquals(CheckStatus::Passed, $resultsB[0]->checks[0]->status);
    }

    public function test_newly_created_rule_is_immediately_picked_up_by_checker()
    {
        $html = '<html><body><div class="custom-badge">Featured</div></body></html>';

        $auditService = $this->createMockAuditService($html);

        // Before creating rule: 0 checks
        $results1 = $auditService->audit(['https://example.com/page1']);
        $this->assertCount(0, $results1[0]->checks);

        // Create custom rule dynamically
        SeoRule::create([
            'code' => 'CUSTOM_BADGE_EXISTS',
            'name' => 'Custom Badge Exists',
            'category' => 'Custom',
            'rule_type' => 'exist',
            'config' => ['selector' => '.custom-badge'],
            'severity' => 'error',
            'is_active' => true,
        ]);

        // Audit immediately picks up the new rule
        $results2 = $auditService->audit(['https://example.com/page1']);
        $this->assertCount(1, $results2[0]->checks);
        $this->assertEquals('Custom Badge Exists', $results2[0]->checks[0]->ruleName);
        $this->assertEquals(CheckStatus::Passed, $results2[0]->checks[0]->status);
    }

    public function test_deleting_rule_removes_it_from_subsequent_audits()
    {
        $rule = SeoRule::create([
            'code' => 'TO_BE_DELETED',
            'name' => 'To Be Deleted Rule',
            'category' => 'Test',
            'rule_type' => 'exist',
            'config' => ['selector' => 'title'],
            'severity' => 'warning',
            'is_active' => true,
        ]);

        $html = '<html><head><title>Test</title></head></html>';
        $auditService = $this->createMockAuditService($html);

        $resultsBefore = $auditService->audit(['https://example.com/page']);
        $this->assertCount(1, $resultsBefore[0]->checks);

        // Admin deletes the rule
        $rule->delete();

        $resultsAfter = $auditService->audit(['https://example.com/page']);
        $this->assertCount(0, $resultsAfter[0]->checks);
    }

    public function test_admin_toggle_endpoint_immediately_updates_active_state()
    {
        $rule = SeoRule::create([
            'code' => 'TOGGLE_TEST',
            'name' => 'Toggle Test Rule',
            'category' => 'Test',
            'rule_type' => 'exist',
            'config' => ['selector' => 'body'],
            'severity' => 'error',
            'is_active' => true,
        ]);

        $this->assertTrue($rule->is_active);

        // Trigger toggle via controller
        $response = $this->patchJson(route('admin.rules.toggle', $rule));
        $response->assertOk();
        $response->assertJson(['is_active' => false]);

        $this->assertFalse($rule->fresh()->is_active);
    }
}
