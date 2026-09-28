<?php

declare(strict_types=1);

namespace App\Core\Helpers;

use Carbon\Carbon;

/**
 * Helper de formateo y utilidades generales.
 * Moderniza y reemplaza funciones.php del sistema legado.
 */
class FormatHelper
{
    /**
     * Formatea fecha para visualización en formato d/m/Y.
     */
    public static function fechaVisualizacion(?string $fecha): string
    {
        if (empty($fecha)) {
            return '-';
        }
        return Carbon::parse($fecha)->format('d/m/Y');
    }

    /**
     * Formatea fecha y hora para visualización en formato d/m/Y H:i:s.
     */
    public static function fechaHoraVisualizacion(?string $fecha): string
    {
        if (empty($fecha)) {
            return '-';
        }
        return Carbon::parse($fecha)->format('d/m/Y H:i:s');
    }

    /**
     * Convierte fecha ingresada por el usuario (d/m/Y) al formato de base de datos (Y-m-d).
     */
    public static function fechaABaseDatos(?string $fecha): ?string
    {
        if (empty($fecha)) {
            return null;
        }
        return Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
    }

    /**
     * Formatea un valor numérico como moneda colombiana (COP / estándar $ 1.000.000).
     */
    public static function formatoMoneda(float|int|null $valor, int $decimales = 0): string
    {
        if ($valor === null) {
            return '$ 0';
        }
        return '$ ' . number_format((float)$valor, $decimales, ',', '.');
    }

    /**
     * Limpia un documento de identidad o NIT dejando solo dígitos.
     */
    public static function limpiarDocumento(?string $documento): string
    {
        if (empty($documento)) {
            return '';
        }
        return (string) preg_replace('/\D/', '', $documento);
    }
}
