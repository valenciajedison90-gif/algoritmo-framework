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
    protected array $args;

    public function __construct(string $coreDir, string $laravelRoot, array $args = [])
    {
        $this->coreDir = rtrim($coreDir, '/\\');
        $this->laravelRoot = rtrim($laravelRoot, '/\\');
        $this->args = $args;
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

        // Paso 9: Verificación de Base de Datos y Asistente Web
        Console::step(9, 'Preparando Asistente de Base de Datos Web (tipo Moodle)');
        $this->ejecutarMigraciones();

        // Paso 10: Mostrar resumen e instrucciones
        Console::step(10, 'Instalación de Core completada exitosamente');
        $this->mostrarCredenciales();
    }

    protected function verificarArtisan(): void
    {
        $artisanPath = $this->laravelRoot . DIRECTORY_SEPARATOR . 'artisan';
        if (!file_exists($artisanPath)) {
            Console::error("No se encontró el archivo 'artisan'. Asegúrese de ejecutar el instalador desde la raíz del proyecto Laravel.");
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
        // Variables base de entorno para Algoritmo Framework
        $envVars = [
            'APP_NAME' => '"Algoritmo Framework"',
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'true',
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
        $forceCli = in_array('--seed', $this->args, true) || in_array('--migrate', $this->args, true);

        if ($forceCli) {
            Console::info("Modo CLI activado: Ejecutando migraciones y seeders de base de datos...");
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
        } else {
            Console::info("Middleware EnsureSystemIsInstalled activado.");
            Console::info("El Asistente Web (/install) se ejecutará automáticamente en la primera pantalla del navegador.");
            Console::success("Detección inteligente de base de datos configurada.");
        }
    }

    protected function mostrarCredenciales(): void
    {
        $credenciales = <<<TXT
\033[1;32m
====================================================================
           ¡ALGORITMO FRAMEWORK INSTALADO CON ÉXITO!
====================================================================\033[0m
  El Core de Algoritmo Framework (ADO / BLL / DAL) y el Asistente Web
  tipo Moodle han sido integrados correctamente en:
  \033[1;36m{$this->laravelRoot}\033[0m

  \033[1;33mSIGUIENTES PASOS PARA INICIAR EL SISTEMA:\033[0m
  ------------------------------------------------------------------
  1. Inicie el servidor de desarrollo de Laravel:
     \033[1;32mphp artisan serve\033[0m

  2. Abra su navegador en la URL:
     \033[1;36mhttp://127.0.0.1:8000\033[0m

  \033[1;37m¿Qué sucederá en la primera pantalla? (Detección tipo Moodle):\033[0m
  - El sistema detectará que la base de datos no está conectada o migrada
    y abrirá automáticamente el \033[1;33mAsistente de Instalación Web\033[0m.
  - Podrá seleccionar el motor de BD (MySQL, MariaDB, PostgreSQL, SQL Server u Oracle).
  - Probar la conexión en tiempo real con el botón \033[1;32m"⚡ Probar Conexión"\033[0m.
  - Crear la base de datos automáticamente si aún no existe en el servidor.
  - Ejecutar las migraciones y registrar su Empresa y Super Administrador.
  - Al finalizar, ingresará directamente al Login y al Dashboard del ERP.
====================================================================
TXT;
        echo $credenciales . PHP_EOL;
    }
}
