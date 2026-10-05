<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

// 1. Fix the main layout and grid so it takes full width
// Remove items-center from main, and add w-full max-w-7xl/max-w-[1600px] to the grid container
$content = preg_replace(
    '/<main class="relative z-0 flex flex-col items-center min-h-screen px-4 pt-24 pb-10">/',
    '<main class="relative z-0 w-full min-h-screen px-4 sm:px-6 lg:px-8 pt-24 pb-10">',
    $content
);

// 2. Change grid classes from xl to lg so it splits nicely on standard laptops
$content = preg_replace('/xl:grid-cols-12/', 'lg:grid-cols-12', $content);
$content = preg_replace('/xl:col-span-4/', 'lg:col-span-4', $content);
$content = preg_replace('/xl:col-span-8/', 'lg:col-span-8', $content);

// 3. Make the x-data container full width
$content = preg_replace(
    '/<div x-data="aiEditor\(\)" class="grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10 h-full">/',
    '<div x-data="aiEditor()" class="w-full max-w-[1600px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 relative z-10 h-full">',
    $content
);

// 4. Update the header text container so it spans full width but centers text
$content = preg_replace(
    '/<div class="text-center max-w-2xl mx-auto mb-8 relative z-10">/',
    '<div class="w-full text-center max-w-3xl mx-auto mb-8 lg:mb-12 relative z-10">',
    $content
);

// Write changes
file_put_contents($path, $content);
echo "Layout fixed.";
