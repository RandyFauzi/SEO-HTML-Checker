<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\HtmlFetcher;
use App\Services\AI\AiDomEditorService;
use Illuminate\Support\Facades\Log;

class AiHtmlEditorController extends Controller
{
    public function __construct(
        protected HtmlFetcher $htmlFetcher,
        protected AiDomEditorService $aiDomEditor
    ) {}

    public function index()
    {
        return view('admin.ai_editor.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'prompt' => 'required|string|max:5000',
        ]);

        $url = $request->input('url');
        $prompt = $request->input('prompt');

        // Fetch HTML
        $fetchResults = $this->htmlFetcher->fetchConcurrent([$url]);
        $fetchResult = $fetchResults[$url] ?? null;

        if (!$fetchResult) {
            return response()->json(['error' => 'Gagal mengambil URL.'], 400);
        }

        if (isset($fetchResult['error'])) {
            return response()->json(['error' => $fetchResult['error']], 400);
        }

        $originalHtml = $fetchResult['html'] ?? '';

        if (empty($originalHtml)) {
            return response()->json(['error' => 'HTML kosong dari URL tersebut.'], 400);
        }

        try {
            // Log activity
            \App\Helpers\Logger::log('AI HTML Editor', "Mengedit URL: {$url} dengan prompt: {$prompt}");
            
            $result = $this->aiDomEditor->editHtml($originalHtml, $prompt);
            
            return response()->json([
                'original_html' => $originalHtml,
                'modified_html' => $result['modified_html'],
                'operations' => $result['operations'],
            ]);
        } catch (\Exception $e) {
            Log::error('AI Editor Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memproses AI: ' . $e->getMessage()], 500);
        }
    }

    public function processAuto(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:ai_templates,id',
            'keyword' => 'required|string',
            'brand' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'amp_url' => 'nullable|url',
            'favicon_url' => 'nullable|url',
            'logo_url' => 'nullable|url',
            'banner_urls' => 'nullable|string',
        ]);

        $template = \App\Models\AiTemplate::findOrFail($request->input('template_id'));
        $url = $template->url_or_html;

        // Fetch HTML
        $fetchResults = $this->htmlFetcher->fetchConcurrent([$url]);
        $fetchResult = $fetchResults[$url] ?? null;

        if (!$fetchResult || isset($fetchResult['error']) || empty($fetchResult['html'])) {
            return response()->json(['error' => 'Gagal mengambil HTML dari URL template: ' . ($fetchResult['error'] ?? 'Unknown error')], 400);
        }

        $originalHtml = $fetchResult['html'];
        $type = $template->type; // LP or AMP
        
        $brand = $request->input('brand', 'ACONGSTORE');
        $keyword = $request->input('keyword');
        
        // Assemble the prompt based on client's specific brief
        $prompt = "Kamu adalah AI Mass Page Generator SEO.\n";
        $prompt .= "Tugasmu: Mengubah HTML ini menjadi halaman spesifik untuk keyword '{$keyword}'.\n";
        $prompt .= "Tipe Template: {$type}\n\n";
        $prompt .= "ATURAN WAJIB:\n";
        $prompt .= "- TITLE dan H1 harus mengandung '{$brand}' dan '{$keyword}'.\n";
        
        if ($type === 'LP') {
            $prompt .= "- Buat Meta Description yang relevan dan menarik.\n";
            $prompt .= "- Ganti atau tambahkan deskripsi panjang (300-500 kata) yang relevan dengan topik '{$keyword}'.\n";
            $prompt .= "- Di dalam deskripsi panjang, sisipkan 2 anchor link ke canonical URL: 1 dengan anchor '{$brand}', dan 1 dengan anchor variasi dari topik (maksimal 2 kata, misal 'top up', 'isi diamond').\n";
        } else { // AMP
            $prompt .= "- TITLE dan H1 harus BERBEDA dari versi LP, namun tetap membahas topik '{$keyword}' dan '{$brand}'.\n";
            $prompt .= "- JANGAN tambahkan deskripsi panjang di AMP.\n";
        }

        $canonical = $request->input('canonical_url');
        if ($canonical) {
            $prompt .= "- Pastikan tag <link rel=\"canonical\"> mengarah ke: {$canonical}\n";
        }
        
        $ampUrl = $request->input('amp_url');
        if ($ampUrl && $type === 'LP') {
            $prompt .= "- Pastikan tag <link rel=\"amphtml\"> mengarah ke: {$ampUrl}\n";
        }

        $favicon = $request->input('favicon_url');
        if ($favicon) {
            $prompt .= "- Ganti favicon ke: {$favicon}\n";
        }
        
        $logo = $request->input('logo_url');
        if ($logo) {
            $prompt .= "- Ganti logo website ke: {$logo}\n";
        }

        $banners = $request->input('banner_urls');
        if ($banners) {
            $prompt .= "- Ganti banner-banner dengan link berikut:\n{$banners}\n";
        }

        $prompt .= "- Sesuaikan SEMUA konten topik pendukung (Komentar testimoni, Anchor, FAQ, Schema, dll) dengan keyword '{$keyword}'.\n";
        $prompt .= "- Anchor text harus bervariasi dan maksimal 2 kata.\n";
        $prompt .= "- Ganti komentar HTML di dalam file agar sesuai.\n";

        try {
            \App\Helpers\Logger::log('AI HTML Editor', "Auto-Pilot Edit: Template {$template->name} ({$type}) - Keyword: {$keyword}");
            
            $result = $this->aiDomEditor->editHtml($originalHtml, $prompt);
            
            return response()->json([
                'original_html' => $originalHtml,
                'modified_html' => $result['modified_html'],
                'operations' => $result['operations'],
                'assembled_prompt' => $prompt
            ]);
        } catch (\Exception $e) {
            Log::error('AI Editor Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memproses AI: ' . $e->getMessage()], 500);
        }
    }

    public function downloadBatch(Request $request)
    {
        $request->validate([
            'batch_data' => 'required|string'
        ]);

        $data = json_decode($request->input('batch_data'), true);

        if (!is_array($data) || empty($data)) {
            return back()->with('error', 'Data batch tidak valid atau kosong.');
        }

        $zipFileName = 'seo-ai-batch-' . time() . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($data as $index => $item) {
                if (isset($item['url']) && isset($item['html'])) {
                    // Create a safe filename from the URL
                    $parsedUrl = parse_url($item['url']);
                    $host = $parsedUrl['host'] ?? 'unknown';
                    $path = isset($parsedUrl['path']) ? str_replace('/', '_', trim($parsedUrl['path'], '/')) : '';
                    if (empty($path)) {
                        $path = 'index';
                    }
                    
                    $safeName = $host . '_' . $path . '_' . ($index + 1) . '.html';
                    
                    $zip->addFromString($safeName, $item['html']);
                }
            }
            $zip->close();
        } else {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
