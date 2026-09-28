<?php

declare(strict_types=1);

namespace Algoritmo\Installer;

/**
 * Gestor de dependencias y scripts de Composer.
 */
class Composer
{
    public static function updateComposerJson(string $laravelRoot, array $packages = [], array $devPackages = []): bool
    {
        $composerPath = rtrim($laravelRoot, '/\\') . DIRECTORY_SEPARATOR . 'composer.json';
        if (!file_exists($composerPath)) {
            return false;
        }

        $content = json_decode((string) file_get_contents($composerPath), true);
        if (!is_array($content)) {
            return false;
        }

        // Agregar paquetes a require
        if (!empty($packages)) {
            $content['require'] = array_merge($content['require'] ?? [], $packages);
        }

        // Agregar paquetes a require-dev
        if (!empty($devPackages)) {
            $content['require-dev'] = array_merge($content['require-dev'] ?? [], $devPackages);
        }

        // Asegurar que App\\ esté mapeado correctamente
        if (!isset($content['autoload']['psr-4']['App\\'])) {
            $content['autoload']['psr-4']['App\\'] = 'app/';
        }

        file_put_contents(
            $composerPath,
            json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        return true;
    }

    public static function run(string $command, string $cwd): int
    {
        $descriptor = [
            0 => ['pipe', 'r'],
            1 => STDOUT,
            2 => STDERR,
        ];

        $process = proc_open($command, $descriptor, $pipes, $cwd);
        if (is_resource($process)) {
            fclose($pipes[0]);
            return proc_close($process);
        }

        return 1;
    }
}
