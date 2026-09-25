<?php

namespace App\DTO;

use Symfony\Component\DomCrawler\Crawler;

final readonly class AuditContext
{
    /**
     * @param  array<int, string>  $redirectChain
     */
    public function __construct(
        public Crawler $dom,
        public string $originalUrl,
        public string $finalUrl,
        public ?Crawler $ampDom = null,
        public ?string $ampUrl = null,
        public int $httpStatus = 200,
        public string $contentType = 'text/html',
        public array $redirectChain = [],
    ) {}
}
