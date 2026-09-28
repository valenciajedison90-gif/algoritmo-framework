<?php

declare(strict_types=1);

namespace Algoritmo\Installer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Manejador de operaciones en el sistema de archivos para la instalación del Core.
 */
class Filesystem
{
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    public static function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public static function copyFile(string $source, string $destination): bool
    {
        self::ensureDirectoryExists(dirname($destination));
        return copy($source, $destination);
    }

    public static function copyDirectory(string $source, string $destination, array $ignore = []): void
    {
        if (!is_dir($source)) {
            return;
        }

        self::ensureDirectoryExists($destination);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($source) + 1);

            // Verificar si el archivo o carpeta debe ser ignorado
            foreach ($ignore as $pattern) {
                if (fnmatch($pattern, $relativePath)) {
                    continue 2;
                }
            }

            $target = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                self::ensureDirectoryExists($target);
            } else {
                self::ensureDirectoryExists(dirname($target));
                copy($item->getPathname(), $target);
            }
        }
    }
}
