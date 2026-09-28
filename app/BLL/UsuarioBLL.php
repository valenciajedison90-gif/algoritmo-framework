<?php

declare(strict_types=1);

namespace App\BLL;

use App\ADO\ADOUsuario;
use App\Core\AuditService;
use App\Core\BusinessException;
use App\Core\LogService;
use App\Core\SecurityService;
use App\DAL\EmpresaDAL;
use App\DAL\UsuarioDAL;

/**
 * Business Logic Layer (BLL) para la administración de Usuarios del Sistema.
 */
class UsuarioBLL extends BaseBLL
{
    protected UsuarioDAL $dal;
    protected EmpresaDAL $empresaDal;
    protected SecurityService $security;

    public function __construct(
        LogService $logger,
        AuditService $auditor,
        UsuarioDAL $dal,
        EmpresaDAL $empresaDal,
        SecurityService $security
    ) {
        parent::__construct($logger, $auditor);
        $this->dal = $dal;
        $this->empresaDal = $empresaDal;
        $this->security = $security;
    }

    public function obtener(int $id): array
    {
        $usuario = $this->dal->obtenerPorId($id);
        if (!$usuario) {
            return $this->respuestaError("El usuario solicitado no existe.");
        }
        return $this->respuestaExitosa("Usuario obtenido correctamente.", $usuario);
    }

    public function listar(array $filtros = []): array
    {
        $usuarios = $this->dal->listar($filtros);
        return $this->respuestaExitosa("Listado de usuarios obtenido correctamente.", $usuarios);
    }

    public function dataTable(array $params): array
    {
        return $this->dal->dataTable($params);
    }

    public function guardar(ADOUsuario $ado, ?string $passwordPlano = null): array
    {
        return $this->ejecutarTransaccion(function () use ($ado, $passwordPlano) {
            // Regla 1: Validar unicidad del correo electrónico
            $usuarioConEmail = $this->dal->obtenerPorEmail($ado->email);
            if ($usuarioConEmail && $usuarioConEmail->id !== $ado->id) {
                $this->lanzarExcepcion(
                    "El correo '{$ado->email}' ya se encuentra registrado por otro usuario.",
                    422,
                    ['email' => 'Correo ya en uso']
                );
            }

            // Regla 2: Validar documento si está presente
            if (!empty($ado->documento)) {
                $usuarioConDoc = $this->dal->obtenerPorDocumento($ado->documento);
                if ($usuarioConDoc && $usuarioConDoc->id !== $ado->id) {
                    $this->lanzarExcepcion(
                        "El documento '{$ado->documento}' ya se encuentra asignado a otro usuario.",
                        422,
                        ['documento' => 'Documento ya en uso']
                    );
                }
            }

            // Regla 3: Validar que la empresa exista y esté activa
            if ($ado->empresa_id) {
                $empresa = $this->empresaDal->obtenerPorId($ado->empresa_id);
                if (!$empresa) {
                    $this->lanzarExcepcion("La empresa asociada no existe.", 422);
                }
                if (!$empresa->activo) {
                    $this->lanzarExcepcion("No se pueden asociar usuarios a una empresa inactiva.", 422);
                }
            }

            if ($ado->id === null || $ado->id === 0) {
                // Creación de usuario
                if (empty($passwordPlano)) {
                    $this->lanzarExcepcion("La contraseña es requerida para nuevos usuarios.", 422);
                }

                $ado->password = $this->security->hashPassword($passwordPlano);
                $nuevoId = $this->dal->crear($ado);
                $ado->id = $nuevoId;

                $this->auditor->registrar('USUARIOS', 'CREAR', $nuevoId, null, $ado->toArray(), "Usuario creado");
                $this->logger->info("Usuario creado con ID: {$nuevoId}");

                return $this->respuestaExitosa("Usuario creado exitosamente.", $ado);
            }

            // Actualización de usuario existente
            $anterior = $this->dal->obtenerPorId($ado->id);
            if (!$anterior) {
                $this->lanzarExcepcion("El usuario a actualizar no existe.", 404);
            }

            $this->dal->actualizar($ado);

            // Si se suministró una nueva contraseña en la edición
            if (!empty($passwordPlano)) {
                $hash = $this->security->hashPassword($passwordPlano);
                $this->dal->actualizarPassword($ado->id, $hash);
                $this->auditor->registrar('USUARIOS', 'CAMBIO_PASSWORD', $ado->id, null, null, "Contraseña modificada en edición");
            }

            $this->auditor->registrar('USUARIOS', 'ACTUALIZAR', $ado->id, $anterior->toArray(), $ado->toArray(), "Usuario modificado");
            $this->logger->info("Usuario actualizado ID: {$ado->id}");

            return $this->respuestaExitosa("Usuario actualizado exitosamente.", $ado);
        });
    }

    public function cambiarPassword(int $userId, string $nuevaPassword, ?string $passwordActual = null, bool $verificarActual = true): array
    {
        return $this->ejecutarTransaccion(function () use ($userId, $nuevaPassword, $passwordActual, $verificarActual) {
            $usuario = $this->dal->obtenerPorId($userId);
            if (!$usuario) {
                $this->lanzarExcepcion("El usuario no existe.", 404);
            }

            if (strlen($nuevaPassword) < 8) {
                $this->lanzarExcepcion("La nueva contraseña debe tener mínimo 8 caracteres.", 422);
            }

            if ($verificarActual && !empty($passwordActual)) {
                $rawUser = $this->dal->table('users')->where('id', $userId)->first();
                if (!$rawUser || !$this->security->verifyPassword($passwordActual, (string)$rawUser->password)) {
                    $this->lanzarExcepcion("La contraseña actual es incorrecta.", 422);
                }
            }

            $hash = $this->security->hashPassword($nuevaPassword);
            $this->dal->actualizarPassword($userId, $hash);

            $this->auditor->registrar('USUARIOS', 'CAMBIO_PASSWORD', $userId, null, null, "Contraseña restablecida exitosamente");
            $this->logger->info("Contraseña actualizada para el usuario ID: {$userId}");

            return $this->respuestaExitosa("Contraseña actualizada exitosamente.");
        });
    }

    public function cambiarEstado(int $id, bool $nuevoEstado): array
    {
        return $this->ejecutarTransaccion(function () use ($id, $nuevoEstado) {
            $usuario = $this->dal->obtenerPorId($id);
            if (!$usuario) {
                $this->lanzarExcepcion("El usuario indicado no existe.", 404);
            }

            $this->dal->cambiarEstado($id, $nuevoEstado);
            $this->auditor->registrar(
                'USUARIOS',
                $nuevoEstado ? 'ACTIVAR' : 'DESACTIVAR',
                $id,
                ['activo' => $usuario->activo],
                ['activo' => $nuevoEstado]
            );

            return $this->respuestaExitosa("Estado del usuario actualizado correctamente.");
        });
    }

    public function eliminar(int $id): array
    {
        return $this->ejecutarTransaccion(function () use ($id) {
            $usuario = $this->dal->obtenerPorId($id);
            if (!$usuario) {
                $this->lanzarExcepcion("El usuario no existe.", 404);
            }

            // Regla: no permitir auto-eliminación
            if ($this->security->getAuthenticatedUserId() === $id) {
                $this->lanzarExcepcion("No puede eliminar su propia cuenta de usuario en sesión.", 422);
            }

            $this->dal->eliminar($id);
            $this->auditor->registrar('USUARIOS', 'ELIMINAR', $id, $usuario->toArray(), null, "Usuario eliminado");
            $this->logger->info("Usuario eliminado ID: {$id}");

            return $this->respuestaExitosa("Usuario eliminado correctamente.");
        });
    }
}
