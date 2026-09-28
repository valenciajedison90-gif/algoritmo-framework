<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Servicio Central de Registro y Logging de Algoritmo Framework.
 * Enriquecimiento contextual automático para auditoría y depuración.
 */
class LogService
{
    /**
     * Enriquece el contexto del log con datos del usuario y la petición.
     */
    protected function enriquecerContexto(array $contexto = []): array
    {
        $userId = Auth::id();
        $user = Auth::user();
        $empresaId = ($user && isset($user->empresa_id)) ? $user->empresa_id : null;

        return array_merge([
            'user_id' => $userId,
            'empresa_id' => $empresaId,
            'ip' => Request::ip() ?? 'CLI',
            'metodo' => Request::method() ?? 'N/A',
            'url' => Request::fullUrl() ?? 'N/A',
        ], $contexto);
    }

    public function emergency(string $mensaje, array $contexto = []): void
    {
        Log::emergency($mensaje, $this->enriquecerContexto($contexto));
    }

    public function alert(string $mensaje, array $contexto = []): void
    {
        Log::alert($mensaje, $this->enriquecerContexto($contexto));
    }

    public function critical(string $mensaje, array $contexto = []): void
    {
        Log::critical($mensaje, $this->enriquecerContexto($contexto));
    }

    public function error(string $mensaje, array $contexto = []): void
    {
        Log::error($mensaje, $this->enriquecerContexto($contexto));
    }

    public function warning(string $mensaje, array $contexto = []): void
    {
        Log::warning($mensaje, $this->enriquecerContexto($contexto));
    }

    public function notice(string $mensaje, array $contexto = []): void
    {
        Log::notice($mensaje, $this->enriquecerContexto($contexto));
    }

    public function info(string $mensaje, array $contexto = []): void
    {
        Log::info($mensaje, $this->enriquecerContexto($contexto));
    }

    public function debug(string $mensaje, array $contexto = []): void
    {
        Log::debug($mensaje, $this->enriquecerContexto($contexto));
    }
}
