@extends('layouts.app')

@section('titulo', 'Panel de Control')

@section('contenido')
<div class="space-y-8">
    <!-- Bienvenida -->
    <div class="bg-gradient-to-r from-indigo-900 to-slate-900 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 max-w-2xl space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                Arquitectura Limpia BLL / DAL / ADO
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight">Bienvenido, {{ auth()->user()->name }}</h1>
            <p class="text-slate-300 text-sm">
                Has ingresado exitosamente al núcleo de <strong>Algoritmo Framework</strong>. El sistema se encuentra sincronizado con la base de datos empresarial.
            </p>
        </div>
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-12 pointer-events-none">
            <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>
    </div>

    <!-- Indicadores Principales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-4 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <span class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Módulo Empresas</span>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Activo</p>
                <a href="{{ route('empresas.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline mt-1 inline-block">Gestionar empresas &rarr;</a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <span class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Usuarios del Sistema</span>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Protegido</p>
                <a href="{{ route('usuarios.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline mt-1 inline-block">Administrar usuarios &rarr;</a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-4 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <span class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Trazabilidad y Auditoría</span>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">AuditService</p>
                <span class="text-xs text-purple-600 dark:text-purple-400 font-semibold mt-1 inline-block">Registro automático</span>
            </div>
        </div>
    </div>
</div>
@endsection