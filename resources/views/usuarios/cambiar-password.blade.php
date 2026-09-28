@extends('layouts.app')

@section('titulo', 'Cambiar Contraseña')

@section('contenido')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cambiar Contraseña</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Usuario: <span class="font-semibold text-gray-800 dark:text-white">{{ $usuario->name }}</span> ({{ $usuario->email }})</p>
        </div>
        <a href="{{ route('usuarios.show', $usuario->id) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            &larr; Volver al perfil
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('usuarios.cambiar-password.post', $usuario->id) }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="nueva_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva Contraseña (mínimo 8 caracteres) *</label>
                <input type="password" name="nueva_password" id="nueva_password" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('nueva_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nueva_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar Nueva Contraseña *</label>
                <input type="password" name="nueva_password_confirmation" id="nueva_password_confirmation" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('usuarios.show', $usuario->id) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancelar</a>
                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium shadow-sm transition">Restablecer Contraseña</button>
            </div>
        </form>
    </div>
</div>
@endsection
