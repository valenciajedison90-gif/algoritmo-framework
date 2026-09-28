<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\ADO\ADOLogin;
use App\BLL\LoginBLL;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de Autenticación y Acceso al Sistema.
 * Regla arquitectónica: Nunca utiliza el Facade Auth directamente; delega toda la lógica a LoginBLL.
 */
class LoginController extends Controller
{
    protected LoginBLL $bll;

    public function __construct(LoginBLL $bll)
    {
        $this->bll = $bll;
    }

    /**
     * Muestra la vista de inicio de sesión.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Procesa la solicitud de autenticación.
     */
    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validated();
        $datos['ip'] = $request->ip();
        $datos['user_agent'] = $request->userAgent();

        // 1. Instanciar ADO
        $ado = ADOLogin::fromArray($datos);

        // 2. Invocar BLL
        $respuesta = $this->bll->autenticar($ado);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 200 : 401);
        }

        if (!$respuesta['estado']) {
            return back()->withInput($request->only('email', 'remember'))->with('error', $respuesta['mensaje']);
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Bienvenido al sistema.');
    }

    /**
     * Cierra la sesión activa.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->bll->cerrarSesion();
        return redirect()->route('login')->with('success', 'Sesión finalizada exitosamente.');
    }
}
