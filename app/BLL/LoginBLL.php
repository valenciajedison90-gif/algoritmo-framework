<?php

declare(strict_types=1);

namespace App\BLL;

use App\ADO\ADOLogin;
use App\Core\AuditService;
use App\Core\BusinessException;
use App\Core\LogService;
use App\Core\SecurityService;
use App\DAL\LoginDAL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Business Logic Layer (BLL) para el proceso de Login y Autenticación.
 * Ejecuta validaciones de credenciales, estado de usuario y empresa, y sesión.
 */
class LoginBLL extends BaseBLL
{
    protected LoginDAL $dal;
    protected SecurityService $security;

    public function __construct(
        LogService $logger,
        AuditService $auditor,
        LoginDAL $dal,
        SecurityService $security
    ) {
        parent::__construct($logger, $auditor);
        $this->dal = $dal;
        $this->security = $security;
    }

    /**
     * Autentica las credenciales suministradas en el ADOLogin.
     * Nunca se debe invocar Auth directamente desde el Controller.
     */
    public function autenticar(ADOLogin $ado): array
    {
        return $this->ejecutarTransaccion(function () use ($ado) {
            // 1. Buscar usuario en base de datos
            $usuario = $this->dal->buscarParaAutenticacion($ado->email);

            if (!$usuario) {
                $this->logger->warning("Intento de login fallido: usuario no encontrado", ['email' => $ado->email, 'ip' => $ado->ip]);
                $this->lanzarExcepcion("Las credenciales suministradas no coinciden con nuestros registros.", 401);
            }

            // 2. Verificar contraseña con Hash::check
            if (!$this->security->verifyPassword($ado->password, (string)$usuario->password)) {
                $this->logger->warning("Intento de login fallido: contraseña incorrecta", ['user_id' => $usuario->id, 'email' => $ado->email, 'ip' => $ado->ip]);
                $this->lanzarExcepcion("Las credenciales suministradas no coinciden con nuestros registros.", 401);
            }

            // 3. Validar si el usuario está activo
            if (empty($usuario->usuario_activo)) {
                $this->logger->warning("Intento de login bloqueado: usuario inactivo", ['user_id' => $usuario->id, 'email' => $ado->email]);
                $this->lanzarExcepcion("Su cuenta de usuario se encuentra inactiva. Contacte al administrador.", 403);
            }

            // 4. Validar si la empresa asociada está activa
            if ($usuario->empresa_id !== null && empty($usuario->empresa_activa)) {
                $this->logger->warning("Intento de login bloqueado: empresa inactiva", [
                    'user_id' => $usuario->id,
                    'empresa_id' => $usuario->empresa_id,
                    'empresa' => $usuario->empresa_nombre
                ]);
                $this->lanzarExcepcion("La empresa a la cual pertenece ('{$usuario->empresa_nombre}') se encuentra inactiva.", 403);
            }

            // 5. Iniciar sesión formalmente en Laravel
            Auth::loginUsingId($usuario->id, $ado->remember);

            // 6. Gestionar remember_token y último acceso
            $rememberToken = $ado->remember ? Str::random(60) : null;
            $this->dal->actualizarAcceso($usuario->id, $rememberToken);

            // 7. Registro de auditoría
            $this->auditor->registrar('AUTH', 'LOGIN', $usuario->id, null, null, "Inicio de sesión exitoso desde IP: {$ado->ip}");
            $this->logger->info("Inicio de sesión exitoso para usuario: {$usuario->email}");

            return $this->respuestaExitosa("Autenticación exitosa.", [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'empresa_id' => $usuario->empresa_id,
                'empresa_nombre' => $usuario->empresa_nombre,
            ]);
        });
    }

    /**
     * Cierra la sesión activa de forma segura.
     */
    public function cerrarSesion(): array
    {
        $userId = Auth::id();
        if ($userId) {
            $this->auditor->registrar('AUTH', 'LOGOUT', (int)$userId, null, null, "Cierre de sesión de usuario");
            $this->logger->info("Cierre de sesión para usuario ID: {$userId}");
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->respuestaExitosa("Sesión cerrada exitosamente.");
    }
}