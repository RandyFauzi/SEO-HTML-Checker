<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class SeoCheckerService
{
    /**
     * Check a single URL against the active rules.
     */
    public function checkSingleUrl(string $url, Collection $activeRules): array
    {
        $result = [
            'url' => $url,
            'status' => 'success',
            'checks' => [],
            'error_message' => null,
        ];

        try {
            $html = SafeHttpClient::get($url);
            $crawler = new Crawler($html);

            foreach ($activeRules as $rule) {
                // Ignore compare_amp rule here as it needs 2 URLs
                if ($rule->rule_type === 'compare_amp') {
                    continue;
                }

                $checkResult = $this->evaluateRule($crawler, $rule);
                $result['checks'][] = $checkResult;
            }
        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error_message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Compare Landing Page (LP) and AMP page for parity based on rules.
     */
    public function compareLpAndAmp(string $lpUrl, string $ampUrl, Collection $activeRules): array
    {
        $result = [
            'lp_url' => $lpUrl,
            'amp_url' => $ampUrl,
            'status' => 'success',
            'checks' => [],
            'error_message' => null,
        ];

        try {
            $lpHtml = SafeHttpClient::get($lpUrl);
            $ampHtml = SafeHttpClient::get($ampUrl);

            $lpCrawler = new Crawler($lpHtml);
            $ampCrawler = new Crawler($ampHtml);

            foreach ($activeRules as $rule) {
                if ($rule->rule_type !== 'compare_amp') {
                    continue;
                }

                $selector = $rule->target_selector;
                $lpNodes = $lpCrawler->filter($selector);
                $ampNodes = $ampCrawler->filter($selector);

                $lpValue = $lpNodes->count() > 0 ? trim($lpNodes->first()->text()) : null;
                $ampValue = $ampNodes->count() > 0 ? trim($ampNodes->first()->text()) : null;

                $lpSnippet = $lpNodes->count() > 0 ? $lpNodes->first()->outerHtml() : null;
                $ampSnippet = $ampNodes->count() > 0 ? $ampNodes->first()->outerHtml() : null;

                // Also check content attribute for meta tags
                if (str_contains($selector, 'meta')) {
                    $lpValue = $lpNodes->count() > 0 ? trim($lpNodes->first()->attr('content') ?? '') : null;
                    $ampValue = $ampNodes->count() > 0 ? trim($ampNodes->first()->attr('content') ?? '') : null;
                }

                $passed = ($lpValue === $ampValue && $lpValue !== null);

                $result['checks'][] = [
                    'rule_name' => $rule->name,
                    'rule_type' => $rule->rule_type,
                    'severity' => $rule->severity,
                    'passed' => $passed,
                    'details' => $passed
                        ? 'Match found.'
                        : "Mismatch. LP: '{$lpValue}' | AMP: '{$ampValue}'",
                    'html_snippet' => "LP:\n".($lpSnippet ?? 'N/A')."\n\nAMP:\n".($ampSnippet ?? 'N/A'),
                ];
            }
        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error_message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Evaluate a specific rule against the DOM Crawler.
     */
    private function evaluateRule(Crawler $crawler, $rule): array
    {
        $passed = false;
        $details = '';
        $nodes = null;
        $htmlSnippet = null;

        try {
            if ($rule->target_selector && $rule->rule_type !== 'json_ld') {
                $nodes = $crawler->filter($rule->target_selector);
            }

            switch ($rule->rule_type) {
                case 'exist':
                    $passed = $nodes && $nodes->count() > 0;
                    $details = $passed ? 'Selector found.' : 'Selector not found.';
                    if ($passed) {
                        $htmlSnippet = $nodes->first()->outerHtml();
                    }
                    break;
                case 'count':
                    $expected = (int) $rule->expected_value;
                    $actual = $nodes ? $nodes->count() : 0;
                    $passed = $actual === $expected;
                    $details = "Expected {$expected}, found {$actual}.";
                    if ($actual > 0) {
                        $htmlSnippet = $nodes->first()->outerHtml();
                    }
                    break;
                case 'length_max':
                    if ($nodes && $nodes->count() > 0) {
                        $htmlSnippet = $nodes->first()->outerHtml();
                        $text = '';
                        if (str_contains($rule->target_selector, 'meta')) {
                            $text = $nodes->first()->attr('content') ?? '';
                        } else {
                            $text = $nodes->first()->text();
                        }
                        $text = trim($text);
                        $length = mb_strlen($text);
                        $max = (int) $rule->expected_value;
                        $passed = $length <= $max;
                        $details = "Length is {$length} (Max: {$max}).";
                    } else {
                        $details = 'Selector not found for length check.';
                    }
                    break;
                case 'regex':
                    if ($nodes && $nodes->count() > 0) {
                        $htmlSnippet = $nodes->first()->outerHtml();
                        $text = '';
                        if (str_contains($rule->target_selector, 'meta')) {
                            $text = $nodes->first()->attr('content') ?? '';
                        } else {
                            $text = $nodes->first()->text();
                        }
                        $text = trim($text);
                        $regex = $rule->expected_value;
                        // Use @ as delimiter if not provided
                        if (! str_starts_with($regex, '/') && ! str_starts_with($regex, '#') && ! str_starts_with($regex, '@')) {
                            $regex = '@'.$regex.'@';
                        }
                        $passed = preg_match($regex, $text) === 1;
                        $details = $passed ? 'Matched regex.' : 'Did not match regex.';
                    } else {
                        $details = 'Selector not found for regex check.';
                    }
                    break;
                case 'json_ld':
                case 'schema':
                    $jsonNodes = $crawler->filter('script[type="application/ld+json"]');
                    if ($jsonNodes->count() > 0) {
                        $expected = $rule->expected_value;
                        $foundMatch = false;
                        $firstSnippet = '';

                        $jsonNodes->each(function (Crawler $node, $i) use ($expected, &$foundMatch, &$firstSnippet) {
                            if ($i === 0) {
                                $firstSnippet = $node->outerHtml();
                            }
                            $jsonText = $node->text();

                            // Simple text search as fallback
                            if (str_contains($jsonText, $expected)) {
                                $foundMatch = true;
                                $firstSnippet = $node->outerHtml(); // grab the matching snippet
                            } else {
                                // Try decoding and recursive search if formatted as key:value
                                $data = json_decode($jsonText, true);
                                if (is_array($data)) {
                                    if (str_contains($expected, ':')) {
                                        [$key, $val] = explode(':', $expected, 2);
                                        $key = trim($key);
                                        $val = trim($val);
                                        if ($this->arrayContainsKeyValue($data, $key, $val)) {
                                            $foundMatch = true;
                                            $firstSnippet = $node->outerHtml();
                                        }
                                    } else {
                                        if ($this->arrayContainsValue($data, trim($expected))) {
                                            $foundMatch = true;
                                            $firstSnippet = $node->outerHtml();
                                        }
                                    }
                                }
                            }
                        });

                        $passed = $foundMatch;
                        $details = $passed ? "JSON-LD schema/key '{$rule->expected_value}' found." : "JSON-LD schema/key '{$rule->expected_value}' not found.";
                        $htmlSnippet = $firstSnippet;
                    } else {
                        $details = 'No application/ld+json script found.';
                    }
                    break;
                default:
                    $details = "Unknown rule type: {$rule->rule_type}";
                    break;
            }
        } catch (\Exception $e) {
            $details = 'Evaluation error: '.$e->getMessage();
        }

        return [
            'rule_name' => $rule->name,
            'rule_type' => $rule->rule_type,
            'severity' => $rule->severity,
            'passed' => $passed,
            'details' => $details,
            'html_snippet' => $htmlSnippet,
        ];
    }

    private function arrayContainsKeyValue(array $array, $searchKey, $searchValue)
    {
        foreach ($array as $key => $value) {
            if ($key === $searchKey && $value === $searchValue) {
                return true;
            }
            if (is_array($value) && $this->arrayContainsKeyValue($value, $searchKey, $searchValue)) {
                return true;
            }
        }

        return false;
    }

    private function arrayContainsValue(array $array, $searchValue)
    {
        foreach ($array as $value) {
            if ($value === $searchValue) {
                return true;
            }
            if (is_array($value) && $this->arrayContainsValue($value, $searchValue)) {
                return true;
            }
        }

        return false;
    }
}
