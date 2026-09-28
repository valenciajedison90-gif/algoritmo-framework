@extends('install.layout', ['currentStep' => 3])

@section('title', 'Paso 3: Inicialización del Sistema')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 sm:p-8 border-b border-slate-100">
        <h2 class="text-xl font-bold text-slate-900">Paso 3: Inicialización de Tablas y Cuenta Principal</h2>
        <p class="text-sm text-slate-500 mt-1">
            El instalador ejecutará las migraciones para crear la estructura de base de datos y registrará tu primera empresa y la cuenta Super Administrador utilizando la arquitectura limpia (ADO / BLL / DAL).
        </p>
    </div>

    <form action="{{ route('install.setup.execute') }}" method="POST" id="setup-form">
        @csrf
        <div class="p-6 sm:p-8 space-y-8">
            <!-- 1. Datos de la Empresa Principal -->
            <div>
                <div class="flex items-center space-x-2 pb-3 border-b border-slate-100 mb-4">
                    <div class="w-6 h-6 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-xs">1</div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Datos de la Empresa Principal</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="empresa_nombre" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Razón Social / Nombre de la Empresa *
                        </label>
                        <input type="text" name="empresa_nombre" id="empresa_nombre" required
                               value="{{ old('empresa_nombre', 'Algoritmo Technologies S.A.S.') }}"
                               placeholder="Ej. Mi Empresa Principal"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="empresa_nit" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            NIT / Identificación Tributaria *
                        </label>
                        <input type="text" name="empresa_nit" id="empresa_nit" required
                               value="{{ old('empresa_nit', '900123456-7') }}"
                               placeholder="900123456-7"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="empresa_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Correo Electrónico Corporativo
                        </label>
                        <input type="email" name="empresa_email" id="empresa_email"
                               value="{{ old('empresa_email', 'contacto@algoritmo.com') }}"
                               placeholder="contacto@empresa.com"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="empresa_telefono" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Teléfono
                        </label>
                        <input type="text" name="empresa_telefono" id="empresa_telefono"
                               value="{{ old('empresa_telefono', '+57 300 123 4567') }}"
                               placeholder="+57 300 123 4567"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="empresa_direccion" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Dirección
                        </label>
                        <input type="text" name="empresa_direccion" id="empresa_direccion"
                               value="{{ old('empresa_direccion', 'Calle Principal #123') }}"
                               placeholder="Calle Principal #123"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                </div>
            </div>

            <!-- 2. Datos del Super Administrador -->
            <div>
                <div class="flex items-center space-x-2 pb-3 border-b border-slate-100 mb-4">
                    <div class="w-6 h-6 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-xs">2</div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Cuenta del Super Administrador</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="admin_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Nombre Completo *
                        </label>
                        <input type="text" name="admin_name" id="admin_name" required
                               value="{{ old('admin_name', 'Administrador General') }}"
                               placeholder="Nombre y Apellidos"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="admin_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Correo Electrónico (Usuario de Acceso) *
                        </label>
                        <input type="email" name="admin_email" id="admin_email" required
                               value="{{ old('admin_email', 'admin@algoritmo.com') }}"
                               placeholder="admin@algoritmo.com"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="admin_documento" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Documento de Identidad
                        </label>
                        <input type="text" name="admin_documento" id="admin_documento"
                               value="{{ old('admin_documento', '1000000001') }}"
                               placeholder="1000000001"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="admin_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Contraseña *
                        </label>
                        <input type="password" name="admin_password" id="admin_password" required minlength="6"
                               value="{{ old('admin_password', 'admin123') }}"
                               placeholder="Mínimo 6 caracteres"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>

                    <div>
                        <label for="admin_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Confirmar Contraseña *
                        </label>
                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required minlength="6"
                               value="{{ old('admin_password_confirmation', 'admin123') }}"
                               placeholder="Repetir contraseña"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                </div>
            </div>

            <!-- Aviso de Ejecución de Migraciones -->
            <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-blue-800 flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="leading-relaxed">
                    Al presionar <strong>"Instalar y Configurar Sistema"</strong>, se ejecutarán las migraciones de Laravel para crear las tablas <code class="bg-blue-100 px-1 py-0.5 rounded">empresas</code>, <code class="bg-blue-100 px-1 py-0.5 rounded">users</code> y <code class="bg-blue-100 px-1 py-0.5 rounded">audits</code>, y se insertará la empresa y el administrador mediante los servicios transaccionales BLL.
                </div>
            </div>
        </div>

        <!-- Barra de Acciones -->
        <div class="p-6 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('install.database') }}" 
               class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                ← Volver a Base de Datos
            </a>

            <button type="submit" id="btn-submit-setup"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-500/20 transition-all">
                <svg id="setup-spinner" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span id="setup-btn-text">🚀 Instalar y Configurar Sistema</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('setup-form');
    const btn = document.getElementById('btn-submit-setup');
    const spinner = document.getElementById('setup-spinner');
    const btnText = document.getElementById('setup-btn-text');

    form.addEventListener('submit', function () {
        btn.disabled = true;
        spinner.classList.remove('hidden');
        btnText.textContent = 'Migrando e instalando sistema...';
    });
});
</script>
@endpush
