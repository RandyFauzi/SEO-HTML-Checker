<x-admin-layout>
    <x-slot name="title">{{ $rule->exists ? 'Edit Rule' : 'Create Rule' }}</x-slot>
    <x-slot name="header">{{ $rule->exists ? 'Edit SEO Rule' : 'Create New SEO Rule' }}</x-slot>

    <div class="max-w-7xl mx-auto" x-data="ruleForm()">
        
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ $rule->exists ? 'Edit Rule' : 'Create New Rule' }}</h2>
            <a href="{{ route('admin.rules.index') }}" class="text-sm text-gray-500 hover:text-gray-800 font-medium flex items-center transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to List
            </a>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Form Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <form action="{{ $rule->exists ? route('admin.rules.update', $rule) : route('admin.rules.store') }}" method="POST" class="space-y-6" @submit="prepareSubmit">
                    @csrf
                    @if($rule->exists)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Code *</label>
                            <input type="text" name="code" x-model="formData.code" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. TITLE_EXISTS">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rule Name *</label>
                            <input type="text" name="name" x-model="formData.name" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. Check Title Exists">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                            <input type="text" name="category" x-model="formData.category" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. Metadata">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rule Type *</label>
                            <select name="rule_type" x-model="formData.rule_type" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                @foreach(\App\Enums\RuleType::cases() as $type)
                                    <option value="{{ $type->value }}">
                                        {{ ucfirst(str_replace('_', ' ', $type->value)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Severity *</label>
                            <select name="severity" x-model="formData.severity" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mode Toggle -->
                    <div class="flex items-center justify-start mb-2 bg-slate-200/50 p-1 rounded-lg w-fit">
                        <button type="button" @click="setMode('basic')" :class="mode === 'basic' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all">Visual Builder</button>
                        <button type="button" @click="setMode('advanced')" :class="mode === 'advanced' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all">Advanced JSON</button>
                    </div>

                    <!-- BASIC MODE (Visual Builder) -->
                    <div x-show="mode === 'basic'" x-cloak class="space-y-5 bg-blue-50/50 p-5 rounded-xl border border-blue-100 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">1. Target Element</label>
                            <select x-model="basic.target" @change="syncBasicToJson()" class="w-full border border-blue-200 rounded-lg p-2.5 focus:ring-blue-500 bg-white text-sm">
                                <option value="title">Page Title (&lt;title&gt;)</option>
                                <option value="meta_description">Meta Description</option>
                                <option value="h1">Main Heading (H1)</option>
                                <option value="h2">Sub Heading (H2)</option>
                                <option value="canonical">Canonical Link</option>
                                <option value="custom">Custom CSS Selector...</option>
                            </select>
                        </div>

                        <div x-show="basic.target === 'custom'" x-cloak>
                            <label class="block text-xs font-bold text-blue-900 mb-1">Custom CSS Selector</label>
                            <input type="text" x-model="basic.customSelector" @input.debounce="syncBasicToJson()" class="w-full border border-blue-200 rounded-lg p-2 focus:ring-blue-500 bg-white text-sm" placeholder="e.g. meta[property='og:title']">
                            
                            <label class="block text-xs font-bold text-blue-900 mt-3 mb-1">Attribute to extract (Optional)</label>
                            <input type="text" x-model="basic.attribute" @input.debounce="syncBasicToJson()" class="w-full border border-blue-200 rounded-lg p-2 focus:ring-blue-500 bg-white text-sm" placeholder="e.g. content, href, src">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">2. Rule Condition</label>
                            <select x-model="basic.condition" @change="syncBasicToJson()" class="w-full border border-blue-200 rounded-lg p-2.5 focus:ring-blue-500 bg-white text-sm">
                                <option value="exist">Must Exist in page</option>
                                <option value="count">Count must be exactly / between</option>
                                <option value="length">Text length must be between</option>
                                <option value="text_match">Text must exactly match</option>
                            </select>
                        </div>

                        <!-- Dynamic Parameters based on Condition -->
                        <div x-show="basic.condition === 'length' || basic.condition === 'count'" x-cloak class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-blue-900 mb-1">Min Value</label>
                                <input type="number" x-model="basic.min" @input.debounce="syncBasicToJson()" class="w-full border border-blue-200 rounded-lg p-2 focus:ring-blue-500 bg-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-blue-900 mb-1">Max Value</label>
                                <input type="number" x-model="basic.max" @input.debounce="syncBasicToJson()" class="w-full border border-blue-200 rounded-lg p-2 focus:ring-blue-500 bg-white text-sm">
                            </div>
                        </div>

                        <div x-show="basic.condition === 'text_match'" x-cloak>
                            <label class="block text-xs font-bold text-blue-900 mb-1">Expected Text</label>
                            <input type="text" x-model="basic.expectedText" @input.debounce="syncBasicToJson()" class="w-full border border-blue-200 rounded-lg p-2 focus:ring-blue-500 bg-white text-sm">
                        </div>
                        
                        <p class="text-xs text-blue-600 flex items-center mt-2">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Rule Type and JSON are automatically generated.
                        </p>
                    </div>

                    <!-- ADVANCED MODE (JSON) -->
                    <div x-show="mode === 'advanced'" x-cloak>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">JSON Configuration *</label>
                        <textarea x-model="configJson" rows="6" class="w-full border border-gray-200 rounded-xl p-3 font-mono text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder='{"selector": "h1"}'></textarea>
                        <input type="hidden" name="config" :value="configJson">
                        <p class="text-xs text-red-500 mt-1" x-show="configError" x-text="configError"></p>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Issue Message (What)</label>
                            <input type="text" name="issue_message" x-model="formData.issue_message" class="w-full border border-gray-200 rounded-lg p-2 focus:ring-blue-500 bg-gray-50" placeholder="e.g. Title tag is missing.">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Reason Template (Why)</label>
                            <input type="text" name="reason_template" x-model="formData.reason_template" class="w-full border border-gray-200 rounded-lg p-2 focus:ring-blue-500 bg-gray-50" placeholder="e.g. Title is important for SEO...">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Recommendation (How to fix)</label>
                            <input type="text" name="recommendation" x-model="formData.recommendation" class="w-full border border-gray-200 rounded-lg p-2 focus:ring-blue-500 bg-gray-50" placeholder="e.g. Add a <title> tag inside <head>.">
                        </div>
                    </div>

                    <div class="flex items-center pt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" x-model="formData.is_active" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-3 block text-sm font-medium text-gray-900">Rule is Active</label>
                    </div>

                    <div class="pt-6 border-t border-gray-100 flex gap-4">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition-transform hover:-translate-y-0.5">
                            {{ $rule->exists ? 'Update Rule' : 'Save New Rule' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Test Preview Section -->
            <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 p-6 md:p-8 text-white flex flex-col h-full">
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Test Rule Preview
                </h3>
                <p class="text-sm text-slate-400 mb-4">Validate your rule logic before saving. Enter a sample HTML block below.</p>
                
                <div class="flex-1 flex flex-col min-h-[300px]">
                    <textarea x-model="testHtml" class="flex-1 w-full bg-slate-800 border border-slate-700 rounded-xl p-4 font-mono text-sm text-blue-200 focus:ring-blue-500 focus:border-blue-500 mb-4 resize-y" placeholder="<html>&#10;  <head>&#10;    <title>Hello World</title>&#10;  </head>&#10;</html>"></textarea>
                    
                    <button type="button" @click="runTest" :disabled="isTesting" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition-colors flex justify-center items-center gap-2">
                        <span x-show="!isTesting">Run Test</span>
                        <span x-show="isTesting" class="animate-pulse">Testing...</span>
                    </button>
                </div>

                <!-- Test Results -->
                <div x-show="testResult !== null" class="mt-6 p-5 rounded-xl border" :class="testResult && testResult.passed ? 'bg-emerald-900/30 border-emerald-800' : 'bg-red-900/30 border-red-800'" x-cloak>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold" :class="testResult && testResult.passed ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'">
                            <svg x-show="testResult && testResult.passed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg x-show="testResult && !testResult.passed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        <h4 class="font-bold text-lg" :class="testResult && testResult.passed ? 'text-emerald-400' : 'text-red-400'" x-text="testResult && testResult.passed ? 'PASS' : 'FAIL'"></h4>
                    </div>

                    <div class="space-y-3 text-sm text-slate-300">
                        <template x-if="testResult && !testResult.passed && testResult.issue">
                            <div><span class="font-bold text-white">Issue:</span> <span x-text="testResult.issue"></span></div>
                        </template>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-if="testResult && testResult.expected">
                                <div><span class="font-bold text-white block">Expected:</span> <span x-text="testResult.expected"></span></div>
                            </template>
                            <template x-if="testResult && testResult.actual">
                                <div><span class="font-bold text-white block">Actual:</span> <span x-text="testResult.actual"></span></div>
                            </template>
                        </div>
                        <template x-if="testResult && testResult.snippet">
                            <div class="mt-2 pt-2 border-t border-slate-700/50">
                                <span class="font-bold text-white block mb-1">Snippet:</span> 
                                <pre class="bg-slate-950 p-3 rounded-lg overflow-x-auto text-xs text-slate-400"><code x-text="testResult.snippet"></code></pre>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function ruleForm() {
            return {
                mode: '{{ $rule->exists ? 'advanced' : 'basic' }}',
                basic: {
                    target: 'title',
                    customSelector: '',
                    attribute: '',
                    condition: 'exist',
                    min: '',
                    max: '',
                    expectedText: ''
                },
                formData: {
                    code: '{{ old('code', $rule->code) }}',
                    name: '{{ old('name', $rule->name) }}',
                    category: '{{ old('category', $rule->category ?? 'General') }}',
                    rule_type: '{{ old('rule_type', $rule->rule_type?->value ?? 'exist') }}',
                    severity: '{{ old('severity', $rule->severity ?? 'warning') }}',
                    issue_message: '{{ old('issue_message', $rule->issue_message) }}',
                    reason_template: '{{ old('reason_template', $rule->reason_template) }}',
                    recommendation: '{{ old('recommendation', $rule->recommendation) }}',
                    is_active: {{ old('is_active', $rule->is_active ?? true) ? 'true' : 'false' }},
                },
                configJson: `{!! addslashes(old('config', $rule->exists ? json_encode($rule->config, JSON_PRETTY_PRINT) : "{\n  \"selector\": \"\"\n}")) !!}`,
                configError: '',
                testHtml: '',
                isTesting: false,
                testResult: null,

                init() {
                    if (this.mode === 'basic') {
                        this.syncBasicToJson();
                    }
                },

                setMode(m) {
                    this.mode = m;
                    if (m === 'basic') {
                        if(confirm("Switching to Basic Mode will overwrite your current JSON configuration. Continue?")) {
                            this.syncBasicToJson();
                        } else {
                            this.mode = 'advanced';
                        }
                    }
                },

                syncBasicToJson() {
                    let config = {};
                    
                    // Handle Target Selector
                    if (this.basic.target === 'title') {
                        config.selector = 'title';
                    } else if (this.basic.target === 'meta_description') {
                        config.selector = 'meta[name="description"]';
                        config.attribute = 'content';
                    } else if (this.basic.target === 'h1') {
                        config.selector = 'h1';
                    } else if (this.basic.target === 'h2') {
                        config.selector = 'h2';
                    } else if (this.basic.target === 'canonical') {
                        config.selector = 'link[rel="canonical"]';
                        config.attribute = 'href';
                    } else {
                        config.selector = this.basic.customSelector;
                        if (this.basic.attribute) {
                            config.attribute = this.basic.attribute;
                        }
                    }

                    // Handle Condition (Updates Rule Type as well)
                    this.formData.rule_type = this.basic.condition;

                    if (this.basic.condition === 'length' || this.basic.condition === 'count') {
                        if (this.basic.min !== '') config.min = parseInt(this.basic.min);
                        if (this.basic.max !== '') config.max = parseInt(this.basic.max);
                    } else if (this.basic.condition === 'text_match') {
                        config.expected_text = this.basic.expectedText;
                        config.exact_match = true;
                    }

                    this.configJson = JSON.stringify(config, null, 2);
                },

                prepareSubmit(e) {
                    try {
                        JSON.parse(this.configJson);
                        this.configError = '';
                    } catch (err) {
                        e.preventDefault();
                        this.configError = 'Invalid JSON in Configuration: ' + err.message;
                    }
                },

                async runTest() {
                    try {
                        JSON.parse(this.configJson);
                        this.configError = '';
                    } catch (err) {
                        this.configError = 'Invalid JSON in Configuration: ' + err.message;
                        return;
                    }

                    if (!this.testHtml.trim()) {
                        alert('Please enter some test HTML');
                        return;
                    }

                    this.isTesting = true;
                    this.testResult = null;

                    try {
                        const response = await fetch('{{ route('admin.rules.test') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                html: this.testHtml,
                                rule: {
                                    name: this.formData.name,
                                    rule_type: this.formData.rule_type,
                                    config: this.configJson
                                }
                            })
                        });

                        const data = await response.json();
                        this.testResult = data;
                    } catch (error) {
                        alert('Error running test: ' + error.message);
                    } finally {
                        this.isTesting = false;
                    }
                }
            }
        }
    </script>
</x-admin-layout>
