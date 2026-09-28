<?php

declare(strict_types=1);

/**
 * Instalador Oficial de Algoritmo Framework para Laravel 12.
 * Ejecución desde la raíz de Laravel:
 * php install.php
 * o
 * php .algoritmo/install.php
 */

require_once __DIR__ . '/src/Console.php';
require_once __DIR__ . '/src/Filesystem.php';
require_once __DIR__ . '/src/Composer.php';
require_once __DIR__ . '/src/MergeConfig.php';
require_once __DIR__ . '/src/Installer.php';

use Algoritmo\Installer\Installer;

$coreDir = __DIR__;
$laravelRoot = getcwd();

// Si se ejecuta dentro de la carpeta del core (.algoritmo), la raíz de Laravel es el directorio superior si existe artisan allí
if (!file_exists($laravelRoot . DIRECTORY_SEPARATOR . 'artisan') && file_exists(dirname($laravelRoot) . DIRECTORY_SEPARATOR . 'artisan')) {
    $laravelRoot = dirname($laravelRoot);
}

$installer = new Installer($coreDir, $laravelRoot);
$installer->run();
