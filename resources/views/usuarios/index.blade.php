@extends('layouts.app')

@section('titulo', 'Usuarios del Sistema')

@section('contenido')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Usuarios del Sistema</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Control de acceso, roles y vinculación con empresas.</p>
        </div>
        <div>
            <a href="{{ route('usuarios.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Usuario
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="overflow-x-auto">
            <table id="tabla-usuarios" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Documento</th>
                        <th class="px-4 py-3">Empresa</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3">Último Acceso</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabla = $('#tabla-usuarios').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('usuarios.datatable') }}",
            type: "GET"
        },
        columns: [
            { data: 'id', name: 'u.id' },
            { data: 'name', name: 'u.name', render: data => `<span class="font-semibold text-gray-900 dark:text-white">${data}</span>` },
            { data: 'email', name: 'u.email' },
            { data: 'documento', name: 'u.documento', defaultContent: '-' },
            { data: 'empresa_nombre', name: 'e.razon_social', defaultContent: '<span class="text-xs italic text-gray-400">Sin empresa</span>' },
            { 
                data: 'rol', 
                name: 'u.rol',
                render: data => {
                    const colors = {
                        superadmin: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                        admin: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
                        operador: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                        consulta: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                    };
                    const color = colors[data] || colors.consulta;
                    return `<span class="px-2 py-0.5 rounded text-xs font-semibold uppercase ${color}">${data}</span>`;
                }
            },
            { 
                data: 'activo', 
                name: 'u.activo',
                className: 'text-center',
                render: function (data, type, row) {
                    const badgeClass = data ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                    const texto = data ? 'Activo' : 'Inactivo';
                    return `<button onclick="cambiarEstadoUsuario(${row.id}, ${!data})" class="px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass} hover:opacity-80 transition cursor-pointer">${texto}</button>`;
                }
            },
            { data: 'ultimo_acceso', name: 'u.ultimo_acceso', defaultContent: '<span class="text-xs text-gray-400">Nunca</span>' },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-right space-x-2',
                render: function (data, type, row) {
                    return `
                        <a href="/usuarios/${row.id}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 text-xs font-semibold">Ver</a>
                        <a href="/usuarios/${row.id}/edit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 text-xs font-semibold">Editar</a>
                        <a href="/usuarios/${row.id}/cambiar-password" class="text-amber-600 hover:text-amber-900 dark:text-amber-400 text-xs font-semibold">Clave</a>
                        <button onclick="eliminarUsuario(${row.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 text-xs font-semibold cursor-pointer">Eliminar</button>
                    `;
                }
            }
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        }
    });

    window.cambiarEstadoUsuario = function (id, nuevoEstado) {
        if (!confirm('¿Desea cambiar el estado del usuario?')) return;
        fetch(`/usuarios/${id}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ activo: nuevoEstado })
        })
        .then(res => res.json())
        .then(res => {
            if (res.estado) {
                tabla.ajax.reload(null, false);
            } else {
                alert(res.mensaje);
            }
        })
        .catch(err => alert('Error de conexión'));
    };

    window.eliminarUsuario = function (id) {
        if (!confirm('¿Está seguro de eliminar este usuario? Esta acción es irreversible.')) return;
        fetch(`/usuarios/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.estado) {
                tabla.ajax.reload(null, false);
            } else {
                alert(res.mensaje);
            }
        })
        .catch(err => alert('Error de conexión'));
    };
});
</script>
@endpush
