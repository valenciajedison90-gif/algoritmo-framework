@extends('layouts.app')

@section('titulo', 'Editar Usuario')

@section('contenido')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Usuario: {{ $usuario->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Actualice la información de la cuenta y roles.</p>
        </div>
        <a href="{{ route('usuarios.index') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
            &larr; Volver al listado
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Completo *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cédula / Documento</label>
                    <input type="text" name="documento" id="documento" value="{{ old('documento', $usuario->documento) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('documento') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="empresa_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Empresa Asociada</label>
                    <select name="empresa_id" id="empresa_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">-- Sin Empresa Asignada --</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}" {{ old('empresa_id', $usuario->empresa_id) == $emp->id ? 'selected' : '' }}>{{ $emp->razon_social }} (NIT: {{ $emp->nit }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rol del Usuario *</label>
                    <select name="rol" id="rol" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="operador" {{ old('rol', $usuario->rol) == 'operador' ? 'selected' : '' }}>Operador</option>
                        <option value="admin" {{ old('rol', $usuario->rol) == 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="superadmin" {{ old('rol', $usuario->rol) == 'superadmin' ? 'selected' : '' }}>Super Administrador</option>
                        <option value="consulta" {{ old('rol', $usuario->rol) == 'consulta' ? 'selected' : '' }}>Solo Consulta</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $usuario->activo ? '1' : '0') == '1' ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Usuario activo con permiso de acceso</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('usuarios.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancelar</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition">Actualizar Usuario</button>
            </div>
        </form>
    </div>
</div>
@endsection
