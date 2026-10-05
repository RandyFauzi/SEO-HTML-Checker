<?php

namespace App\DTO;

class DomEditOperation
{
    /**
     * @param string $selector The CSS selector to target the element(s).
     * @param string $action The action to perform: 'set_attr', 'set_text', 'replace_html', 'remove'.
     * @param string|null $attribute The attribute name (only required for 'set_attr').
     * @param string|null $value The new value for the attribute, text, or html.
     */
    public function __construct(
        public string $selector,
        public string $action,
        public ?string $attribute = null,
        public ?string $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            selector: $data['selector'] ?? '',
            action: $data['action'] ?? '',
            attribute: $data['attribute'] ?? null,
            value: $data['value'] ?? null,
        );
    }
}
