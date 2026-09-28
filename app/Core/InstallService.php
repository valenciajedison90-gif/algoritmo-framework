<?php

declare(strict_types=1);

namespace App\Core;

use App\ADO\ADOEmpresa;
use App\ADO\ADOUsuario;
use App\BLL\EmpresaBLL;
use App\BLL\UsuarioBLL;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Throwable;

/**
 * Servicio Central de Instalación y Detección de Base de Datos
 *
 * Implementa el flujo guiado estilo Moodle para instalación desatendida,
 * diagnóstico del sistema, configuración dinámica de bases de datos y
 * creación del primer administrador mediante arquitectura limpia.
 */
class InstallService
{
    /**
     * Determina si el sistema se encuentra completamente instalado y operativo.
     */
    public function isInstalled(): bool
    {
        $lockFile = storage_path('framework/installed.lock');
        if (!file_exists($lockFile)) {
            return false;
        }

        try {
            DB::connection()->getPdo();

            if (!Schema::hasTable('users') || !Schema::hasTable('empresas')) {
                return false;
            }

            $userCount = DB::table('users')->count();
            return $userCount > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Realiza un diagnóstico completo de requisitos de servidor, extensiones y permisos.
     *
     * @return array{allPassed: bool, php: array, extensions: array, permissions: array}
     */
    public function checkRequirements(): array
    {
        $minPhpVersion = '8.2.0';
        $currentPhpVersion = PHP_VERSION;
        $phpPassed = version_compare($currentPhpVersion, $minPhpVersion, '>=');

        $requiredExtensions = [
            'pdo' => 'PDO (PHP Data Objects)',
            'mbstring' => 'Mbstring Multibyte String',
            'openssl' => 'OpenSSL Criptografía',
            'tokenizer' => 'Tokenizer Parser',
            'xml' => 'XML Parser',
            'ctype' => 'Ctype Checking',
            'json' => 'JSON Parser',
            'bcmath' => 'BCMath Arbitrary Precision',
            'curl' => 'cURL Client',
        ];

        $extensionResults = [];
        $extensionsPassed = true;
        foreach ($requiredExtensions as $ext => $name) {
            $loaded = extension_loaded($ext);
            $extensionResults[] = [
                'extension' => $ext,
                'name' => $name,
                'passed' => $loaded,
            ];
            if (!$loaded) {
                $extensionsPassed = false;
            }
        }

        // Detectar drivers de base de datos disponibles
        $drivers = [
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'pdo_pgsql' => extension_loaded('pdo_pgsql'),
            'pdo_sqlite' => extension_loaded('pdo_sqlite'),
            'pdo_sqlsrv' => extension_loaded('pdo_sqlsrv'),
        ];

        $paths = [
            'storage' => storage_path(),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            '.env' => file_exists(base_path('.env')) ? base_path('.env') : base_path(),
        ];

        $permissionResults = [];
        $permissionsPassed = true;
        foreach ($paths as $name => $path) {
            $isWritable = is_writable($path);
            $permissionResults[] = [
                'name' => $name,
                'path' => $path,
                'passed' => $isWritable,
            ];
            if (!$isWritable) {
                $permissionsPassed = false;
            }
        }

        $allPassed = $phpPassed && $extensionsPassed && $permissionsPassed;

        return [
            'allPassed' => $allPassed,
            'php' => [
                'required' => $minPhpVersion,
                'current' => $currentPhpVersion,
                'passed' => $phpPassed,
            ],
            'extensions' => $extensionResults,
            'drivers' => $drivers,
            'permissions' => $permissionResults,
        ];
    }

    /**
     * Prueba una conexión a base de datos de manera dinámica usando PDO.
     *
     * @param array<string, mixed> $config
     * @return array{success: bool, message: string, can_create_db?: bool}
     */
    public function testDatabaseConnection(array $config): array
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = (int) ($config['port'] ?? ($driver === 'pgsql' ? 5432 : 3306));
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if ($driver === 'sqlite') {
            try {
                $dbPath = $database ?: database_path('database.sqlite');
                if (!file_exists($dbPath)) {
                    $dir = dirname($dbPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    touch($dbPath);
                }
                new PDO("sqlite:{$dbPath}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);
                return [
                    'success' => true,
                    'message' => "Conexión SQLite verificada correctamente en {$dbPath}.",
                ];
            } catch (Throwable $e) {
                return [
                    'success' => false,
                    'message' => 'Error SQLite: ' . $e->getMessage(),
                ];
            }
        }

        // Intento 1: Conexión directa a la base de datos especificada
        try {
            $dsn = "{$driver}:host={$host};port={$port};dbname={$database}";
            new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            return [
                'success' => true,
                'message' => "¡Conexión exitosa! El servidor y la base de datos '{$database}' están accesibles.",
            ];
        } catch (Throwable $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Error 1049: Unknown database (MySQL)
            // Error 7 / SQLSTATE 3D000: database does not exist (PostgreSQL)
            if ($errorCode === 1049 || str_contains($errorMessage, 'Unknown database') || str_contains($errorMessage, 'does not exist')) {
                // Verificar si al menos podemos conectarnos al servidor sin especificar base de datos
                try {
                    $serverDsn = $driver === 'pgsql'
                        ? "pgsql:host={$host};port={$port};dbname=postgres"
                        : "mysql:host={$host};port={$port}";

                    new PDO($serverDsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 5,
                    ]);

                    return [
                        'success' => false,
                        'can_create_db' => true,
                        'message' => "La base de datos '{$database}' no existe en el servidor, pero las credenciales son válidas. Puedes marcar la opción para crearla automáticamente.",
                    ];
                } catch (Throwable) {
                    // Falló también al servidor base
                }
            }

            return [
                'success' => false,
                'message' => "No se pudo conectar a la base de datos: {$errorMessage}",
            ];
        }
    }

    /**
     * Intenta crear la base de datos en el servidor si no existe.
     *
     * @param array<string, mixed> $config
     * @return array{success: bool, message: string}
     */
    public function createDatabaseIfNotExists(array $config): array
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = (int) ($config['port'] ?? ($driver === 'pgsql' ? 5432 : 3306));
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if ($driver === 'sqlite') {
            $dbPath = $database ?: database_path('database.sqlite');
            if (!file_exists($dbPath)) {
                @touch($dbPath);
            }
            return ['success' => true, 'message' => 'Archivo SQLite verificado.'];
        }

        try {
            if ($driver === 'pgsql') {
                $pdo = new PDO("pgsql:host={$host};port={$port};dbname=postgres", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $cleanDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
                $pdo->exec("CREATE DATABASE \"{$cleanDb}\"");
            } else {
                $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $cleanDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cleanDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            return [
                'success' => true,
                'message' => "Base de datos '{$database}' creada con éxito.",
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'No se pudo crear la base de datos: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Actualiza el archivo .env con los nuevos parámetros de conexión y purga la configuración.
     *
     * @param array<string, mixed> $config
     */
    public function saveDatabaseConfiguration(array $config): bool
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = (string) ($config['port'] ?? ($driver === 'pgsql' ? '5432' : '3306'));
        $database = (string) ($config['database'] ?? 'algoritmo_db');
        $username = (string) ($config['username'] ?? 'root');
        $password = (string) ($config['password'] ?? '');

        $envUpdates = [
            'DB_CONNECTION' => $driver,
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ];

        $saved = $this->updateEnvFile($envUpdates);

        // Actualizar la configuración activa en memoria para el ciclo de vida actual
        config([
            'database.default' => $driver,
            "database.connections.{$driver}.host" => $host,
            "database.connections.{$driver}.port" => (int) $port,
            "database.connections.{$driver}.database" => $database,
            "database.connections.{$driver}.username" => $username,
            "database.connections.{$driver}.password" => $password,
        ]);

        try {
            DB::purge($driver);
            DB::reconnect($driver);
        } catch (Throwable) {
            // Continúa para permitir ejecución subsecuente
        }

        return $saved;
    }

    /**
     * Ejecuta las migraciones de Laravel para crear la estructura de base de datos.
     *
     * @return array{success: bool, output: string}
     */
    public function runMigrations(): array
    {
        try {
            Artisan::call('migrate:fresh', [
                '--force' => true,
            ]);

            return [
                'success' => true,
                'output' => Artisan::output(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'output' => 'Error ejecutando migraciones: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Inicializa la primera empresa y el usuario Super Administrador
     * utilizando estrictamente la arquitectura limpia (ADO -> BLL -> DAL).
     *
     * @param array<string, mixed> $companyData
     * @param array<string, mixed> $adminData
     * @return array{success: bool, message: string, empresa_id?: int, usuario_id?: int}
     */
    public function setupInitialData(array $companyData, array $adminData): array
    {
        try {
            // 1. Crear Empresa Principal mediante ADO y EmpresaBLL
            $empresaAdo = new ADOEmpresa(
                nombre: (string) ($companyData['nombre'] ?? 'Mi Empresa Principal'),
                nit: (string) ($companyData['nit'] ?? '900000000-1'),
                email: !empty($companyData['email']) ? (string) $companyData['email'] : null,
                telefono: !empty($companyData['telefono']) ? (string) $companyData['telefono'] : null,
                direccion: !empty($companyData['direccion']) ? (string) $companyData['direccion'] : null,
                activo: true
            );

            $empresaBll = new EmpresaBLL();
            $empresaResponse = $empresaBll->crear($empresaAdo);

            if (!$empresaResponse['estado']) {
                return [
                    'success' => false,
                    'message' => 'Error al registrar la empresa: ' . $empresaResponse['mensaje'],
                ];
            }

            $empresaId = (int) $empresaResponse['datos']['id'];

            // 2. Crear Super Administrador mediante ADO y UsuarioBLL
            $usuarioAdo = new ADOUsuario(
                empresaId: $empresaId,
                name: (string) ($adminData['name'] ?? 'Super Administrador'),
                email: (string) ($adminData['email'] ?? 'admin@misistema.com'),
                password: (string) ($adminData['password'] ?? 'admin123'),
                documento: !empty($adminData['documento']) ? (string) $adminData['documento'] : null,
                telefono: !empty($adminData['telefono']) ? (string) $adminData['telefono'] : null,
                rol: 'admin',
                activo: true
            );

            $usuarioBll = new UsuarioBLL();
            $usuarioResponse = $usuarioBll->crear($usuarioAdo);

            if (!$usuarioResponse['estado']) {
                return [
                    'success' => false,
                    'message' => 'Error al registrar el administrador: ' . $usuarioResponse['mensaje'],
                ];
            }

            $usuarioId = (int) $usuarioResponse['datos']['id'];

            // 3. Crear archivo de bloqueo de instalación
            $this->markAsInstalled([
                'installed_at' => date('Y-m-d H:i:s'),
                'framework_version' => '2.0.0',
                'admin_email' => $adminData['email'],
                'empresa_nombre' => $companyData['nombre'],
            ]);

            return [
                'success' => true,
                'message' => 'Instalación completada exitosamente.',
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Excepción durante la inicialización: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Escribe el archivo de bloqueo que certifica que el sistema está completamente instalado.
     *
     * @param array<string, mixed> $data
     */
    public function markAsInstalled(array $data = []): void
    {
        $dir = storage_path('framework');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $lockPath = storage_path('framework/installed.lock');
        $payload = array_merge([
            'installed' => true,
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'),
            'version' => '2.0.0',
        ], $data);

        file_put_contents($lockPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Actualiza o inserta variables en el archivo .env de forma segura.
     *
     * @param array<string, string> $values
     */
    private function updateEnvFile(array $values): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
            } else {
                touch($envPath);
            }
        }

        $content = file_get_contents($envPath);
        if ($content === false) {
            return false;
        }

        foreach ($values as $key => $value) {
            // Entrecomillar si contiene espacios, almohadillas o signos de dólar
            $formattedValue = (str_contains($value, ' ') || str_contains($value, '#') || str_contains($value, '$'))
                ? '"' . str_replace('"', '\"', $value) . '"'
                : $value;

            // Reemplazar clave si ya existe o está comentada, o anexarla
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$formattedValue}", $content);
            } elseif (preg_match("/^#\s*{$key}=.*/m", $content)) {
                $content = preg_replace("/^#\s*{$key}=.*/m", "{$key}={$formattedValue}", $content);
            } else {
                $content .= "\n{$key}={$formattedValue}";
            }
        }

        return file_put_contents($envPath, $content) !== false;
    }
}
