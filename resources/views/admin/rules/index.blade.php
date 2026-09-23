<x-admin-layout>
    <x-slot name="title">SEO Rules Management</x-slot>
    <x-slot name="header">Rules Management</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">SEO Check Rules</h2>
                <p class="text-sm text-gray-500 mt-1">Manage all the rules used to check landing pages.</p>
            </div>
            <div class="flex w-full md:w-auto">
                <a href="{{ route('admin.rules.create') }}" class="w-full md:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 md:py-2 px-4 rounded-xl shadow-sm transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add New Rule
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative mb-6 shadow-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative mb-6 shadow-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Code</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Severity</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse($rules as $rule)
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $rule->code ?: '#'.$rule->id }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $rule->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-medium border border-gray-200">{{ $rule->rule_type->value ?? $rule->rule_type }}</span>
                                </td>
                                <td class="px-6 py-4"><span class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-xs border border-indigo-100">{{ $rule->category }}</span></td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-semibold border {{ $rule->severity === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-yellow-50 text-yellow-700 border-yellow-200' }}">
                                        {{ ucfirst($rule->severity) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div x-data="{ 
                                            isActive: {{ $rule->is_active ? 'true' : 'false' }}, 
                                            loading: false,
                                            toggleStatus() {
                                                this.loading = true;
                                                fetch('{{ route('admin.rules.toggle', $rule) }}', {
                                                    method: 'PATCH',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json'
                                                    }
                                                })
                                                .then(res => res.json())
                                                .then(data => {
                                                    this.isActive = data.is_active;
                                                    this.loading = false;
                                                })
                                                .catch(() => this.loading = false);
                                            }
                                        }">
                                        <button @click="toggleStatus" 
                                                :disabled="loading"
                                                :class="isActive ? 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'"
                                                class="px-3 py-1 rounded-full text-xs font-bold transition-colors w-24 text-center shadow-sm border">
                                            <span x-show="!loading" x-text="isActive ? 'Active' : 'Inactive'"></span>
                                            <span x-show="loading" class="animate-pulse">Wait...</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    <a href="{{ route('admin.rules.edit', $rule) }}" class="text-blue-600 hover:text-blue-900 font-medium transition-colors">Edit</a>
                                    <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium transition-colors">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                                    <p class="text-base font-medium text-gray-900">No rules found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-100">
                @forelse($rules as $rule)
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-xs font-mono text-gray-500 mb-1">{{ $rule->code ?: '#'.$rule->id }}</div>
                                <div class="font-bold text-gray-900 text-base leading-tight">{{ $rule->name }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $rule->severity === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-yellow-50 text-yellow-700 border-yellow-200' }}">
                                {{ $rule->severity }}
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200">{{ $rule->rule_type->value ?? $rule->rule_type }}</span>
                            <span class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100">{{ $rule->category }}</span>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-gray-50 mt-2">
                            <div x-data="{ 
                                    isActive: {{ $rule->is_active ? 'true' : 'false' }}, 
                                    loading: false,
                                    toggleStatus() {
                                        this.loading = true;
                                        fetch('{{ route('admin.rules.toggle', $rule) }}', {
                                            method: 'PATCH',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        }).then(res => res.json()).then(data => {
                                            this.isActive = data.is_active;
                                            this.loading = false;
                                        }).catch(() => this.loading = false);
                                    }
                                }">
                                <button @click="toggleStatus" 
                                        :disabled="loading"
                                        :class="isActive ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-200'"
                                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-colors w-20 text-center shadow-sm border">
                                    <span x-show="!loading" x-text="isActive ? 'Active' : 'Inactive'"></span>
                                    <span x-show="loading" class="animate-pulse">...</span>
                                </button>
                            </div>
                            
                            <div class="flex space-x-3 text-sm">
                                <a href="{{ route('admin.rules.edit', $rule) }}" class="text-blue-600 font-medium">Edit</a>
                                <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" onsubmit="return confirm('Delete rule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 font-medium">Del</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">No rules found</div>
                @endforelse
            </div>
        </div>

        <!-- Import Section as a nice card -->
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 max-w-xl">
            <div class="flex items-start">
                <div class="flex-shrink-0 bg-blue-50 p-3 rounded-xl text-blue-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">Import JSON Rules</h3>
                    <p class="text-sm text-gray-500 mb-4 mt-1">Upload a JSON file containing SEO rules to bulk import them.</p>
                    
                    <form action="{{ route('admin.rules.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-400">JSON file only</p>
                                </div>
                                <input id="dropzone-file" type="file" name="json_file" accept=".json" required class="hidden" />
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm transition-colors">
                            Upload & Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
