<x-admin-layout>
    <x-slot name="title">{{ $user->exists ? 'Edit Admin' : 'Tambah Admin' }}</x-slot>
    <x-slot name="header">{{ $user->exists ? 'Edit Admin' : 'Tambah Admin' }}</x-slot>

    <div class="max-w-2xl mx-auto glass-panel p-8 rounded-3xl shadow-sm">
        <form action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Role</label>
                <select name="role" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
                @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Password {{ $user->exists ? '(Kosongkan jika tidak ingin mengubah)' : '' }}</label>
                <input type="password" name="password" {{ $user->exists ? '' : 'required' }} minlength="8"
                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 flex gap-4">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition-colors">
                    Simpan Admin
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 px-6 rounded-xl transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
