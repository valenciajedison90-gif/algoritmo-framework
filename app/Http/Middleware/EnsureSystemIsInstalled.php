<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\InstallService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Detección de Instalación y Conexión de Base de Datos
 *
 * Intercepta todas las peticiones web:
 * - Si el sistema NO está instalado o no tiene base de datos operativa,
 *   redirige automáticamente al Asistente de Instalación (/install) estilo Moodle.
 * - Si el sistema YA está instalado e intentan ingresar a /install,
 *   redirige al login o dashboard para proteger el sistema.
 */
class EnsureSystemIsInstalled
{
    public function __construct(
        protected InstallService $installService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = $this->installService->isInstalled();
        $isInstallRoute = $request->is('install') || $request->is('install/*');

        // Si NO está instalado
        if (!$isInstalled) {
            // Permitir rutas del instalador y recursos estáticos
            if ($isInstallRoute || $request->is('build/*') || $request->is('assets/*')) {
                return $next($request);
            }

            // Redirigir cualquier otra pantalla al Asistente de Instalación
            return redirect()->route('install.index');
        }

        // Si YA está instalado y pretenden entrar al instalador, bloquear
        if ($isInstallRoute) {
            if (auth()->check()) {
                return redirect()->route('dashboard');
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
