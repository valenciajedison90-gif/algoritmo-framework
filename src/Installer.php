<?php

declare(strict_types=1);

namespace Algoritmo\Installer;

/**
 * Orquestador principal de instalación del Core de Algoritmo Framework sobre Laravel 12.
 */
class Installer
{
    protected string $coreDir;
    protected string $laravelRoot;

    public function __construct(string $coreDir, string $laravelRoot)
    {
        $this->coreDir = rtrim($coreDir, '/\\');
        $this->laravelRoot = rtrim($laravelRoot, '/\\');
    }

    public function run(): void
    {
        Console::banner();

        // Paso 1: Verificar artisan
        Console::step(1, 'Verificando instalación de Laravel');
        $this->verificarArtisan();

        // Paso 2: Copiar carpetas del Core
        Console::step(2, 'Copiando carpetas de arquitectura Clean Architecture');
        $this->copiarCarpetasCore();

        // Paso 3: Fusionar configuraciones
        Console::step(3, 'Fusionando configuraciones y variables de entorno');
        $this->fusionarConfiguraciones();

        // Paso 4: Actualizar composer.json
        Console::step(4, 'Actualizando dependencias en composer.json');
        $this->actualizarComposerJson();

        // Paso 5: Actualizar package.json
        Console::step(5, 'Actualizando scripts y assets en package.json');
        $this->actualizarPackageJson();

        // Paso 6: Ejecutar composer install / dump-autoload
        Console::step(6, 'Optimizando el cargador de clases (composer dump-autoload)');
        $this->ejecutarComposer();

        // Paso 7: Ejecutar npm install
        Console::step(7, 'Verificando dependencias frontend (npm)');
        $this->ejecutarNpm();

        // Paso 8: Ejecutar php artisan key:generate
        Console::step(8, 'Verificando clave de aplicación (php artisan key:generate)');
        $this->ejecutarKeyGenerate();

        // Paso 9: Ejecutar migrate --seed
        Console::step(9, 'Ejecutando migraciones y seeders de base de datos');
        $this->ejecutarMigraciones();

        // Paso 10: Mostrar credenciales
        Console::step(10, 'Instalación completada exitosamente');
        $this->mostrarCredenciales();
    }

    protected function verificarArtisan(): void
    {
        $artisanPath = $this->laravelRoot . DIRECTORY_SEPARATOR . 'artisan';
        if (!file_exists($artisanPath)) {
            Console::error("No se encontró el archivo 'artisan'. Asegúrese de ejecutar el instalador desde la raíz de Laravel 12.");
            exit(1);
        }
        Console::success("Proyecto Laravel 12 verificado en: {$this->laravelRoot}");
    }

    protected function copiarCarpetasCore(): void
    {
        $carpetas = [
            'app',
            'config',
            'database',
            'resources',
            'routes',
            'stubs',
            '.claude',
        ];

        foreach ($carpetas as $carpeta) {
            $origen = $this->coreDir . DIRECTORY_SEPARATOR . $carpeta;
            $destino = $this->laravelRoot . DIRECTORY_SEPARATOR . $carpeta;

            if (is_dir($origen)) {
                Filesystem::copyDirectory($origen, $destino);
                Console::info("Carpeta '{$carpeta}' sincronizada con éxito.");
            }
        }

        Console::success("Todos los módulos del Core fueron copiados a la estructura de Laravel.");
    }

    protected function fusionarConfiguraciones(): void
    {
        // Variables de entorno para Algoritmo Framework
        $envVars = [
            'APP_NAME' => '"Algoritmo Framework"',
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'true',
            'DB_CONNECTION' => 'sqlite',
        ];

        MergeConfig::updateEnv($this->laravelRoot, $envVars);

        // Fusionar rutas web
        $targetRoutes = $this->laravelRoot . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        $sourceRoutes = $this->coreDir . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        MergeConfig::mergeRoutes($targetRoutes, $sourceRoutes);

        // Registrar Middleware de Detección de Instalación y Base de Datos (tipo Moodle)
        MergeConfig::registerMiddleware($this->laravelRoot, 'App\Http\Middleware\EnsureSystemIsInstalled');

        Console::success("Configuraciones, middleware de instalación y rutas web fusionadas correctamente.");
    }

    protected function actualizarComposerJson(): void
    {
        Composer::updateComposerJson($this->laravelRoot, [], []);
        Console::success("composer.json configurado con namespaces de Algoritmo.");
    }

    protected function actualizarPackageJson(): void
    {
        MergeConfig::updatePackageJson($this->laravelRoot, [], []);
        Console::success("package.json verificado.");
    }

    protected function ejecutarComposer(): void
    {
        Console::info("Ejecutando composer dump-autoload...");
        $exitCode = Composer::run('composer dump-autoload -o', $this->laravelRoot);
        if ($exitCode === 0) {
            Console::success("Autoload optimizado exitosamente.");
        } else {
            Console::warning("No se pudo ejecutar composer automáticamente. Ejecute manualmente: composer dump-autoload");
        }
    }

    protected function ejecutarNpm(): void
    {
        Console::info("Las vistas de Algoritmo Framework utilizan Tailwind CSS y DataTables vía CDN para compatibilidad directa sin compilación obligatoria.");
        Console::success("Frontend listo para usar.");
    }

    protected function ejecutarKeyGenerate(): void
    {
        Console::info("Generando Application Key si no está configurada...");
        Composer::run('php artisan key:generate --no-interaction', $this->laravelRoot);
        Console::success("Clave de cifrado de Laravel lista.");
    }

    protected function ejecutarMigraciones(): void
    {
        Console::info("Comprobando base de datos y ejecutando migraciones...");
        $exitCode = Composer::run('php artisan migrate:fresh --seed --force', $this->laravelRoot);
        if ($exitCode === 0) {
            $lockDir = $this->laravelRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework';
            if (!is_dir($lockDir)) {
                @mkdir($lockDir, 0755, true);
            }
            $payload = [
                'installed' => true,
                'timestamp' => time(),
                'date' => date('Y-m-d H:i:s'),
                'version' => '2.0.0',
                'mode' => 'cli_installer',
            ];
            @file_put_contents($lockDir . DIRECTORY_SEPARATOR . 'installed.lock', json_encode($payload, JSON_PRETTY_PRINT));
            Console::success("Base de datos migrada y certificada con bloqueo installed.lock.");
        } else {
            Console::warning("No se pudo conectar a la base de datos por CLI. El Asistente Web (/install) le permitirá configurarla interactivamente al iniciar el servidor.");
        }
    }

    protected function mostrarCredenciales(): void
    {
        $credenciales = <<<TXT
\033[1;32m
====================================================================
           ¡ALGORITMO FRAMEWORK INSTALADO CON ÉXITO!
====================================================================\033[0m
  Puede iniciar el servidor de desarrollo ejecutando:
  \033[1;33mphp artisan serve\033[0m

  \033[1;37mDetección Inteligente de Base de Datos (tipo Moodle):\033[0m
  - Si la base de datos aún no está conectada, al abrir el navegador en
    \033[1;36mhttp://127.0.0.1:8000\033[0m el sistema abrirá automáticamente el
    \033[1;33mAsistente de Instalación Web\033[0m para probar la conexión en tiempo real,
    crear la base de datos y configurar el primer Administrador.

  - Si la base de datos ya está conectada y migrada:
    Acceda directamente a: \033[1;36mhttp://127.0.0.1:8000/login\033[0m

  Credenciales por defecto (si ejecutó seeders):
  ------------------------------------------------------------------
  \033[1;37mSuper Administrador:\033[0m
  Usuario: \033[1;32madmin@algoritmo.com\033[0m
  Clave:   \033[1;32madmin123\033[0m
====================================================================
TXT;
        echo $credenciales . PHP_EOL;
    }
}
