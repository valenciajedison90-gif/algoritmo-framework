<?php

declare(strict_types=1);

namespace App\BLL;

use App\ADO\ADOEmpresa;
use App\Core\AuditService;
use App\Core\BusinessException;
use App\Core\LogService;
use App\DAL\EmpresaDAL;

/**
 * Business Logic Layer (BLL) para la gestión de Empresas.
 * Aplica todas las reglas de negocio, validaciones y auditoría.
 */
class EmpresaBLL extends BaseBLL
{
    protected EmpresaDAL $dal;

    public function __construct(LogService $logger, AuditService $auditor, EmpresaDAL $dal)
    {
        parent::__construct($logger, $auditor);
        $this->dal = $dal;
    }

    public function obtener(int $id): array
    {
        $empresa = $this->dal->obtenerPorId($id);
        if (!$empresa) {
            return $this->respuestaError("La empresa solicitada no existe.", ['id' => $id]);
        }
        return $this->respuestaExitosa("Empresa obtenida correctamente.", $empresa);
    }

    public function listar(array $filtros = []): array
    {
        $empresas = $this->dal->listar($filtros);
        return $this->respuestaExitosa("Listado de empresas obtenido correctamente.", $empresas);
    }

    public function dataTable(array $params): array
    {
        return $this->dal->dataTable($params);
    }

    public function guardar(ADOEmpresa $ado): array
    {
        return $this->ejecutarTransaccion(function () use ($ado) {
            // Regla 1: Validar unicidad del NIT
            $empresaExistente = $this->dal->obtenerPorNit($ado->nit);
            if ($empresaExistente && $empresaExistente->id !== $ado->id) {
                $this->lanzarExcepcion(
                    "El NIT '{$ado->nit}' ya se encuentra registrado para la empresa '{$empresaExistente->razon_social}'.",
                    422,
                    ['nit' => 'NIT ya registrado']
                );
            }

            if ($ado->id === null || $ado->id === 0) {
                // Creación
                $nuevoId = $this->dal->crear($ado);
                $ado->id = $nuevoId;

                $this->auditor->registrar('EMPRESAS', 'CREAR', $nuevoId, null, $ado->toArray(), "Empresa creada exitosamente");
                $this->logger->info("Empresa registrada con ID: {$nuevoId}");

                return $this->respuestaExitosa("Empresa creada exitosamente.", $ado);
            }

            // Actualización
            $anterior = $this->dal->obtenerPorId($ado->id);
            if (!$anterior) {
                $this->lanzarExcepcion("La empresa a actualizar no existe.", 404);
            }

            $this->dal->actualizar($ado);
            $this->auditor->registrar('EMPRESAS', 'ACTUALIZAR', $ado->id, $anterior->toArray(), $ado->toArray(), "Empresa actualizada");
            $this->logger->info("Empresa actualizada ID: {$ado->id}");

            return $this->respuestaExitosa("Empresa actualizada exitosamente.", $ado);
        });
    }

    public function cambiarEstado(int $id, bool $nuevoEstado): array
    {
        return $this->ejecutarTransaccion(function () use ($id, $nuevoEstado) {
            $empresa = $this->dal->obtenerPorId($id);
            if (!$empresa) {
                $this->lanzarExcepcion("La empresa indicada no existe.", 404);
            }

            // Si se va a desactivar, verificar si tiene usuarios asociados
            if (!$nuevoEstado) {
                $usuariosCount = $this->dal->contarUsuariosAsociados($id);
                if ($usuariosCount > 0) {
                    $this->lanzarExcepcion("No se puede desactivar la empresa porque tiene {$usuariosCount} usuario(s) asociado(s).");
                }
            }

            $this->dal->cambiarEstado($id, $nuevoEstado);
            $this->auditor->registrar(
                'EMPRESAS',
                $nuevoEstado ? 'ACTIVAR' : 'DESACTIVAR',
                $id,
                ['activo' => $empresa->activo],
                ['activo' => $nuevoEstado]
            );

            return $this->respuestaExitosa("Estado de la empresa actualizado correctamente.");
        });
    }

    public function eliminar(int $id): array
    {
        return $this->ejecutarTransaccion(function () use ($id) {
            $empresa = $this->dal->obtenerPorId($id);
            if (!$empresa) {
                $this->lanzarExcepcion("La empresa indicada no existe.", 404);
            }

            // Regla de integridad de negocio: no eliminar empresas con usuarios
            $usuariosCount = $this->dal->contarUsuariosAsociados($id);
            if ($usuariosCount > 0) {
                $this->lanzarExcepcion("No es posible eliminar la empresa porque posee {$usuariosCount} usuario(s) registrado(s).");
            }

            $this->dal->eliminar($id);
            $this->auditor->registrar('EMPRESAS', 'ELIMINAR', $id, $empresa->toArray(), null, "Empresa eliminada");
            $this->logger->info("Empresa eliminada ID: {$id}");

            return $this->respuestaExitosa("Empresa eliminada correctamente.");
        });
    }
}
