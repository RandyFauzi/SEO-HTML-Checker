<?php
$mdContent = file_get_contents('C:\Users\Administrator\Pictures\cek\SEO_Rules_Bank.md');
$jsonPath = 'database/data/rules_import.json';
$jsonData = json_decode(file_get_contents($jsonPath), true);
$rules = &$jsonData['rules'];

$currentCode = null;
$whyImportant = null;
$howToFix = null;
$description = null;

$lines = explode("\n", $mdContent);
foreach ($lines as $line) {
    $line = trim($line);
    
    // Match header like ### HEAD-001 - Title
    if (preg_match('/^###\s+([A-Z0-9\-]+)[^\w]+(.+)$/u', $line, $matches)) {
        $currentCode = trim($matches[1]);
        $description = trim($matches[2]);
        $whyImportant = null;
        $howToFix = null;
        continue;
    }
    
    if (preg_match('/^\*\*Kenapa penting:\*\*\s*(.*)$/', $line, $matches)) {
        $whyImportant = $matches[1];
    }
    
    if (preg_match('/^\*\*Cara memperbaiki:\*\*\s*(.*)$/', $line, $matches)) {
        $howToFix = $matches[1];
    }
    
    // If we have all pieces for the current code, update the JSON
    if ($currentCode && $whyImportant !== null && $howToFix !== null) {
        foreach ($rules as &$rule) {
            if ($rule['code'] === $currentCode) {
                $rule['description'] = $description;
                $rule['reason_template'] = $whyImportant;
                $rule['recommendation'] = $howToFix;
                // Also issue message
                $rule['issue_message'] = "Masalah pada " . $rule['name'];
                break;
            }
        }
        $whyImportant = null;
        $howToFix = null;
    }
}

file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT));
echo "Done appending descriptions to JSON!\n";
