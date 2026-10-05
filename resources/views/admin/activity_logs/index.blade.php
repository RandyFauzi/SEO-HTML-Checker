<x-admin-layout>
    <x-slot name="title">Log Aktivitas</x-slot>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">Log Aktivitas</h1>
        <p class="text-slate-500 mt-2">Seluruh catatan aktivitas yang terjadi di sistem.</p>
    </div>

    <div class="bg-white/70 backdrop-blur-2xl rounded-3xl border border-white/50 shadow-xl overflow-hidden p-6 relative">
        <div class="overflow-x-auto relative z-10">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/60 text-xs uppercase tracking-wider text-slate-400 font-semibold">
                        <th class="pb-4 pl-4">Waktu</th>
                        <th class="pb-4">Pengguna</th>
                        <th class="pb-4">Aksi</th>
                        <th class="pb-4 pr-4">Detail</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($logs as $log)
                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 pl-4 font-medium text-slate-700 whitespace-nowrap">
                            {{ $log->created_at->format('d M Y, H:i:s') }}
                        </td>
                        <td class="py-4 text-slate-600">
                            {{ $log->user ? $log->user->name : 'Sistem / Guest' }}
                        </td>
                        <td class="py-4 text-indigo-600 font-medium">
                            {{ $log->action }}
                        </td>
                        <td class="py-4 text-slate-500 pr-4">
                            {{ $log->description ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-500">
                            Belum ada aktivitas tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 relative z-10">
            {{ $logs->links() }}
        </div>
    </div>
</x-admin-layout>
