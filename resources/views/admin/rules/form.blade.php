<x-admin-layout>
    <x-slot name="title">{{ $rule->exists ? 'Edit Rule' : 'Create Rule' }}</x-slot>
    <x-slot name="header">{{ $rule->exists ? 'Edit SEO Rule' : 'Create New SEO Rule' }}</x-slot>

    <div class="max-w-3xl mx-auto" x-data="{ ruleType: '{{ old('rule_type', $rule->rule_type ?? 'exist') }}' }">
        
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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <form action="{{ $rule->exists ? route('admin.rules.update', $rule) : route('admin.rules.store') }}" method="POST" class="space-y-6">
                @csrf
                @if($rule->exists)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rule Name *</label>
                    <input type="text" name="name" value="{{ old('name', $rule->name) }}" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. Check H1 Exist">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rule Type *</label>
                    <select name="rule_type" x-model="ruleType" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                        @foreach(\App\Enums\RuleType::cases() as $type)
                            <option value="{{ $type->value }}" {{ old('rule_type', $rule->rule_type?->value ?? 'exist') === $type->value ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type->value)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="ruleType !== 'json_ld' && ruleType !== 'schema'" x-transition>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Target Selector</label>
                    <input type="text" name="target_selector" value="{{ old('target_selector', $rule->target_selector) }}" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. h1 or meta[name='description']">
                    <p class="text-xs text-gray-500 mt-2">Leave empty if checking JSON-LD.</p>
                </div>

                <div x-show="ruleType !== 'json_ld' && ruleType !== 'schema'" x-transition>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Attribute (Optional)</label>
                    <input type="text" name="attribute" value="{{ old('attribute', $rule->attribute) }}" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. content or href">
                    <p class="text-xs text-gray-500 mt-2">If filled, the rule will extract this attribute's value instead of the element's text.</p>
                </div>

                <div x-show="['count', 'length', 'length_max', 'text_match', 'compare_amp'].includes(ruleType)" x-transition>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Operator</label>
                    <select name="operator" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                        <option value="=" {{ old('operator', $rule->operator) === '=' ? 'selected' : '' }}>Equals (=)</option>
                        <option value="!=" {{ old('operator', $rule->operator) === '!=' ? 'selected' : '' }}>Not Equals (!=)</option>
                        <option value="contains" {{ old('operator', $rule->operator) === 'contains' ? 'selected' : '' }}>Contains</option>
                        <option value=">" {{ old('operator', $rule->operator) === '>' ? 'selected' : '' }}>Greater Than (>)</option>
                        <option value="<" {{ old('operator', $rule->operator) === '<' ? 'selected' : '' }}>Less Than (<)</option>
                        <option value="between" {{ old('operator', $rule->operator) === 'between' ? 'selected' : '' }}>Between</option>
                    </select>
                </div>

                <div x-show="['count', 'length', 'length_max', 'text_match', 'json_ld', 'schema'].includes(ruleType)" x-transition>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Expected Value</label>
                    <input type="text" name="expected_value" value="{{ old('expected_value', $rule->expected_value) }}" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. 1, 60, or @type:Article">
                    <p class="text-xs text-gray-500 mt-2" x-show="ruleType === 'json_ld'">For JSON-LD, you can use exact search or Key:Value (e.g. <code>@type:Article</code>)</p>
                </div>

                <div class="grid grid-cols-2 gap-4" x-show="['count', 'length', 'length_max'].includes(ruleType)" x-transition>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Min Value</label>
                        <input type="number" name="min_value" value="{{ old('min_value', $rule->min_value) }}" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Max Value</label>
                        <input type="number" name="max_value" value="{{ old('max_value', $rule->max_value) }}" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    </div>
                </div>

                <div x-show="ruleType === 'regex'" x-transition>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Regex Pattern</label>
                    <input type="text" name="regex_pattern" value="{{ old('regex_pattern', $rule->regex_pattern) }}" class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. /^[a-zA-Z0-9]+$/">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Severity *</label>
                    <select name="severity" required class="w-full border border-gray-200 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                        <option value="warning" {{ old('severity', $rule->severity) === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="error" {{ old('severity', $rule->severity) === 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>

                <div class="flex items-center pt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }} class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-3 block text-sm font-medium text-gray-900">Rule is Active</label>
                </div>

                <div class="pt-6 border-t border-gray-100">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition-transform hover:-translate-y-0.5">
                        {{ $rule->exists ? 'Update Rule' : 'Save New Rule' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
