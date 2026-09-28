<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\InstallService;
use App\Core\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Controlador del Asistente de Instalación Web (tipo Moodle)
 *
 * Guía al usuario paso a paso:
 * 1. Diagnóstico de requisitos y permisos
 * 2. Detección, prueba y configuración de Base de Datos
 * 3. Ejecución de migraciones y registro de Empresa / Administrador inicial
 * 4. Certificación y redirección al Login
 */
class InstallController extends Controller
{
    public function __construct(
        protected InstallService $installService
    ) {}

    /**
     * Paso 1: Diagnóstico de Requisitos del Sistema
     */
    public function index(): View
    {
        $requirements = $this->installService->checkRequirements();

        return view('install.step1-requirements', compact('requirements'));
    }

    /**
     * Paso 2: Formulario de Configuración de Base de Datos
     */
    public function databaseForm(): View
    {
        $currentConfig = [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'algoritmo_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];

        return view('install.step2-database', compact('currentConfig'));
    }

    /**
     * Endpoint AJAX para Probar la Conexión a la Base de Datos
     */
    public function testDatabase(Request $request): JsonResponse
    {
        $config = $request->validate([
            'driver' => 'required|string|in:mysql,mariadb,pgsql,sqlite,sqlsrv',
            'host' => 'nullable|string',
            'port' => 'nullable|numeric',
            'database' => 'required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $test = $this->installService->testDatabaseConnection($config);

        if ($test['success']) {
            return ResponseHelper::success($test['message'], $test);
        }

        return ResponseHelper::error($test['message'], 400, $test);
    }

    /**
     * Guarda la configuración de la Base de Datos en .env y continúa
     */
    public function saveDatabase(Request $request): RedirectResponse
    {
        $config = $request->validate([
            'driver' => 'required|string|in:mysql,mariadb,pgsql,sqlite,sqlsrv',
            'host' => 'nullable|string',
            'port' => 'nullable|numeric',
            'database' => 'required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'create_db_if_not_exists' => 'nullable|boolean',
        ]);

        // Si solicitó crear la base de datos si no existe
        if (!empty($config['create_db_if_not_exists'])) {
            $this->installService->createDatabaseIfNotExists($config);
        }

        // Probar conexión real
        $test = $this->installService->testDatabaseConnection($config);

        if (!$test['success']) {
            return back()->withInput()->with('error', $test['message']);
        }

        // Guardar configuración en .env
        $saved = $this->installService->saveDatabaseConfiguration($config);

        if (!$saved) {
            return back()->withInput()->with('error', 'No se pudo escribir en el archivo .env. Verifica los permisos de escritura.');
        }

        return redirect()->route('install.setup')
            ->with('success', '¡Conexión a la Base de Datos configurada exitosamente! Procede con la inicialización del sistema.');
    }

    /**
     * Paso 3: Formulario de Empresa Inicial y Administrador
     */
    public function setupForm(): View
    {
        return view('install.step3-setup');
    }

    /**
     * Ejecuta las migraciones y crea la primera empresa y super administrador
     */
    public function executeSetup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Datos de la Empresa
            'empresa_nombre' => 'required|string|max:150',
            'empresa_nit' => 'required|string|max:50',
            'empresa_email' => 'nullable|email|max:100',
            'empresa_telefono' => 'nullable|string|max:50',
            'empresa_direccion' => 'nullable|string|max:255',

            // Datos del Super Administrador
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|max:100',
            'admin_documento' => 'nullable|string|max:50',
            'admin_telefono' => 'nullable|string|max:50',
            'admin_password' => 'required|string|min:6|confirmed',
        ], [
            'empresa_nombre.required' => 'El nombre de la empresa es obligatorio.',
            'empresa_nit.required' => 'El NIT o documento tributario es obligatorio.',
            'admin_name.required' => 'El nombre del administrador es obligatorio.',
            'admin_email.required' => 'El correo electrónico es obligatorio.',
            'admin_email.email' => 'El correo electrónico no es válido.',
            'admin_password.required' => 'La contraseña del administrador es obligatoria.',
            'admin_password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'admin_password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        // 1. Ejecutar Migraciones
        $migrationResult = $this->installService->runMigrations();

        if (!$migrationResult['success']) {
            return back()->withInput()->with('error', $migrationResult['output']);
        }

        // 2. Crear Empresa y Administrador mediante Clean Architecture (ADO -> BLL -> DAL)
        $companyData = [
            'nombre' => $data['empresa_nombre'],
            'nit' => $data['empresa_nit'],
            'email' => $data['empresa_email'] ?? null,
            'telefono' => $data['empresa_telefono'] ?? null,
            'direccion' => $data['empresa_direccion'] ?? null,
        ];

        $adminData = [
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'documento' => $data['admin_documento'] ?? null,
            'telefono' => $data['admin_telefono'] ?? null,
            'password' => $data['admin_password'],
        ];

        $setupResult = $this->installService->setupInitialData($companyData, $adminData);

        if (!$setupResult['success']) {
            return back()->withInput()->with('error', $setupResult['message']);
        }

        return redirect()->route('install.finish');
    }

    /**
     * Paso 4: Pantalla Final de Éxito
     */
    public function finish(): View
    {
        return view('install.step4-finish');
    }
}
