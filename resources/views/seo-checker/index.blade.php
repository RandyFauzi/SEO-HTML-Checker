<x-admin-layout>
    <x-slot name="title">SEO Checker</x-slot>
    <x-slot name="header">SEO HTML Checker</x-slot>

    <div class="max-w-7xl mx-auto" x-data="{ hasCompareRule: {{ $hasCompareRule ? 'true' : 'false' }} }">
        
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Run SEO Audit</h2>
            <p class="text-sm text-gray-500 mt-1">Check multiple URLs against your active SEO rules.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative mb-6 shadow-sm text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
            <form action="{{ route('seo.process') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1" :class="{ 'md:grid-cols-2 gap-6': hasCompareRule }">
                    <div>
                        <label for="lp_urls" class="block text-sm font-semibold text-gray-700 mb-2">
                            Landing Page URLs (Max 10)
                        </label>
                        <textarea name="lp_urls" id="lp_urls" rows="6" placeholder="https://example.com/page-1&#10;https://example.com/page-2" class="w-full border border-gray-200 rounded-xl shadow-sm p-4 text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors" required>{{ old('lp_urls') }}</textarea>
                    </div>

                    <div x-show="hasCompareRule" x-cloak>
                        <label for="amp_urls" class="block text-sm font-semibold text-gray-700 mb-2">
                            AMP URLs (Matches LP order)
                        </label>
                        <textarea name="amp_urls" id="amp_urls" rows="6" placeholder="https://amp.example.com/page-1&#10;https://amp.example.com/page-2" class="w-full border border-gray-200 rounded-xl shadow-sm p-4 text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors" :required="hasCompareRule">{{ old('amp_urls') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-sm transition-transform hover:-translate-y-0.5 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Run Checker
                    </button>
                </div>
            </form>
        </div>

        @if(isset($results))
            <div class="space-y-6">
                <h3 class="text-xl font-bold text-gray-900 border-b border-gray-200 pb-3">Audit Results</h3>
                
                @foreach($results as $result)
                    <div class="bg-white border rounded-2xl overflow-hidden shadow-sm {{ $result->errorMessage ? 'border-red-200' : 'border-gray-200' }}">
                        
                        <div class="px-6 py-4 {{ $result->errorMessage ? 'bg-red-50/50' : 'bg-gray-50/50' }} border-b {{ $result->errorMessage ? 'border-red-100' : 'border-gray-100' }}">
                            @if($result->ampUrl)
                                <h4 class="font-bold text-gray-800 text-sm uppercase tracking-wide mb-2">Comparison Result</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                    <div class="flex items-start"><span class="font-semibold text-gray-500 w-12 shrink-0">LP:</span> <a href="{{ $result->lpUrl }}" class="text-blue-600 hover:underline break-all" target="_blank">{{ $result->lpUrl }}</a></div>
                                    <div class="flex items-start"><span class="font-semibold text-gray-500 w-12 shrink-0">AMP:</span> <a href="{{ $result->ampUrl }}" class="text-blue-600 hover:underline break-all" target="_blank">{{ $result->ampUrl }}</a></div>
                                </div>
                            @else
                                <h4 class="font-bold text-gray-800 text-sm uppercase tracking-wide mb-2">Single URL Result</h4>
                                <div class="flex items-start text-sm"><span class="font-semibold text-gray-500 w-12 shrink-0">URL:</span> <a href="{{ $result->lpUrl }}" class="text-blue-600 hover:underline break-all" target="_blank">{{ $result->lpUrl }}</a></div>
                            @endif

                            @if(!empty($result->redirectChain))
                                <div class="mt-3 text-xs text-gray-500 flex items-center flex-wrap gap-1">
                                    <span class="font-semibold">Redirects:</span>
                                    @foreach($result->redirectChain as $hop)
                                        <span class="bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ $hop }}</span>
                                        @if(!$loop->last) <span class="text-gray-400">→</span> @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="p-0">
                            @if($result->errorMessage)
                                <div class="p-6 text-red-600 flex items-center font-medium">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $result->errorMessage }}
                                </div>
                            @else
                                @if(empty($result->checks))
                                    <div class="p-6 text-gray-500 italic text-center">No rules were executed.</div>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm text-left">
                                            <thead class="bg-white text-gray-400 text-xs uppercase font-semibold border-b border-gray-100">
                                                <tr>
                                                    <th class="px-6 py-4">Rule Name</th>
                                                    <th class="px-6 py-4">Status</th>
                                                    <th class="px-6 py-4">Details</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                                @foreach($result->checks as $check)
                                                    <tr class="hover:bg-gray-50/50">
                                                        <td class="px-6 py-4 font-medium text-gray-900 w-1/4">
                                                            {{ $check->ruleName }}
                                                        </td>
                                                        <td class="px-6 py-4 w-32">
                                                            @if($check->status->value === 'passed')
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                                                    Passed
                                                                </span>
                                                            @elseif($check->status->value === 'warning')
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200">
                                                                    Warning
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                                                    Error
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 text-gray-600">
                                                            <div class="mb-1">{{ $check->details }}</div>
                                                            @if(!empty($check->htmlSnippet))
                                                                <div class="mt-3 text-xs bg-gray-900 text-green-400 p-3 rounded-lg overflow-x-auto font-mono shadow-inner border border-gray-800">
                                                                    <pre><code>{{ $check->htmlSnippet }}</code></pre>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>
