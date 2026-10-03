<?php

$f = 'resources/views/admin/rules/index.blade.php';
$c = file_get_contents($f);
$c = preg_replace('/<\s*\/\s*x\s*-\s*a\s*d\s*m\s*i\s*n\s*-\s*l\s*a\s*y\s*o\s*u\s*t\s*>/i', '', $c);
$c = preg_replace('/[^\x20-\x7E\t\r\n]/', '', $c); // Strip all non-ascii characters just in case
file_put_contents($f, rtrim($c)."\n");
echo 'Cleaned';
