<?php

declare(strict_types=1);

namespace App\DAL;

/**
 * Data Access Layer para operaciones específicas de Login y Autenticación.
 */
class LoginDAL extends BaseDAL
{
    /**
     * Busca el registro de usuario por correo electrónico incluyendo el estado de su empresa.
     */
    public function buscarParaAutenticacion(string $email): ?object
    {
        return $this->table('users as u')
            ->leftJoin('empresas as e', 'u.empresa_id', '=', 'e.id')
            ->select(
                'u.id',
                'u.empresa_id',
                'u.name',
                'u.email',
                'u.password',
                'u.rol',
                'u.activo as usuario_activo',
                'u.documento',
                'e.razon_social as empresa_nombre',
                'e.activo as empresa_activa'
            )
            ->where('u.email', $email)
            ->first();
    }

    /**
     * Actualiza la fecha y hora de último acceso y el remember_token si aplica.
     */
    public function actualizarAcceso(int $userId, ?string $rememberToken = null): void
    {
        $datos = ['ultimo_acceso' => now()];
        if ($rememberToken !== null) {
            $datos['remember_token'] = $rememberToken;
        }

        $this->update('users', ['id' => $userId], $datos);
    }
}