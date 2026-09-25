<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SEO HTML Checker</title>
    <x-favicon />
    <!-- Use Tailwind CSS via CDN for quick styling as requested -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom background gradient with soft waves feel */
        body {
            background-color: #dbeafe;
            background-image: 
                radial-gradient(at 40% 20%, hsla(210,100%,74%,1) 0px, transparent 50%),
                radial-gradient(at 80% 0%, hsla(230,100%,86%,1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(205,100%,85%,1) 0px, transparent 50%),
                radial-gradient(at 80% 50%, hsla(240,100%,89%,1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(215,100%,84%,1) 0px, transparent 50%),
                radial-gradient(at 80% 100%, hsla(242,100%,85%,1) 0px, transparent 50%),
                radial-gradient(at 0% 0%, hsla(243,100%,94%,1) 0px, transparent 50%);
            background-attachment: fixed;
        }
        /* Glassmorphism utility */
        .glass-card {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            border-color: rgba(59, 130, 246, 0.5);
            outline: none;
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased text-gray-800 flex items-center justify-center p-6 relative overflow-hidden">

    <!-- Decorative floating elements -->
    <div class="absolute top-20 left-20 w-64 h-64 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
    <div class="absolute top-20 right-20 w-64 h-64 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-1/2 w-64 h-64 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>

    <div class="w-full max-w-5xl flex flex-col md:flex-row items-center justify-between z-10 gap-12">
        
        <!-- Left Side: Branding & Info -->
        <div class="w-full md:w-1/2 flex flex-col justify-center text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start gap-4 mb-4">
                <img src="{{ asset('Logo.webp') }}" alt="Logo" class="w-16 h-16 md:w-20 md:h-20 object-contain drop-shadow-md">
                <h1 class="text-5xl md:text-6xl font-extrabold text-blue-900 tracking-tight" style="font-family: 'Inter', sans-serif;">
                    SEO <br class="hidden md:block" />Checker
                </h1>
            </div>
            <p class="text-lg text-blue-800/80 mb-8 max-w-md mx-auto md:mx-0">
                Advanced HTML Analysis & Optimization. Log in to manage SEO rules and monitor performance.
            </p>
            
            <!-- Chat bubble style decoration similar to the image -->
            <div class="hidden md:flex flex-col space-y-3 items-start opacity-70">
                <div class="bg-white/60 backdrop-blur-sm px-4 py-2 rounded-2xl rounded-bl-sm text-sm font-medium text-gray-700 inline-block shadow-sm">
                    Verifying your identity...
                </div>
                <div class="bg-white/60 backdrop-blur-sm px-4 py-2 rounded-2xl rounded-bl-sm text-sm font-medium text-gray-700 inline-block shadow-sm">
                    Secure access required to proceed.
                </div>
            </div>
        </div>

        <!-- Right Side: Login Card -->
        <div class="w-full md:w-1/2 max-w-md">
            <div class="glass-card rounded-3xl p-8 md:p-10 relative">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                <p class="text-sm text-gray-600 mb-8">Sign in to your admin account</p>

                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/30 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm backdrop-blur-sm">
                        <ul class="list-none m-0 p-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div>
                        <input type="email" name="email" id="email" required autofocus placeholder="Email Address"
                            class="glass-input w-full rounded-xl p-4 text-gray-800 placeholder-gray-500 font-medium" 
                            value="{{ old('email') }}">
                    </div>

                    <div class="relative">
                        <input type="password" name="password" id="password" required placeholder="Password"
                            class="glass-input w-full rounded-xl p-4 text-gray-800 placeholder-gray-500 font-medium">
                        <!-- Eye icon decoration -->
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center mt-2 mb-6">
                        <input id="remember" type="checkbox" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember" class="ml-2 text-sm font-medium text-gray-600">Remember me</label>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg transform transition hover:-translate-y-0.5">
                        Log In
                    </button>
                </form>
            </div>
        </div>
        
    </div>

</body>
</html>
