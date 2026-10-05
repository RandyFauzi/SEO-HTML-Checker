<?php
$path = 'app/Http/Controllers/Admin/AiHtmlEditorController.php';
$content = file_get_contents($path);

// Add the downloadBatch method
$newMethod = '
    public function downloadBatch(Request $request)
    {
        $request->validate([
            \'batch_data\' => \'required|string\'
        ]);

        $data = json_decode($request->input(\'batch_data\'), true);

        if (!is_array($data) || empty($data)) {
            return back()->with(\'error\', \'Data batch tidak valid atau kosong.\');
        }

        $zipFileName = \'seo-ai-batch-\' . time() . \'.zip\';
        $zipFilePath = storage_path(\'app/public/\' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($data as $index => $item) {
                if (isset($item[\'url\']) && isset($item[\'html\'])) {
                    // Create a safe filename from the URL
                    $parsedUrl = parse_url($item[\'url\']);
                    $host = $parsedUrl[\'host\'] ?? \'unknown\';
                    $path = isset($parsedUrl[\'path\']) ? str_replace(\'/\', \'_\', trim($parsedUrl[\'path\'], \'/\')) : \'\';
                    if (empty($path)) {
                        $path = \'index\';
                    }
                    
                    $safeName = $host . \'_\' . $path . \'_\' . ($index + 1) . \'.html\';
                    
                    $zip->addFromString($safeName, $item[\'html\']);
                }
            }
            $zip->close();
        } else {
            return back()->with(\'error\', \'Gagal membuat file ZIP.\');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
';

$content = preg_replace('/\}\s*$/', $newMethod, $content);

file_put_contents($path, $content);
echo "Added downloadBatch to controller";
