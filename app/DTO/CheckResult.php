<?php

namespace App\DTO;

use App\Enums\CheckStatus;
use App\Enums\RuleType;

final readonly class CheckResult
{
    public function __construct(
        public string $ruleName,
        public RuleType $ruleType,
        public CheckStatus $status,
        public ?string $issue = null,
        public ?string $reason = null,
        public ?string $expected = null,
        public ?string $actual = null,
        public ?string $selector = null,
        public ?string $attribute = null,
        public ?string $htmlSnippet = null,
    ) {}
}
