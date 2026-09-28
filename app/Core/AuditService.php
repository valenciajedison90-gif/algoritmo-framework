<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Throwable;

/**
 * Servicio de Auditoría y Trazabilidad de Algoritmo Framework.
 * Registra cada operación sensible en la base de datos de manera atómica.
 */
class AuditService
{
    /**
     * Registra un evento en la tabla de auditoría.
     */
    public function registrar(
        string $modulo,
        string $accion,
        ?int $registroId = null,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $detalles = null
    ): bool {
        try {
            $userId = Auth::id() ? (int) Auth::id() : null;
            $user = Auth::user();
            $empresaId = ($user && isset($user->empresa_id)) ? (int) $user->empresa_id : null;

            DB::table('audits')->insert([
                'empresa_id' => $empresaId,
                'user_id' => $userId,
                'modulo' => strtoupper($modulo),
                'accion' => strtoupper($accion),
                'registro_id' => $registroId,
                'valores_anteriores' => $valoresAnteriores ? json_encode($valoresAnteriores, JSON_UNESCAPED_UNICODE) : null,
                'valores_nuevos' => $valoresNuevos ? json_encode($valoresNuevos, JSON_UNESCAPED_UNICODE) : null,
                'detalles' => $detalles,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => substr(Request::userAgent() ?? 'CLI/Unknown', 0, 500),
                'created_at' => now(),
            ]);

            return true;
        } catch (Throwable $e) {
            // Se registra el fallo en el log del sistema para no interrumpir el flujo crítico si la auditoría falla
            app(LogService::class)->warning("Fallo al escribir en auditoría: " . $e->getMessage(), [
                'modulo' => $modulo,
                'accion' => $accion,
                'registro_id' => $registroId,
            ]);
            return false;
        }
    }

    /**
     * Consulta el historial de auditoría de un registro o módulo con filtros.
     */
    public function consultarHistorial(string $modulo, ?int $registroId = null, int $limit = 50): array
    {
        $query = DB::table('audits')
            ->where('modulo', strtoupper($modulo))
            ->orderByDesc('id')
            ->limit($limit);

        if ($registroId !== null) {
            $query->where('registro_id', $registroId);
        }

        return $query->get()->toArray();
    }
}
