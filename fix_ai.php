<?php
$f = 'app/Services/AI/RuleGeneratorService.php';
$c = file_get_contents($f);
$c = str_replace('"rule_type": "TIPE_ATURAN",', '"rule_type": "TIPE_ATURAN",' . "\n" . '    "description": "Penjelasan detail mengenai fungsi dan tujuan rule ini",', $c);
file_put_contents($f, $c);
