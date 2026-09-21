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
        public string $details,
        public ?string $htmlSnippet = null,
    ) {}
}
