@extends('layouts.app')

@section('titulo', 'Detalle de Empresa')

@section('contenido')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $empresa->razon_social }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Información detallada de la empresa.</p>
        </div>
        <div class="space-x-3">
            <a href="{{ route('empresas.edit', $empresa->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Editar</a>
            <a href="{{ route('empresas.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">Volver</a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">NIT</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $empresa->nit }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</dt>
                <dd class="mt-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $empresa->activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                        {{ $empresa->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Teléfono</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $empresa->telefono ?? 'No registrado' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Correo Electrónico</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $empresa->email ?? 'No registrado' }}</dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dirección</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $empresa->direccion ?? 'No registrada' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha de Creación</dt>
                <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $empresa->created_at ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Última Actualización</dt>
                <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $empresa->updated_at ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
