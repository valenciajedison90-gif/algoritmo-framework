<?php

declare(strict_types=1);

namespace Algoritmo\Installer;

/**
 * Fusión inteligente de archivos de configuración, variables de entorno, middleware y package.json.
 */
class MergeConfig
{
    /**
     * Fusiona variables de entorno en el archivo .env de Laravel.
     */
    public static function updateEnv(string $laravelRoot, array $variables): bool
    {
        $envPath = rtrim($laravelRoot, '/\\') . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($envPath)) {
            $examplePath = rtrim($laravelRoot, '/\\') . DIRECTORY_SEPARATOR . '.env.example';
            if (file_exists($examplePath)) {
                copy($examplePath, $envPath);
            } else {
                return false;
            }
        }

        $envContent = (string) file_get_contents($envPath);

        foreach ($variables as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $line = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = (string) preg_replace($pattern, $line, $envContent);
            } else {
                $envContent .= PHP_EOL . $line;
            }
        }

        file_put_contents($envPath, $envContent);
        return true;
    }

    /**
     * Registra un middleware web global en bootstrap/app.php de Laravel 12.
     */
    public static function registerMiddleware(string $laravelRoot, string $middlewareClass): bool
    {
        $appPath = rtrim($laravelRoot, '/\\') . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
        if (!file_exists($appPath)) {
            return false;
        }

        $content = (string) file_get_contents($appPath);
        $cleanClass = ltrim($middlewareClass, '\\');

        if (str_contains($content, $cleanClass)) {
            return true;
        }

        $pattern = '/->withMiddleware\s*\(\s*function\s*\(\s*Middleware\s*\$middleware\s*\)\s*\{([^}]*)\}\s*\)/s';
        if (preg_match($pattern, $content, $matches)) {
            $inner = trim($matches[1]);
            $appendCode = "\n        \$middleware->web(append: [\n            \\{$cleanClass}::class,\n        ]);\n    ";

            if (!empty($inner) && $inner !== '//') {
                $appendCode = "\n        {$inner}\n        \$middleware->web(append: [\n            \\{$cleanClass}::class,\n        ]);\n    ";
            }

            $newSection = "->withMiddleware(function (Middleware \$middleware) {{$appendCode}})";
            $newContent = preg_replace($pattern, $newSection, $content);
            file_put_contents($appPath, $newContent);
            return true;
        }

        return false;
    }

    /**
     * Actualiza el archivo package.json con dependencias de UI si existen.
     */
    public static function updatePackageJson(string $laravelRoot, array $dependencies = [], array $devDependencies = []): bool
    {
        $pkgPath = rtrim($laravelRoot, '/\\') . DIRECTORY_SEPARATOR . 'package.json';
        if (!file_exists($pkgPath)) {
            return false;
        }

        $content = json_decode((string) file_get_contents($pkgPath), true);
        if (!is_array($content)) {
            return false;
        }

        if (!empty($dependencies)) {
            $content['dependencies'] = array_merge($content['dependencies'] ?? [], $dependencies);
        }

        if (!empty($devDependencies)) {
            $content['devDependencies'] = array_merge($content['devDependencies'] ?? [], $devDependencies);
        }

        file_put_contents(
            $pkgPath,
            json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        return true;
    }

    /**
     * Fusiona las rutas web asegurando que las rutas del Core queden integradas.
     */
    public static function mergeRoutes(string $targetWebRoutes, string $sourceRoutes): bool
    {
        if (!file_exists($targetWebRoutes) || !file_exists($sourceRoutes)) {
            return false;
        }

        $sourceContent = (string) file_get_contents($sourceRoutes);
        $targetContent = (string) file_get_contents($targetWebRoutes);

        // Si ya contiene el marcador de algoritmo, no duplicar
        if (str_contains($targetContent, 'Rutas Web de Algoritmo Framework')) {
            return true;
        }

        // Si es el archivo web.php básico de Laravel por defecto, podemos reemplazarlo directamente
        if (str_contains($targetContent, "return view('welcome');")) {
            file_put_contents($targetWebRoutes, $sourceContent);
            return true;
        }

        // En caso contrario, anexamos el contenido al final
        file_put_contents($targetWebRoutes, $targetContent . PHP_EOL . PHP_EOL . $sourceContent);
        return true;
    }
}
