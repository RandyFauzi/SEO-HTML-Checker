<?php
$html = file_get_contents('https://seochecker.my.id');
$start = strpos($html, '<div class="max-w-6xl mx-auto"');
$end = strpos($html, '</form>', $start);
echo substr($html, $start, $end - $start);
