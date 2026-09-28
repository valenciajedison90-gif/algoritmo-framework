<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asistente de Instalación') - Algoritmo Framework</title>
    <!-- Tailwind CSS CDN para renderizado autónomo e inmediato sin dependencias locales -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col justify-between text-slate-800 antialiased">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-md shadow-brand-500/20">
                    A
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-none">Algoritmo Framework</h1>
                    <p class="text-xs text-slate-500 mt-1">Asistente de Instalación y Conexión Web</p>
                </div>
            </div>
            <div class="text-xs font-semibold px-2.5 py-1 bg-brand-50 text-brand-700 border border-brand-200 rounded-full">
                v2.0.0 • Laravel 12
            </div>
        </div>
    </header>

    <!-- Stepper de Pasos -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 py-4">
            @php
                $step = $currentStep ?? 1;
            @endphp
            <div class="grid grid-cols-4 gap-2 sm:gap-4 text-center">
                <!-- Paso 1 -->
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $step >= 1 ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400' }}">
                        1
                    </div>
                    <span class="text-xs font-medium mt-1 {{ $step >= 1 ? 'text-brand-700 font-semibold' : 'text-slate-400' }}">
                        Diagnóstico
                    </span>
                </div>

                <!-- Paso 2 -->
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $step >= 2 ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400' }}">
                        2
                    </div>
                    <span class="text-xs font-medium mt-1 {{ $step >= 2 ? 'text-brand-700 font-semibold' : 'text-slate-400' }}">
                        Base de Datos
                    </span>
                </div>

                <!-- Paso 3 -->
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $step >= 3 ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400' }}">
                        3
                    </div>
                    <span class="text-xs font-medium mt-1 {{ $step >= 3 ? 'text-brand-700 font-semibold' : 'text-slate-400' }}">
                        Inicialización
                    </span>
                </div>

                <!-- Paso 4 -->
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $step >= 4 ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400' }}">
                        4
                    </div>
                    <span class="text-xs font-medium mt-1 {{ $step >= 4 ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                        Listo
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <main class="flex-grow py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Alertas Flash -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start space-x-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <strong class="font-semibold">¡Éxito!</strong> {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start space-x-3 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <div>
                        <strong class="font-semibold">Error:</strong> {{ session('error') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm shadow-sm">
                    <strong class="font-semibold">Por favor corrige los siguientes campos:</strong>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Contenido Dinámico -->
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Algoritmo Framework • Arquitectura N-Tier Desacoplada (ADO / BLL / DAL)</p>
    </footer>

    @stack('scripts')
</body>
</html>
