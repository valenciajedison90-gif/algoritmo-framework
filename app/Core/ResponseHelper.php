<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Helper estándar de respuestas de Algoritmo Framework.
 * Garantiza el formato uniforme obligatorio:
 * {
 *   "estado": bool,
 *   "mensaje": string,
 *   "datos": mixed
 * }
 */
class ResponseHelper
{
    /**
     * Construye un arreglo estandarizado de respuesta exitosa.
     */
    public static function exitoso(string $mensaje = "Operación exitosa.", mixed $datos = []): array
    {
        return [
            'estado' => true,
            'mensaje' => $mensaje,
            'datos' => $datos ?? new \stdClass(),
        ];
    }

    /**
     * Construye un arreglo estandarizado de respuesta con error.
     */
    public static function error(string $mensaje = "Ocurrió un error.", mixed $datos = []): array
    {
        return [
            'estado' => false,
            'mensaje' => $mensaje,
            'datos' => $datos ?? new \stdClass(),
        ];
    }

    /**
     * Retorna una respuesta JsonResponse exitosa con código HTTP.
     */
    public static function jsonExitoso(
        string $mensaje = "Operación exitosa.",
        mixed $datos = [],
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return response()->json(self::exitoso($mensaje, $datos), $statusCode);
    }

    /**
     * Retorna una respuesta JsonResponse con error y código HTTP.
     */
    public static function jsonError(
        string $mensaje = "Ocurrió un error en la solicitud.",
        mixed $datos = [],
        int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY
    ): JsonResponse {
        return response()->json(self::error($mensaje, $datos), $statusCode);
    }

    /**
     * Genera la estructura de respuesta requerida para DataTables con paginación server-side.
     */
    public static function dataTable(
        int $draw,
        int $recordsTotal,
        int $recordsFiltered,
        array $data,
        array $extra = []
    ): JsonResponse {
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'estado' => true,
            'mensaje' => 'Datos obtenidos correctamente.',
            'extra' => $extra,
        ]);
    }
}