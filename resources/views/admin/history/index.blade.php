<x-admin-layout>
    <x-slot name="title">Riwayat Pengecekan SEO</x-slot>

    <div class="mb-8 relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Riwayat Pengecekan SEO</h1>
            <p class="text-slate-500 mt-2">Lihat histori audit SEO yang pernah Anda jalankan sebelumnya.</p>
        </div>
        <div>
            <button type="submit" form="batch-delete-form" id="btn-batch-delete" disabled class="disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 font-bold py-2 px-4 rounded-xl transition-colors shadow-sm border border-red-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Hapus Terpilih (<span id="selected-count">0</span>)
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl border border-emerald-100 flex items-center gap-3 mb-6 relative z-10">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white/70 backdrop-blur-2xl rounded-3xl border border-white/50 shadow-xl overflow-hidden p-6 relative">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

        <form id="batch-delete-form" action="{{ route('admin.history.batchDestroy') }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="overflow-x-auto relative z-10">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200/60 text-xs uppercase tracking-wider text-slate-400 font-semibold">
                            <th class="pb-4 pl-4 w-10">
                                <input type="checkbox" id="select-all" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 bg-white">
                            </th>
                            <th class="pb-4 pl-2">Tanggal Audit</th>
                            <th class="pb-4">Total URL</th>
                            <th class="pb-4 text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($runs as $run)
                        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 pl-4">
                                <input type="checkbox" name="run_ids[]" value="{{ $run->getRouteKey() }}" class="run-checkbox rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 bg-white">
                            </td>
                            <td class="py-4 pl-2 font-medium text-slate-700">
                                {{ $run->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="py-4 text-slate-600">
                                {{ $run->total_urls }}
                            </td>
                            <td class="py-4 text-right pr-4 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.history.show', $run) }}" class="inline-flex items-center gap-1.5 text-indigo-600 font-medium hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Detail
                                    </a>
                                    <button type="submit" formaction="{{ route('admin.history.destroy', $run) }}" class="inline-flex items-center gap-1.5 text-red-600 font-medium hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors" onclick="return confirm('Apakah Anda yakin ingin menghapus riwayat ini?');">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-500">
                                Belum ada riwayat pengecekan SEO.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        <div class="mt-6 relative z-10">
            {{ $runs->links() }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.run-checkbox');
            const btnBatchDelete = document.getElementById('btn-batch-delete');
            const selectedCount = document.getElementById('selected-count');
            const batchForm = document.getElementById('batch-delete-form');

            function updateBatchDeleteButton() {
                const checked = document.querySelectorAll('.run-checkbox:checked').length;
                if(selectedCount) selectedCount.textContent = checked;
                if(btnBatchDelete) btnBatchDelete.disabled = checked === 0;
            }

            if(selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBatchDeleteButton();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (!this.checked && selectAll) selectAll.checked = false;
                    if (document.querySelectorAll('.run-checkbox:checked').length === checkboxes.length && selectAll) selectAll.checked = true;
                    updateBatchDeleteButton();
                });
            });

            if (batchForm) {
                batchForm.addEventListener('submit', function(e) {
                    // Check if it's the batch delete button being clicked
                    // (individual delete uses formaction which bypasses this or triggers separately)
                    if (e.submitter && e.submitter.id === 'btn-batch-delete') {
                        if (!confirm('Apakah Anda yakin ingin menghapus ' + document.querySelectorAll('.run-checkbox:checked').length + ' riwayat terpilih?')) {
                            e.preventDefault();
                        }
                    }
                });
            }
        });
    </script>
</x-admin-layout>
