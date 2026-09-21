<?php

namespace App\Services;

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
            'exist' => new ExistsRuleEvaluator,
            'count' => new CountRuleEvaluator,
            'length' => new LengthRuleEvaluator,
            'length_max' => new LengthRuleEvaluator, // backward compatibility
            'text_match' => new TextMatchRuleEvaluator,
            'regex' => new RegexRuleEvaluator,
            'compare_amp' => new CompareAmpRuleEvaluator,
            'json_ld' => new JsonLdRuleEvaluator,
        ];
    }

    public function evaluate(Crawler $dom, SeoRule $rule, ?Crawler $ampDom = null): array
    {
        $type = $rule->rule_type;

        if (! isset($this->evaluators[$type])) {
            return [
                'status' => 'warning',
                'details' => "No evaluator found for rule type: {$type}",
                'html_snippet' => null,
            ];
        }

        /** @var RuleEvaluatorInterface $evaluator */
        $evaluator = $this->evaluators[$type];

        try {
            $result = $evaluator->evaluate($dom, $rule, $ampDom);

            $status = $result['passed'] ? 'passed' : $rule->severity;

            return [
                'status' => $status,
                'details' => $result['details'],
                'html_snippet' => $result['html_snippet'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'details' => 'Error evaluating rule: '.$e->getMessage(),
                'html_snippet' => null,
            ];
        }
    }
}
