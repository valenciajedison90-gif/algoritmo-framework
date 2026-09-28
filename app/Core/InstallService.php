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
 * Servicio Central de Instalación y Conexión Multi-Base de Datos
 *
 * Soporta motores empresariales cliente-servidor:
 * - MySQL
 * - MariaDB
 * - PostgreSQL
 * - Microsoft SQL Server (SQLSRV)
 * - Oracle Database (OCI)
 */
class InstallService
{
    /**
     * Puertos estándar por motor de base de datos
     */
    public const DEFAULT_PORTS = [
        'mysql' => 3306,
        'mariadb' => 3306,
        'pgsql' => 5432,
        'sqlsrv' => 1433,
        'oracle' => 1521,
    ];

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
     * @return array{allPassed: bool, php: array, extensions: array, drivers: array, permissions: array}
     */
    public function checkRequirements(): array
    {
        $minPhpVersion = '8.2.0';
        $currentPhpVersion = PHP_VERSION;
        $phpPassed = version_compare($currentPhpVersion, $minPhpVersion, '>=');

        $requiredExtensions = [
            'pdo' => 'PDO (PHP Data Objects Core)',
            'mbstring' => 'Mbstring Multibyte String',
            'openssl' => 'OpenSSL Criptografía Segura',
            'tokenizer' => 'Tokenizer PHP Parser',
            'xml' => 'XML / DOM Parser',
            'ctype' => 'Ctype Checking',
            'json' => 'JSON Parser & Encoder',
            'bcmath' => 'BCMath Precisión Arbitraria',
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

        // Detectar controladores de bases de datos empresariales
        $drivers = [
            'pdo_mysql' => [
                'name' => 'MySQL / MariaDB',
                'loaded' => extension_loaded('pdo_mysql'),
            ],
            'pdo_pgsql' => [
                'name' => 'PostgreSQL',
                'loaded' => extension_loaded('pdo_pgsql'),
            ],
            'pdo_sqlsrv' => [
                'name' => 'Microsoft SQL Server',
                'loaded' => extension_loaded('pdo_sqlsrv') || extension_loaded('sqlsrv'),
            ],
            'pdo_oci' => [
                'name' => 'Oracle Database',
                'loaded' => extension_loaded('pdo_oci') || extension_loaded('oci8'),
            ],
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
     * Prueba una conexión a base de datos de manera dinámica usando PDO nativo.
     *
     * @param array<string, mixed> $config
     * @return array{success: bool, message: string, can_create_db?: bool}
     */
    public function testDatabaseConnection(array $config): array
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = (int) ($config['port'] ?? (self::DEFAULT_PORTS[$driver] ?? 3306));
        $database = trim((string) ($config['database'] ?? ''));
        $username = trim((string) ($config['username'] ?? ''));
        $password = (string) ($config['password'] ?? '');

        if (empty($database)) {
            return [
                'success' => false,
                'message' => 'El nombre de la base de datos o servicio es obligatorio.',
            ];
        }

        // Intento 1: Conexión directa a la base de datos especificada
        try {
            $dsn = $this->buildDsn($driver, $host, $port, $database);
            new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            return [
                'success' => true,
                'message' => "¡Conexión exitosa! El servidor de base de datos ({$driver}) y la base de datos '{$database}' están accesibles.",
            ];
        } catch (Throwable $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Detectar si el servidor respondió pero la base de datos no existe
            $isMissingDb = (
                $errorCode === 1049 ||
                str_contains($errorMessage, 'Unknown database') ||
                str_contains($errorMessage, 'does not exist') ||
                str_contains($errorMessage, 'Cannot open database')
            );

            if ($isMissingDb && in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlsrv'], true)) {
                try {
                    $serverDsn = $this->buildServerDsn($driver, $host, $port);

                    new PDO($serverDsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 5,
                    ]);

                    return [
                        'success' => false,
                        'can_create_db' => true,
                        'message' => "El servidor ({$driver}) respondió y las credenciales son correctas, pero la base de datos '{$database}' no existe. Puedes marcar la casilla para crearla automáticamente.",
                    ];
                } catch (Throwable) {
                    // Falló al conectar al servidor base
                }
            }

            return [
                'success' => false,
                'message' => "Fallo de conexión ({$driver}): {$errorMessage}",
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
        $port = (int) ($config['port'] ?? (self::DEFAULT_PORTS[$driver] ?? 3306));
        $database = trim((string) ($config['database'] ?? ''));
        $username = trim((string) ($config['username'] ?? ''));
        $password = (string) ($config['password'] ?? '');

        $cleanDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        if (empty($cleanDb)) {
            return ['success' => false, 'message' => 'Nombre de base de datos inválido.'];
        }

        try {
            if ($driver === 'pgsql') {
                $pdo = new PDO("pgsql:host={$host};port={$port};dbname=postgres", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $pdo->exec("CREATE DATABASE \"{$cleanDb}\"");
            } elseif ($driver === 'sqlsrv') {
                $pdo = new PDO("sqlsrv:Server={$host},{$port};Database=master", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $pdo->exec("CREATE DATABASE [{$cleanDb}]");
            } elseif ($driver === 'oracle') {
                return [
                    'success' => false,
                    'message' => 'En Oracle la base de datos o PDB debe crearse previamente por el DBA del sistema.',
                ];
            } else {
                // MySQL y MariaDB
                $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cleanDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            return [
                'success' => true,
                'message' => "Base de datos '{$cleanDb}' creada con éxito en el servidor.",
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'No se pudo crear la base de datos automáticamente: ' . $e->getMessage(),
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
        $port = (string) ($config['port'] ?? (self::DEFAULT_PORTS[$driver] ?? '3306'));
        $database = (string) ($config['database'] ?? 'algoritmo_db');
        $username = (string) ($config['username'] ?? 'root');
        $password = (string) ($config['password'] ?? '');

        // En Laravel MariaDB puede operar con driver 'mariadb' o 'mysql'
        $connectionName = $driver;

        $envUpdates = [
            'DB_CONNECTION' => $connectionName,
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ];

        $saved = $this->updateEnvFile($envUpdates);

        // Actualizar la configuración activa en memoria para el ciclo de vida actual
        config([
            'database.default' => $connectionName,
            "database.connections.{$connectionName}.host" => $host,
            "database.connections.{$connectionName}.port" => (int) $port,
            "database.connections.{$connectionName}.database" => $database,
            "database.connections.{$connectionName}.username" => $username,
            "database.connections.{$connectionName}.password" => $password,
        ]);

        try {
            DB::purge($connectionName);
            DB::reconnect($connectionName);
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
     * Construye la cadena DSN específica según el motor de base de datos
     */
    private function buildDsn(string $driver, string $host, int $port, string $database): string
    {
        return match ($driver) {
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$database}",
            'sqlsrv' => "sqlsrv:Server={$host},{$port};Database={$database}",
            'oracle' => "oci:dbname=//{$host}:{$port}/{$database};charset=AL32UTF8",
            default => "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        };
    }

    /**
     * Construye la cadena DSN del servidor base sin base de datos específica
     */
    private function buildServerDsn(string $driver, string $host, int $port): string
    {
        return match ($driver) {
            'pgsql' => "pgsql:host={$host};port={$port};dbname=postgres",
            'sqlsrv' => "sqlsrv:Server={$host},{$port};Database=master",
            default => "mysql:host={$host};port={$port}",
        };
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
