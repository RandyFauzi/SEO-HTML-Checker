<?php

namespace App\Services;

use App\DTO\DomEditOperation;
use Symfony\Component\CssSelector\CssSelectorConverter;
use DOMDocument;
use DOMXPath;
use DOMElement;

class HtmlModifierService
{
    /**
     * Applies a list of DOM edit operations to the given HTML and returns the modified HTML.
     *
     * @param string $html
     * @param DomEditOperation[] $operations
     * @return string
     */
    public function applyEdits(string $html, array $operations): string
    {
        if (empty(trim($html)) || empty($operations)) {
            return $html;
        }

        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        
        // Load HTML with UTF-8 encoding support
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $converter = new CssSelectorConverter();

        foreach ($operations as $operation) {
            try {
                $xpathQuery = $converter->toXPath($operation->selector);
                $elements = $xpath->query($xpathQuery);

                if ($elements === false || $elements->length === 0) {
                    continue;
                }

                foreach ($elements as $element) {
                    if (!$element instanceof DOMElement) {
                        continue;
                    }

                    switch ($operation->action) {
                        case 'set_attr':
                            if ($operation->attribute) {
                                $element->setAttribute($operation->attribute, $operation->value ?? '');
                            }
                            break;

                        case 'set_text':
                            $element->nodeValue = htmlspecialchars($operation->value ?? '', ENT_QUOTES, 'UTF-8');
                            break;

                        case 'replace_html':
                            // To replace inner HTML, we remove all child nodes and append new parsed ones
                            while ($element->hasChildNodes()) {
                                $element->removeChild($element->firstChild);
                            }
                            if ($operation->value) {
                                $tempDom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $tempDom->loadHTML('<?xml encoding="UTF-8"><body>' . $operation->value . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                                libxml_clear_errors();
                                
                                $body = $tempDom->getElementsByTagName('body')->item(0);
                                if ($body) {
                                    foreach ($body->childNodes as $child) {
                                        $imported = $dom->importNode($child, true);
                                        $element->appendChild($imported);
                                    }
                                }
                            }
                            break;

                        case 'remove':
                            $element->parentNode?->removeChild($element);
                            break;
                    }
                }
            } catch (\Exception $e) {
                // Log exception but continue applying other operations
                \Illuminate\Support\Facades\Log::warning('Failed to apply DOM edit operation', [
                    'operation' => json_encode($operation),
                    'error' => $e->getMessage()
                ]);
            }
        }

        $result = $dom->saveHTML();
        // Remove the xml encoding declaration added at the beginning
        $result = str_replace('<?xml encoding="UTF-8">', '', $result);
        
        return trim($result);
    }
}
