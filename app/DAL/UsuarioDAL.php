<?php

declare(strict_types=1);

namespace App\DAL;

use App\ADO\ADOUsuario;

/**
 * Data Access Layer para la gestión de Usuarios del Sistema.
 * Sentencias 100% parametrizadas con joins a empresas.
 */
class UsuarioDAL extends BaseDAL
{
    protected string $tabla = 'users';

    public function obtenerPorId(int $id): ?ADOUsuario
    {
        $registro = $this->table($this->tabla . ' as u')
            ->leftJoin('empresas as e', 'u.empresa_id', '=', 'e.id')
            ->select('u.*', 'e.razon_social as empresa_nombre')
            ->where('u.id', $id)
            ->first();

        return $registro ? ADOUsuario::fromArray((array) $registro) : null;
    }

    public function obtenerPorEmail(string $email): ?ADOUsuario
    {
        $registro = $this->table($this->tabla . ' as u')
            ->leftJoin('empresas as e', 'u.empresa_id', '=', 'e.id')
            ->select('u.*', 'e.razon_social as empresa_nombre')
            ->where('u.email', $email)
            ->first();

        return $registro ? ADOUsuario::fromArray((array) $registro) : null;
    }

    public function obtenerPorDocumento(string $documento): ?ADOUsuario
    {
        $registro = $this->table($this->tabla)
            ->where('documento', $documento)
            ->first();

        return $registro ? ADOUsuario::fromArray((array) $registro) : null;
    }

    public function listar(array $filtros = []): array
    {
        $query = $this->table($this->tabla . ' as u')
            ->leftJoin('empresas as e', 'u.empresa_id', '=', 'e.id')
            ->select('u.*', 'e.razon_social as empresa_nombre');

        if (!empty($filtros['empresa_id'])) {
            $query->where('u.empresa_id', (int) $filtros['empresa_id']);
        }

        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $query->where('u.activo', (bool) $filtros['activo']);
        }

        if (!empty($filtros['rol'])) {
            $query->where('u.rol', $filtros['rol']);
        }

        $registros = $query->orderBy('u.name', 'asc')->get();

        return array_map(fn($item) => ADOUsuario::fromArray((array) $item), $registros->toArray());
    }

    public function dataTable(array $params): array
    {
        $draw = (int) ($params['draw'] ?? 1);
        $start = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $searchValue = (string) ($params['search']['value'] ?? '');

        $query = $this->table($this->tabla . ' as u')
            ->leftJoin('empresas as e', 'u.empresa_id', '=', 'e.id')
            ->select('u.*', 'e.razon_social as empresa_nombre');

        $totalRecords = $query->count();

        if ($searchValue !== '') {
            $like = '%' . $searchValue . '%';
            $query->where(function ($q) use ($like) {
                $q->where('u.name', 'like', $like)
                  ->orWhere('u.email', 'like', $like)
                  ->orWhere('u.documento', 'like', $like)
                  ->orWhere('u.rol', 'like', $like)
                  ->orWhere('e.razon_social', 'like', $like);
            });
        }

        $filteredRecords = $query->count();

        $columnas = ['u.id', 'u.name', 'u.email', 'u.documento', 'e.razon_social', 'u.rol', 'u.activo', 'u.ultimo_acceso'];
        $orderColIndex = (int) ($params['order'][0]['column'] ?? 0);
        $orderDir = strtolower((string) ($params['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $orderColumn = $columnas[$orderColIndex] ?? 'u.id';

        $data = $query->orderBy($orderColumn, $orderDir)
                      ->offset($start)
                      ->limit($length)
                      ->get();

        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data->toArray(),
        ];
    }

    public function crear(ADOUsuario $ado): int
    {
        $datos = [
            'empresa_id' => $ado->empresa_id,
            'name' => $ado->name,
            'email' => $ado->email,
            'password' => $ado->password,
            'documento' => $ado->documento,
            'telefono' => $ado->telefono,
            'rol' => $ado->rol,
            'activo' => $ado->activo ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $this->insertGetId($this->tabla, $datos);
    }

    public function actualizar(ADOUsuario $ado): bool
    {
        if (!$ado->id) {
            return false;
        }

        $datos = [
            'empresa_id' => $ado->empresa_id,
            'name' => $ado->name,
            'email' => $ado->email,
            'documento' => $ado->documento,
            'telefono' => $ado->telefono,
            'rol' => $ado->rol,
            'activo' => $ado->activo ? 1 : 0,
            'updated_at' => now(),
        ];

        return (bool) $this->update($this->tabla, ['id' => $ado->id], $datos);
    }

    public function actualizarPassword(int $id, string $hashedPassword): bool
    {
        return (bool) $this->update(
            $this->tabla,
            ['id' => $id],
            ['password' => $hashedPassword, 'updated_at' => now()]
        );
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        return (bool) $this->update(
            $this->tabla,
            ['id' => $id],
            ['activo' => $activo ? 1 : 0, 'updated_at' => now()]
        );
    }

    public function actualizarUltimoAcceso(int $id): bool
    {
        return (bool) $this->update(
            $this->tabla,
            ['id' => $id],
            ['ultimo_acceso' => now()]
        );
    }

    public function eliminar(int $id): bool
    {
        return (bool) $this->delete($this->tabla, ['id' => $id]);
    }
}
