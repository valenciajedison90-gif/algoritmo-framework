@extends('install.layout', ['currentStep' => 1])

@section('title', 'Paso 1: Diagnóstico de Requisitos')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 sm:p-8 border-b border-slate-100">
        <h2 class="text-xl font-bold text-slate-900">Paso 1: Diagnóstico y Requisitos del Servidor</h2>
        <p class="text-sm text-slate-500 mt-1">
            Verificación automática del entorno de ejecución PHP, extensiones necesarias y permisos de escritura en disco antes de configurar la base de datos empresarial.
        </p>
    </div>

    <div class="p-6 sm:p-8 space-y-8">
        <!-- 1. Versión de PHP -->
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">Versión de PHP</h3>
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $requirements['php']['passed'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }}">
                <div class="flex items-center space-x-3">
                    @if($requirements['php']['passed'])
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">✓</div>
                    @else
                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center font-bold">✕</div>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-slate-900">PHP {{ $requirements['php']['current'] }}</p>
                        <p class="text-xs text-slate-500">Mínimo requerido para Laravel 12: PHP {{ $requirements['php']['required'] }}</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $requirements['php']['passed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $requirements['php']['passed'] ? 'Compatible' : 'Incompatible' }}
                </span>
            </div>
        </div>

        <!-- 2. Extensiones PHP Requeridas -->
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">Extensiones PHP Requeridas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($requirements['extensions'] as $ext)
                    <div class="p-3.5 rounded-xl border flex items-center justify-between {{ $ext['passed'] ? 'bg-white border-slate-200' : 'bg-rose-50 border-rose-200' }}">
                        <div class="flex items-center space-x-2.5 truncate">
                            @if($ext['passed'])
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span>
                            @else
                                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✕</span>
                            @endif
                            <span class="text-xs font-medium text-slate-800 truncate" title="{{ $ext['name'] }}">{{ $ext['name'] }}</span>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded {{ $ext['passed'] ? 'bg-slate-100 text-slate-600' : 'bg-rose-100 text-rose-800' }}">
                            {{ $ext['passed'] ? 'OK' : 'Falta' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. Controladores de Bases de Datos Empresariales Soportados -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Controladores de Base de Datos Detectados</h3>
                <span class="text-xs text-slate-400">Se requiere al menos uno activo</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($requirements['drivers'] as $key => $driver)
                    <div class="p-3.5 rounded-xl border text-center {{ $driver['loaded'] ? 'bg-emerald-50/40 border-emerald-200' : 'bg-slate-50 border-slate-200 opacity-60' }}">
                        <div class="text-xs font-bold text-slate-900">{{ $driver['name'] }}</div>
                        <div class="text-[11px] mt-1 font-semibold {{ $driver['loaded'] ? 'text-emerald-700' : 'text-slate-400' }}">
                            {{ $driver['loaded'] ? '✓ Disponible' : 'No instalado' }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">({{ $key }})</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 4. Permisos de Escritura en Disco -->
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">Permisos de Directorios y Archivos</h3>
            <div class="space-y-2">
                @foreach($requirements['permissions'] as $perm)
                    <div class="p-3 rounded-xl border flex items-center justify-between {{ $perm['passed'] ? 'bg-white border-slate-200' : 'bg-rose-50 border-rose-200' }}">
                        <div class="flex items-center space-x-2.5">
                            @if($perm['passed'])
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                            @else
                                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold">✕</span>
                            @endif
                            <div>
                                <span class="text-xs font-semibold text-slate-800">{{ $perm['name'] }}</span>
                                <span class="text-[11px] text-slate-400 ml-2">({{ $perm['path'] }})</span>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $perm['passed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $perm['passed'] ? 'Escritura OK' : 'Sin Permisos' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Barra de Acciones -->
    <div class="p-6 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-xs text-slate-500">
            @if($requirements['allPassed'])
                <span class="text-emerald-600 font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Requisitos del servidor validados correctamente.
                </span>
            @else
                <span class="text-rose-600 font-semibold">
                    Por favor resuelve los requisitos marcados en rojo antes de continuar.
                </span>
            @endif
        </div>

        <a href="{{ route('install.database') }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-500/20 transition-all {{ !$requirements['allPassed'] ? 'opacity-50 pointer-events-none' : '' }}">
            <span>Continuar a Selección de Base de Datos</span>
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
</div>
@endsection
