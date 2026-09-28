@extends('install.layout', ['currentStep' => 4])

@section('title', 'Paso 4: Instalación Finalizada')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center p-8 sm:p-12">
    <!-- Icono de Éxito -->
    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/10">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">¡Instalación Completada con Éxito!</h2>
    <p class="text-slate-500 text-sm max-w-md mx-auto mt-2">
        Tu instancia de <strong>Algoritmo Framework</strong> ha sido configurada correctamente con su base de datos, tablas migradas y la cuenta administrativa inicial.
    </p>

    <!-- Tarjeta de Resumen -->
    <div class="mt-8 max-w-lg mx-auto bg-slate-50 border border-slate-200 rounded-2xl p-6 text-left space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2">
            Resumen de Configuración
        </h3>

        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">Motor de Datos:</span>
            <span class="font-semibold text-slate-800 uppercase">{{ env('DB_CONNECTION') }} ({{ env('DB_DATABASE') }})</span>
        </div>

        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">Estructura de Datos:</span>
            <span class="font-semibold text-emerald-600">✓ Tablas Migradas</span>
        </div>

        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">Arquitectura:</span>
            <span class="font-semibold text-slate-800">Clean Architecture (ADO / BLL / DAL)</span>
        </div>

        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">Protección de Seguridad:</span>
            <span class="font-semibold text-emerald-600">✓ Bloqueo installed.lock Activo</span>
        </div>
    </div>

    <!-- Mensaje de Seguridad -->
    <p class="text-xs text-slate-400 mt-6 max-w-md mx-auto">
        El asistente de instalación ha sido bloqueado automáticamente. Para reconfigurar el sistema en el futuro, elimina el archivo <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600">storage/framework/installed.lock</code>.
    </p>

    <!-- Botón Principal hacia el Login -->
    <div class="mt-8">
        <a href="{{ route('login') }}" 
           class="inline-flex items-center justify-center px-8 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-500/25 transition-all transform hover:-translate-y-0.5">
            <span>Iniciar Sesión en el Sistema</span>
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
    </div>
</div>
@endsection
