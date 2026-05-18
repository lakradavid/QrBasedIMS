<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — QR Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }

        .input-field {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 0.875rem;
            color: #111827;
            background: #fafafa;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
            outline: none;
        }
        .input-field:focus {
            border-color: #4f46e5;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }
        .input-field.error {
            border-color: #f87171;
            background: #fff5f5;
        }
        .input-field::placeholder { color: #9ca3af; }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.1s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            letter-spacing: 0.01em;
        }
        .btn-primary:hover {
            opacity: 0.93;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }
        .btn-primary:active { transform: scale(0.99); }

        /* Animated QR grid on the left panel */
        .qr-dot {
            width: 6px;
            height: 6px;
            border-radius: 1.5px;
            background: rgba(255,255,255,0.18);
            animation: pulse-dot 3s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 0.18; }
            50% { opacity: 0.55; }
        }
    </style>
</head>
<body class="min-h-screen flex bg-white">

    <!-- ── Left brand panel ── -->
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative flex-col justify-between overflow-hidden"
         style="background: linear-gradient(145deg, #312e81 0%, #4338ca 45%, #6366f1 100%);">

        <!-- Decorative dot grid -->
        <div class="absolute inset-0 p-10 grid gap-3 opacity-60"
             style="grid-template-columns: repeat(18, 1fr); grid-template-rows: repeat(22, 1fr);">
            @for ($i = 0; $i < 396; $i++)
                <div class="qr-dot" style="animation-delay: {{ ($i * 37) % 3000 }}ms"></div>
            @endfor
        </div>

        <!-- Decorative blobs -->
        <div class="absolute top-[-80px] right-[-80px] w-72 h-72 rounded-full"
             style="background: rgba(255,255,255,0.06); filter: blur(40px);"></div>
        <div class="absolute bottom-[-60px] left-[-60px] w-80 h-80 rounded-full"
             style="background: rgba(255,255,255,0.05); filter: blur(50px);"></div>

        <!-- Top logo mark -->
        <div class="relative z-10 p-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <span class="text-white font-semibold text-lg tracking-tight">QR Inventory</span>
            </div>
        </div>

        <!-- Centre copy -->
        <div class="relative z-10 px-10 pb-4">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-1.5 mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-white/80 text-xs font-medium">Real-time tracking</span>
            </div>
            <h2 class="text-4xl font-bold text-white leading-tight mb-4">
                Smart inventory,<br>
                <span class="text-indigo-200">zero guesswork.</span>
            </h2>
            <p class="text-indigo-200 text-sm leading-relaxed max-w-xs">
                Scan, track, and manage your assets with QR codes. Know exactly what you have and where it is — always.
            </p>
        </div>

        <!-- Feature pills -->
        <div class="relative z-10 px-10 pb-12 flex flex-col gap-3">
            @foreach ([
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Instant QR code generation'],
                ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Location-aware stock control'],
                ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Reports & analytics'],
            ] as $feature)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-indigo-100 text-sm">{{ $feature['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ── Right form panel ── -->
    <div class="flex-1 flex flex-col justify-center items-center px-6 py-12 sm:px-12 lg:px-16 xl:px-24 bg-gray-50">

        <!-- Mobile logo (shown only on small screens) -->
        <div class="lg:hidden flex items-center gap-2 mb-10">
            <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <span class="text-gray-900 font-semibold text-lg">QR Inventory</span>
        </div>

        <div class="w-full max-w-sm">

            <!-- Heading -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h1>
                <p class="text-gray-500 text-sm">Sign in to your account to continue</p>
            </div>

            <!-- Session status -->
            @if (session('status'))
                <div class="mb-5 flex items-start gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
                    <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm text-emerald-700">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               required autofocus autocomplete="email"
                               placeholder="you@example.com"
                               class="input-field pl-10 @error('email') error @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" type="password" name="password"
                               required autocomplete="current-password"
                               placeholder="••••••••"
                               class="input-field pl-10 pr-10 @error('password') error @enderror">
                        <!-- Toggle visibility -->
                        <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="flex items-center gap-2.5">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer">
                    <label for="remember_me" class="text-sm text-gray-600 cursor-pointer select-none">
                        Keep me signed in
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-primary">
                    Sign in
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-gray-50 px-3 text-xs text-gray-400 font-medium uppercase tracking-wider">Demo access</span>
                </div>
            </div>

            <!-- Demo credentials card -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-xs text-gray-500 leading-relaxed">
                    <p class="font-medium text-gray-700 mb-1">Demo credentials</p>
                    <p>Email: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700 font-mono">admin@inventory.com</code></p>
                    <p class="mt-0.5">Password: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700 font-mono">password</code></p>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <p class="mt-10 text-xs text-gray-400 text-center">
            &copy; {{ date('Y') }} QR Inventory. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
            } else {
                input.type = 'password';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
            }
        }
    </script>
</body>
</html>
