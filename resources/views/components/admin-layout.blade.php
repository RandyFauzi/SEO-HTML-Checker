<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    <x-favicon />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; } /* bg-slate-100 base */
        .sidebar-item-active { background-color: rgba(255, 255, 255, 0.8); color: #4f46e5; font-weight: 600; border-radius: 1rem; margin: 0 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .sidebar-item { color: #64748b; font-weight: 500; transition: all 0.3s; border-radius: 1rem; margin: 0 0.75rem; }
        .sidebar-item:hover:not(.sidebar-item-active) { background-color: rgba(255, 255, 255, 0.5); color: #334155; transform: translateX(4px); }
        .glass-panel { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.8); }
        
        /* Global Subtle Blobs */
        .animate-blob { animation: blob 10s infinite alternate; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        
        /* Custom Scrollbar for a cleaner look */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="text-gray-800 antialiased flex h-screen overflow-hidden relative" x-data="{ showToast: false, toastMessage: '', sidebarOpen: false }" @notify.window="showToast = true; toastMessage = $event.detail; setTimeout(() => showToast = false, 3000)">
    
    <!-- Clean Subtle Global Background Blobs -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-96 h-96 bg-indigo-200/40 rounded-full mix-blend-multiply filter blur-3xl opacity-60 animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-[30rem] h-[30rem] bg-sky-200/40 rounded-full mix-blend-multiply filter blur-3xl opacity-60 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-[25rem] h-[25rem] bg-slate-200/50 rounded-full mix-blend-multiply filter blur-3xl opacity-60 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Toast Notification -->
    <div x-show="showToast" x-transition.opacity.duration.300ms class="fixed top-5 right-5 z-50 glass-panel shadow-[0_8px_30px_rgb(0,0,0,0.08)] px-5 py-4 rounded-2xl font-semibold flex items-center space-x-3" x-cloak>
        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <span x-text="toastMessage" class="text-slate-700"></span>
    </div>

    <!-- Sidebar (Glass) -->
    <aside class="w-64 glass-panel border-r border-white/60 flex-shrink-0 hidden md:flex flex-col z-20 m-4 rounded-[2rem] shadow-[0_8px_32px_rgba(0,0,0,0.02)]">
        <div class="h-20 flex items-center px-8 border-b border-white/50">
            <div class="flex items-center gap-3">
                <img src="{{ asset('Logo.webp') }}" alt="Logo" class="w-10 h-10 object-contain drop-shadow-sm">
                <span class="text-xl font-extrabold text-slate-800 tracking-tight">Checker</span>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto py-6">
            <div class="px-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Main Menu</div>
            <nav class="space-y-1.5">
                <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center px-4 py-3 {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('tutorial.index') }}" class="sidebar-item flex items-center px-4 py-3 {{ request()->routeIs('tutorial.*') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('tutorial.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Tutorial
                </a>
                <a href="{{ route('seo.index') }}" class="sidebar-item flex items-center px-4 py-3 {{ request()->routeIs('seo.*') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('seo.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Run Checker
                </a>
                @if(auth()->user() && auth()->user()->role === 'super_admin')
                <a href="{{ route('admin.users.index') }}" class="sidebar-item flex items-center px-4 py-3 {{ request()->routeIs('admin.users.*') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Users
                </a>
                @endif
                <a href="{{ route('admin.rules.index') }}" class="sidebar-item flex items-center px-4 py-3 {{ request()->routeIs('admin.rules.*') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.rules.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    SEO Rules
                </a>
            </nav>
        </div>
        <div class="p-6 border-t border-white/50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full sidebar-item flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white/50 hover:bg-red-50 hover:text-red-600 transition-colors border border-slate-200 hover:border-red-100 rounded-xl">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden z-10">
        <!-- Topbar (Glass) -->
        <header class="h-20 bg-transparent flex items-center justify-between px-8 z-10">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="md:hidden text-gray-500 hover:text-gray-700 mr-4 bg-white/50 p-2 rounded-xl backdrop-blur-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $header ?? '' }}</h1>
            </div>
        </header>

        <!-- Mobile Sidebar Overlay (Alpine.js) -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-40 flex md:hidden" x-cloak>
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="sidebarOpen = false"></div>
            <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full glass-panel m-4 rounded-[2rem] overflow-hidden shadow-2xl">
                <div class="h-20 flex items-center px-6 border-b border-white/50 justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('Logo.webp') }}" alt="Logo" class="w-9 h-9 object-contain drop-shadow-sm">
                        <span class="text-xl font-extrabold text-slate-800">Checker</span>
                    </div>
                    <button @click="sidebarOpen = false" class="text-slate-500 hover:text-slate-800 bg-white/50 p-2 rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto py-6">
                    <nav class="space-y-1.5">
                        <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center px-6 py-3 {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('tutorial.index') }}" class="sidebar-item flex items-center px-6 py-3 {{ request()->routeIs('tutorial.*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Tutorial
                        </a>
                        <a href="{{ route('seo.index') }}" class="sidebar-item flex items-center px-6 py-3 {{ request()->routeIs('seo.*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Run Checker
                        </a>
                        @if(auth()->user() && auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.users.index') }}" class="sidebar-item flex items-center px-6 py-3 {{ request()->routeIs('admin.users.*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Users
                        </a>
                        @endif
                        <a href="{{ route('admin.rules.index') }}" class="sidebar-item flex items-center px-6 py-3 {{ request()->routeIs('admin.rules.*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            SEO Rules
                        </a>
                    </nav>
                </div>
                <div class="p-6 border-t border-white/50">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full sidebar-item flex items-center justify-center px-4 py-3 text-sm font-semibold text-slate-600 bg-white/50 hover:bg-red-50 hover:text-red-600 transition-colors border border-slate-200 hover:border-red-100 rounded-xl">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Keluar (Logout)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-8 pb-32 md:pb-8">
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </div>
        
        <!-- Mobile Bottom Navigation (Glassmorphism) -->
        <nav class="md:hidden fixed bottom-6 left-4 right-4 z-50 glass-panel shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] rounded-[2rem] px-8 py-3 flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }} hover:text-indigo-600 transition-colors p-2">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            </a>
            
            <div class="relative">
                <a href="{{ route('seo.index') }}" class="absolute left-1/2 -translate-x-1/2 -top-12 w-14 h-14 bg-indigo-600 shadow-xl shadow-indigo-600/30 flex items-center justify-center text-white rounded-2xl hover:scale-105 hover:-translate-y-1 transition-all">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </a>
            </div>

            <a href="{{ route('admin.rules.index') }}" class="{{ request()->routeIs('admin.rules.*') ? 'text-indigo-600' : 'text-slate-400' }} hover:text-indigo-600 transition-colors p-2">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </a>
        </nav>
    </main>

</body>
</html>
