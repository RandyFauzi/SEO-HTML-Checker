<?php

$files = [
    'app/Services/AI/HtmlRemediationService.php',
    'app/Services/AI/RuleGeneratorService.php',
    'app/Services/AI/AiDomEditorService.php'
];

foreach ($files as $path) {
    if (file_exists($path)) {
        $content = file_get_contents($path);

        // Replace AiDomEditorService model
        $content = str_replace(
            "\$model = env('OPENAI_REMEDIATION_MODEL', 'gpt-5');",
            "\$model = env('OPENAI_REMEDIATION_MODEL', 'gpt-4o-mini');",
            $content
        );

        // Replace HtmlRemediationService logic
        $oldRemediation = "\$provider = config('services.ai_provider', 'openai');\n        \$model = \$provider === 'gemini' \n            ? env('GEMINI_REMEDIATION_MODEL', 'gemini-flash-latest')\n            : env('OPENAI_REMEDIATION_MODEL', 'gpt-6-astra');";
        $newRemediation = "\$model = env('OPENAI_REMEDIATION_MODEL', 'gpt-4o-mini');";
        $content = str_replace($oldRemediation, $newRemediation, $content);

        // Replace RuleGeneratorService logic
        $oldRule = "\$provider = config('services.ai_provider', 'openai');\n        \$model = \$provider === 'gemini' \n            ? env('GEMINI_RULE_BUILDER_MODEL', 'gemini-flash-latest')\n            : env('OPENAI_RULE_BUILDER_MODEL', 'gpt-6-astra');";
        $newRule = "\$model = env('OPENAI_RULE_BUILDER_MODEL', 'gpt-4o-mini');";
        $content = str_replace($oldRule, $newRule, $content);

        // Fallbacks if formatting is slightly different
        $content = preg_replace("/\\\$provider = config\('services\.ai_provider', 'openai'\);\s+\\\$model = \\\$provider === 'gemini'\s+\?\s+env\('GEMINI_[A-Z_]+', '[^']+'\)\s+:\s+env\('OPENAI_[A-Z_]+', '[^']+'\);/s", "\$model = env('OPENAI_MODEL', 'gpt-4o-mini');", $content);

        file_put_contents($path, $content);
    }
}
echo "Models updated to gpt-4o-mini";
