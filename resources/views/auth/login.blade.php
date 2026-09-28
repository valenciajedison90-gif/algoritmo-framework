<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Algoritmo Framework</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo e Introducción -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-xl bg-indigo-600 items-center justify-center text-white font-black text-2xl shadow-lg shadow-indigo-500/30">
                A
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Algoritmo Framework</h1>
            <p class="text-sm text-slate-400">Plataforma Empresarial Clean Architecture</p>
        </div>

        <!-- Alertas de Sesión / Error -->
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card de Formulario -->
        <div class="bg-slate-800/80 backdrop-blur-md rounded-2xl border border-slate-700/60 p-8 shadow-2xl">
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300">Correo Electrónico</label>
                    <div class="mt-1.5 relative">
                        <input type="email" name="email" id="email" value="{{ old('email', 'admin@algoritmo.com') }}" required autocomplete="email" autofocus
                               class="block w-full rounded-xl bg-slate-900/60 border border-slate-700 px-4 py-3 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-sm transition">
                    </div>
                    @error('email') <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300">Contraseña</label>
                    <div class="mt-1.5 relative">
                        <input type="password" name="password" id="password" value="password123" required autocomplete="current-password"
                               class="block w-full rounded-xl bg-slate-900/60 border border-slate-700 px-4 py-3 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-sm transition">
                    </div>
                    @error('password') <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500/20">
                        <span class="text-slate-400 select-none">Recordar dispositivo</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-sm shadow-lg shadow-indigo-600/30 transition duration-150 ease-in-out cursor-pointer">
                    Acceder al Sistema
                </button>
            </form>
        </div>

        <!-- Credenciales Demo Informativas -->
        <div class="bg-slate-800/40 rounded-xl border border-slate-800 p-4 text-xs text-slate-400 text-center space-y-1">
            <p class="font-semibold text-slate-300">Credenciales por defecto del sistema:</p>
            <p>Usuario: <span class="text-indigo-400 font-mono">admin@algoritmo.com</span> | Clave: <span class="text-indigo-400 font-mono">password123</span></p>
        </div>
    </div>
</body>
</html>