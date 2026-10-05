<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTemplate;
use Illuminate\Http\Request;

class AiTemplateController extends Controller
{
    public function index()
    {
        $templates = AiTemplate::latest()->get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:LP,AMP',
            'url_or_html' => 'required|url', // Currently supporting URL fetching
        ]);

        if (AiTemplate::count() >= 10) {
            return response()->json(['error' => 'Maksimal 10 template tercapai.'], 400);
        }

        $template = AiTemplate::create($request->only('name', 'type', 'url_or_html'));

        return response()->json($template);
    }

    public function destroy(AiTemplate $template)
    {
        $template->delete();
        return response()->json(['success' => true]);
    }
}
