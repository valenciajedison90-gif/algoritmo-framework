<?php

declare(strict_types=1);

namespace Algoritmo\Installer;

/**
 * Utilidad de Consola y salida con colores ANSI para el instalador de Algoritmo Framework.
 */
class Console
{
    public static function banner(): void
    {
        $banner = <<<TXT
\033[1;36m
   ___   _                      _  _                      
  / _ \ | |                    (_)| |                     
 / /_\ \| | __ _   ___   _ __  _ | |_  _ __ ___    ___   
 |  _  || |/ _` | / _ \ | '__|| || __|| '_ ` _ \  / _ \  
 | | | || || (_| || (_) || |   | || |_ | | | | | || (_) | 
 \_| |_/|_| \__, | \___/ |_|   |_| \__||_| |_| |_| \___/  
             __/ |   F R A M E W O R K   C O R E          
            |___/    Migración Clean Architecture Laravel 12
\033[0m
TXT;
        echo $banner . PHP_EOL;
    }

    public static function line(string $message = ''): void
    {
        echo $message . PHP_EOL;
    }

    public static function info(string $message): void
    {
        echo "\033[0;34mℹ [INFO]\033[0m {$message}" . PHP_EOL;
    }

    public static function success(string $message): void
    {
        echo "\033[0;32m✔ [EXITO]\033[0m {$message}" . PHP_EOL;
    }

    public static function warning(string $message): void
    {
        echo "\033[0;33m⚠ [AVISO]\033[0m {$message}" . PHP_EOL;
    }

    public static function error(string $message): void
    {
        echo "\033[0;31m✖ [ERROR]\033[0m {$message}" . PHP_EOL;
    }

    public static function step(int $number, string $message): void
    {
        echo PHP_EOL . "\033[1;35m==> Paso {$number}/10:\033[0m \033[1;37m{$message}\033[0m" . PHP_EOL;
    }
}
