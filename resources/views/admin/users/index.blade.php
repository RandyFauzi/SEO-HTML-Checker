<x-admin-layout>
    <x-slot name="title">User Management</x-slot>
    <x-slot name="header">Kelola Admin</x-slot>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Akun Admin</h2>
            <p class="text-sm text-slate-500 mt-1 font-medium">Kelola hak akses pengguna.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl shadow-md transition-colors">
            + Tambah Admin
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="glass-panel rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-{{ $user->role === 'super_admin' ? 'purple' : 'slate' }}-100 text-{{ $user->role === 'super_admin' ? 'purple' : 'slate' }}-700 rounded text-xs font-bold uppercase">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-500 hover:text-indigo-700 font-medium text-xs bg-indigo-50 px-3 py-1.5 rounded-lg">Edit</a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs bg-red-50 px-3 py-1.5 rounded-lg">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
