<?php

namespace App\DTO;

final readonly class UrlAuditResult
{
    public function __construct(
        public string $lpUrl,
        public ?string $ampUrl,
        public ?string $errorMessage,
        /** @var CheckResult[] */
        public array $checks,
        /** @var string[] */
        public array $redirectChain = [],
    ) {}
}
