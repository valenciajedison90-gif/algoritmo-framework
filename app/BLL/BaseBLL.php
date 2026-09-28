<?php

declare(strict_types=1);

namespace App\BLL;

use App\Core\AuditService;
use App\Core\BusinessException;
use App\Core\LogService;
use App\Core\ResponseHelper;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Base Business Logic Layer (BLL) de Algoritmo Framework.
 * Orquesta transacciones atómicas, validaciones de negocio y respuestas unificadas.
 */
abstract class BaseBLL
{
    protected LogService $logger;
    protected AuditService $auditor;

    public function __construct(LogService $logger, AuditService $auditor)
    {
        $this->logger = $logger;
        $this->auditor = $auditor;
    }

    /**
     * Construye una respuesta estandarizada exitosa en formato obligatorio.
     */
    protected function respuestaExitosa(string $mensaje = "Operación exitosa.", mixed $datos = []): array
    {
        return ResponseHelper::exitoso($mensaje, $datos);
    }

    /**
     * Construye una respuesta estandarizada de error en formato obligatorio.
     */
    protected function respuestaError(string $mensaje = "Error en la operación.", mixed $datos = []): array
    {
        return ResponseHelper::error($mensaje, $datos);
    }

    /**
     * Lanza una excepción de negocio que interrumpe el flujo y revierte transacciones activas.
     *
     * @throws BusinessException
     */
    protected function lanzarExcepcion(string $mensaje, int $codigo = 422, array $errores = []): never
    {
        throw new BusinessException($mensaje, $codigo, $errores);
    }

    /**
     * Ejecuta una rutina de negocio dentro de una transacción atómica segura.
     * Si ocurre una excepción o error, realiza rollback automático y registra el log.
     */
    protected function ejecutarTransaccion(callable $callback): array
    {
        DB::beginTransaction();
        try {
            $resultado = $callback();
            DB::commit();

            if (is_array($resultado) && isset($resultado['estado'])) {
                return $resultado;
            }

            return $this->respuestaExitosa("Operación ejecutada correctamente.", $resultado);
        } catch (BusinessException $be) {
            DB::rollBack();
            $this->logger->warning("Regla de negocio violada: " . $be->getMessage(), [
                'codigo' => $be->getCode(),
                'errores' => $be->getErrores(),
            ]);

            return $this->respuestaError($be->getMessage(), [
                'codigo' => $be->getCode(),
                'errores' => $be->getErrores(),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->logger->error("Error crítico durante la transacción: " . $e->getMessage(), [
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->respuestaError("Ocurrió un error inesperado al procesar la solicitud.");
        }
    }
}