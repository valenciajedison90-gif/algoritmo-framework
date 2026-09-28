<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Facades\Auth;

/**
 * Servicio Central de Permisos y Autorización de Algoritmo Framework.
 */
class PermissionService
{
    public const ROL_SUPERADMIN = 'superadmin';
    public const ROL_ADMIN = 'admin';
    public const ROL_OPERADOR = 'operador';
    public const ROL_CONSULTA = 'consulta';

    /**
     * Valida si el usuario actual es Super Administrador del sistema.
     */
    public function esSuperAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return isset($user->rol) && strtolower((string)$user->rol) === self::ROL_SUPERADMIN;
    }

    /**
     * Valida si el usuario actual tiene permisos administrativos (SuperAdmin o Admin).
     */
    public function esAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $rol = strtolower((string)($user->rol ?? ''));
        return in_array($rol, [self::ROL_SUPERADMIN, self::ROL_ADMIN], true);
    }

    /**
     * Verifica si el usuario autenticado tiene acceso a un registro de una empresa específica.
     * Si es SuperAdmin puede acceder a cualquier empresa.
     * En caso contrario, su empresa_id debe coincidir exactamente.
     */
    public function validarAccesoEmpresa(int $empresaId): bool
    {
        if ($this->esSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user || !isset($user->empresa_id)) {
            return false;
        }

        return (int) $user->empresa_id === $empresaId;
    }

    /**
     * Evalúa si el rol actual tiene permiso para una acción determinada en un módulo.
     */
    public function puedeEjecutar(string $modulo, string $accion): bool
    {
        if ($this->esSuperAdmin()) {
            return true;
        }

        $user = Auth::user();
        if (!$user || empty($user->activo)) {
            return false;
        }

        $rol = strtolower((string)($user->rol ?? ''));

        // Reglas de negocio para roles por defecto
        if ($rol === self::ROL_ADMIN) {
            return true;
        }

        if ($rol === self::ROL_OPERADOR) {
            // Operador no puede eliminar empresas ni cambiar configuraciones globales
            if ($modulo === 'empresas' && in_array($accion, ['eliminar', 'destroy'], true)) {
                return false;
            }
            return true;
        }

        if ($rol === self::ROL_CONSULTA) {
            // Consulta sólo puede listar y ver detalles
            return in_array($accion, ['index', 'listar', 'show', 'ver', 'dataTable'], true);
        }

        return false;
    }
}
