@extends('layouts.app')

@section('titulo', 'Gestión de Empresas')

@section('contenido')
<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Empresas</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Listado, control y configuración de organizaciones asociadas.</p>
        </div>
        <div>
            <a href="{{ route('empresas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Empresa
            </a>
        </div>
    </div>

    <!-- Filtros y Tabla -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="overflow-x-auto">
            <table id="tabla-empresas" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">NIT</th>
                        <th class="px-4 py-3">Razón Social</th>
                        <th class="px-4 py-3">Teléfono</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3 text-center">Estado</th>
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
    const tabla = $('#tabla-empresas').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('empresas.datatable') }}",
            type: "GET"
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nit', name: 'nit', render: data => `<span class="font-semibold text-gray-900 dark:text-white">${data}</span>` },
            { data: 'razon_social', name: 'razon_social' },
            { data: 'telefono', name: 'telefono', defaultContent: '-' },
            { data: 'email', name: 'email', defaultContent: '-' },
            { 
                data: 'activo', 
                name: 'activo',
                className: 'text-center',
                render: function (data, type, row) {
                    const badgeClass = data ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                    const texto = data ? 'Activo' : 'Inactivo';
                    return `<button onclick="cambiarEstadoEmpresa(${row.id}, ${!data})" class="px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass} hover:opacity-80 transition cursor-pointer">${texto}</button>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-right space-x-2',
                render: function (data, type, row) {
                    return `
                        <a href="/empresas/${row.id}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 text-xs font-semibold">Ver</a>
                        <a href="/empresas/${row.id}/edit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 text-xs font-semibold">Editar</a>
                        <button onclick="eliminarEmpresa(${row.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 text-xs font-semibold cursor-pointer">Eliminar</button>
                    `;
                }
            }
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        }
    });

    window.cambiarEstadoEmpresa = function (id, nuevoEstado) {
        if (!confirm('¿Desea cambiar el estado de esta empresa?')) return;
        fetch(`/empresas/${id}/toggle-status`, {
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

    window.eliminarEmpresa = function (id) {
        if (!confirm('¿Está seguro de eliminar esta empresa? Esta acción no se puede deshacer.')) return;
        fetch(`/empresas/${id}`, {
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
