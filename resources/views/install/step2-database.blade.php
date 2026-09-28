@extends('install.layout', ['currentStep' => 2])

@section('title', 'Paso 2: Conexión Multi-Base de Datos')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 sm:p-8 border-b border-slate-100">
        <h2 class="text-xl font-bold text-slate-900">Paso 2: Conexión de Base de Datos Empresarial</h2>
        <p class="text-sm text-slate-500 mt-1">
            Selecciona el motor de base de datos de tu infraestructura (MySQL, MariaDB, PostgreSQL, SQL Server u Oracle). Puedes probar la conexión en tiempo real antes de guardar la configuración en el archivo <code class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded text-xs font-mono">.env</code>.
        </p>
    </div>

    <!-- Banner de Resultado de Prueba AJAX -->
    <div id="test-result-box" class="hidden mx-6 sm:mx-8 mt-6 p-4 rounded-xl border text-sm flex items-start space-x-3 transition-all duration-300">
        <div id="test-result-icon" class="w-5 h-5 mt-0.5 flex-shrink-0"></div>
        <div class="flex-grow">
            <strong id="test-result-title" class="font-semibold block"></strong>
            <span id="test-result-message" class="text-xs mt-0.5 block"></span>
        </div>
    </div>

    <form action="{{ route('install.database.save') }}" method="POST" id="db-form">
        @csrf
        <div class="p-6 sm:p-8 space-y-6">
            <!-- 1. Selector de Motor de Base de Datos Empresarial -->
            <div>
                <label for="driver" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">
                    Motor de Base de Datos (Gestor RDBMS) *
                </label>
                <div class="relative">
                    <select name="driver" id="driver" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all appearance-none cursor-pointer">
                        <option value="mysql" {{ old('driver', $currentConfig['driver']) === 'mysql' ? 'selected' : '' }}>
                            🐬 MySQL Server (Puerto 3306 - Estándar Empresarial Web)
                        </option>
                        <option value="mariadb" {{ old('driver', $currentConfig['driver']) === 'mariadb' ? 'selected' : '' }}>
                            🦭 MariaDB Enterprise (Puerto 3306 - RDBMS Open Source de Alto Desempeño)
                        </option>
                        <option value="pgsql" {{ old('driver', $currentConfig['driver']) === 'pgsql' ? 'selected' : '' }}>
                            🐘 PostgreSQL (Puerto 5432 - Alta Concurrencia y Datos Complejos)
                        </option>
                        <option value="sqlsrv" {{ old('driver', $currentConfig['driver']) === 'sqlsrv' ? 'selected' : '' }}>
                            🏢 Microsoft SQL Server (Puerto 1433 - Entornos Windows / Azure Enterprise)
                        </option>
                        <option value="oracle" {{ old('driver', $currentConfig['driver']) === 'oracle' ? 'selected' : '' }}>
                            🏛️ Oracle Database (Puerto 1521 - Corporativo / SID / Services OCI)
                        </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7 7"></path></svg>
                    </div>
                </div>
                <p id="driver-hint" class="text-xs text-slate-500 mt-1.5"></p>
            </div>

            <!-- Campos Red (Host & Port) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="host" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                        Dirección del Servidor / Host *
                    </label>
                    <input type="text" name="host" id="host" required
                           value="{{ old('host', $currentConfig['host'] ?: '127.0.0.1') }}" 
                           placeholder="127.0.0.1 o localhost o IP"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all font-mono">
                </div>

                <div>
                    <label for="port" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                        Puerto de Conexión *
                    </label>
                    <input type="number" name="port" id="port" required
                           value="{{ old('port', $currentConfig['port'] ?: '3306') }}" 
                           placeholder="3306"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all font-mono">
                </div>
            </div>

            <!-- Nombre de Base de Datos o Servicio -->
            <div>
                <label for="database" id="database-label" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                    Nombre de la Base de Datos *
                </label>
                <input type="text" name="database" id="database" required 
                       value="{{ old('database', $currentConfig['database'] ?: 'algoritmo_db') }}" 
                       placeholder="algoritmo_db"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all font-mono">
                <span id="database-hint" class="text-[11px] text-slate-400 mt-1 block">
                    Nombre del esquema o base de datos relacional en el servidor.
                </span>
            </div>

            <!-- Campos Credenciales (Usuario & Contraseña) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" id="username-label" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                        Usuario de la BD *
                    </label>
                    <input type="text" name="username" id="username" required
                           value="{{ old('username', $currentConfig['username'] ?: 'root') }}" 
                           placeholder="root"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all font-mono">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                        Contraseña de la BD
                    </label>
                    <input type="password" name="password" id="password" 
                           value="{{ old('password', $currentConfig['password']) }}" 
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                </div>
            </div>

            <!-- Opción para Crear la Base de Datos automáticamente si no existe -->
            <div id="create-db-option" class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <label class="relative flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="create_db_if_not_exists" id="create_db_if_not_exists" value="1" checked 
                           class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500">
                    <span class="text-xs text-slate-700 font-medium">
                        Intentar crear la base de datos automáticamente en el servidor si aún no existe
                    </span>
                </label>
            </div>
        </div>

        <!-- Barra de Acciones y Botón de Prueba en Tiempo Real -->
        <div class="p-6 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-3 w-full sm:w-auto">
                <a href="{{ route('install.index') }}" 
                   class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                    ← Volver a Diagnóstico
                </a>

                <!-- Botón Probar Conexión (AJAX) -->
                <button type="button" id="btn-test-connection" 
                        class="px-4 py-2.5 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-xl transition-all inline-flex items-center shadow-sm">
                    <svg id="test-spinner" class="hidden animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-brand-700" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span id="test-btn-text">⚡ Probar Conexión</span>
                </button>
            </div>

            <button type="submit" id="btn-submit" 
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-500/20 transition-all">
                <span>Guardar y Continuar</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const driverSelect = document.getElementById('driver');
    const portInput = document.getElementById('port');
    const dbInput = document.getElementById('database');
    const dbLabel = document.getElementById('database-label');
    const dbHint = document.getElementById('database-hint');
    const usernameInput = document.getElementById('username');
    const driverHint = document.getElementById('driver-hint');
    const createDbOption = document.getElementById('create-db-option');

    const btnTest = document.getElementById('btn-test-connection');
    const testSpinner = document.getElementById('test-spinner');
    const testBtnText = document.getElementById('test-btn-text');
    const resultBox = document.getElementById('test-result-box');
    const resultIcon = document.getElementById('test-result-icon');
    const resultTitle = document.getElementById('test-result-title');
    const resultMessage = document.getElementById('test-result-message');

    const portMap = {
        'mysql': { port: 3306, user: 'root', label: 'Nombre de la Base de Datos *', hint: 'Puerto por defecto: 3306. Requiere extensión pdo_mysql.' },
        'mariadb': { port: 3306, user: 'root', label: 'Nombre de la Base de Datos *', hint: 'Puerto por defecto: 3306. Totalmente compatible con MySQL.' },
        'pgsql': { port: 5432, user: 'postgres', label: 'Nombre de la Base de Datos *', hint: 'Puerto por defecto: 5432. Requiere extensión pdo_pgsql.' },
        'sqlsrv': { port: 1433, user: 'sa', label: 'Nombre de la Base de Datos *', hint: 'Puerto por defecto: 1433. Requiere drivers Microsoft SQLSRV.' },
        'oracle': { port: 1521, user: 'SYSTEM', label: 'Nombre del Servicio / SID / PDB *', hint: 'Puerto por defecto: 1521. Requiere extensión pdo_oci u OCI8.' },
    };

    function handleDriverChange() {
        const driver = driverSelect.value;
        const config = portMap[driver] || portMap['mysql'];

        portInput.value = config.port;
        dbLabel.textContent = config.label;
        driverHint.textContent = config.hint;

        if (!usernameInput.value || usernameInput.value === 'root' || usernameInput.value === 'postgres' || usernameInput.value === 'sa' || usernameInput.value === 'SYSTEM') {
            usernameInput.value = config.user;
        }

        if (driver === 'oracle') {
            createDbOption.classList.add('hidden');
            dbHint.textContent = 'Ingresa el Service Name (ej. ORCL, XE, XEPDB1) configurado en el Listener de Oracle.';
        } else {
            createDbOption.classList.remove('hidden');
            dbHint.textContent = 'Nombre del esquema o base de datos relacional en el servidor.';
        }
    }

    driverSelect.addEventListener('change', handleDriverChange);
    handleDriverChange();

    // Probar conexión AJAX en vivo
    btnTest.addEventListener('click', async function () {
        const formData = new FormData(document.getElementById('db-form'));
        const payload = Object.fromEntries(formData.entries());

        btnTest.disabled = true;
        testSpinner.classList.remove('hidden');
        testBtnText.textContent = 'Probando conexión...';
        resultBox.classList.add('hidden');

        try {
            const response = await fetch("{{ route('install.database.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            resultBox.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800', 'bg-amber-50', 'border-amber-200', 'text-amber-800');

            if (data.estado) {
                // Éxito total
                resultBox.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-800');
                resultIcon.innerHTML = `<svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                resultTitle.textContent = '¡Conexión Exitosa!';
                resultMessage.textContent = data.mensaje;
            } else if (data.datos && data.datos.can_create_db) {
                // Servidor conectado, BD pendiente por crear
                resultBox.classList.add('bg-amber-50', 'border-amber-200', 'text-amber-800');
                resultIcon.innerHTML = `<svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
                resultTitle.textContent = 'Servidor Accesible (BD no encontrada)';
                resultMessage.textContent = data.mensaje;
                document.getElementById('create_db_if_not_exists').checked = true;
            } else {
                // Error de conexión
                resultBox.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
                resultIcon.innerHTML = `<svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                resultTitle.textContent = 'Fallo de Conexión';
                resultMessage.textContent = data.mensaje || 'No se pudo conectar con el servidor de base de datos.';
            }
        } catch (err) {
            resultBox.classList.remove('hidden');
            resultBox.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
            resultIcon.innerHTML = `<svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
            resultTitle.textContent = 'Error Inesperado';
            resultMessage.textContent = 'Hubo un error al ejecutar la prueba: ' + err.message;
        } finally {
            btnTest.disabled = false;
            testSpinner.classList.add('hidden');
            testBtnText.textContent = '⚡ Probar Conexión';
        }
    });
});
</script>
@endpush
