<?php
$f = 'resources/views/admin/rules/index.blade.php';
$c = file_get_contents($f);
$c = str_replace('< / x - a d m i n - l a y o u t >', '', $c);
// also remove any weird spaces at the end
$c = rtrim($c) . "\n";
file_put_contents($f, $c);
echo "Fixed";
