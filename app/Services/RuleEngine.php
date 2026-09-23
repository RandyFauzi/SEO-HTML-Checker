<?php

namespace App\Services;

use App\DTO\CheckResult;
use App\Enums\CheckStatus;
use App\Enums\RuleType;
use App\Models\SeoRule;
use App\Services\Rules\CompareAmpRuleEvaluator;
use App\Services\Rules\CountRuleEvaluator;
use App\Services\Rules\ExistsRuleEvaluator;
use App\Services\Rules\JsonLdRuleEvaluator;
use App\Services\Rules\LengthRuleEvaluator;
use App\Services\Rules\RegexRuleEvaluator;
use App\Services\Rules\RuleEvaluatorInterface;
use App\Services\Rules\TextMatchRuleEvaluator;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class RuleEngine
{
    protected array $evaluators = [];

    public function __construct()
    {
        $this->evaluators = [
            RuleType::Exist->value => new ExistsRuleEvaluator,
            RuleType::Count->value => new CountRuleEvaluator,
            RuleType::Length->value => new LengthRuleEvaluator,
            RuleType::TextMatch->value => new TextMatchRuleEvaluator,
            RuleType::Regex->value => new RegexRuleEvaluator,
            RuleType::CompareAmp->value => new CompareAmpRuleEvaluator,
            RuleType::JsonLd->value => new JsonLdRuleEvaluator,
        ];
    }

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): CheckResult
    {
        $type = $rule->rule_type;
        $typeString = $type instanceof RuleType ? $type->value : $type;

        if (! isset($this->evaluators[$typeString])) {
            return new CheckResult(
                ruleName: $rule->name,
                ruleType: $type,
                status: CheckStatus::Warning,
                category: $rule->category,
                issue: "Evaluator not found",
                reason: "No evaluator found for rule type: {$typeString}"
            );
        }

        /** @var RuleEvaluatorInterface $evaluator */
        $evaluator = $this->evaluators[$typeString];

        try {
            $result = $evaluator->evaluate($dom, $rule, $ampDom);

            $status = $result['passed'] ? CheckStatus::Passed : CheckStatus::from($rule->severity);

            return new CheckResult(
                ruleName: $rule->name,
                ruleType: $type,
                status: $status,
                category: $rule->category,
                issue: $result['issue'] ?? null,
                reason: $result['reason'] ?? null,
                expected: $result['expected'] ?? null,
                actual: $result['actual'] ?? null,
                selector: $result['selector'] ?? null,
                attribute: $result['attribute'] ?? null,
                htmlSnippet: $result['html_snippet'] ?? null
            );
        } catch (Exception $e) {
            return new CheckResult(
                ruleName: $rule->name,
                ruleType: $type,
                status: CheckStatus::Error,
                category: $rule->category,
                issue: "Evaluation Error",
                reason: 'Error evaluating rule: '.$e->getMessage()
            );
        }
    }
}

