<?php

function scanAndFix($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, "\0") !== false) {
                echo "Found UTF-16: " . $file->getPathname() . "\n";
            }
        }
    }
}

scanAndFix('public/');
