<?php

declare(strict_types=1);

namespace App\DAL;

use App\ADO\ADOEmpresa;

/**
 * Data Access Layer para la gestión de Empresas.
 * Ejecución 100% parametrizada sin concatenación de cadenas SQL.
 */
class EmpresaDAL extends BaseDAL
{
    protected string $tabla = 'empresas';

    public function obtenerPorId(int $id): ?ADOEmpresa
    {
        $registro = $this->table($this->tabla)->where('id', $id)->first();
        return $registro ? ADOEmpresa::fromArray((array) $registro) : null;
    }

    public function obtenerPorNit(string $nit): ?ADOEmpresa
    {
        $registro = $this->table($this->tabla)->where('nit', $nit)->first();
        return $registro ? ADOEmpresa::fromArray((array) $registro) : null;
    }

    public function listar(array $filtros = []): array
    {
        $query = $this->table($this->tabla);

        if (!empty($filtros['buscar'])) {
            $termino = '%' . $filtros['buscar'] . '%';
            $query->where(function ($q) use ($termino) {
                $q->where('razon_social', 'like', $termino)
                  ->orWhere('nit', 'like', $termino)
                  ->orWhere('email', 'like', $termino);
            });
        }

        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $query->where('activo', (bool) $filtros['activo']);
        }

        $registros = $query->orderBy('razon_social', 'asc')->get();

        return array_map(fn($item) => ADOEmpresa::fromArray((array) $item), $registros->toArray());
    }

    /**
     * Procesa la paginación, búsqueda y ordenamiento para jQuery DataTables server-side.
     */
    public function dataTable(array $params): array
    {
        $draw = (int) ($params['draw'] ?? 1);
        $start = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $searchValue = (string) ($params['search']['value'] ?? '');

        $query = $this->table($this->tabla);
        $totalRecords = $query->count();

        if ($searchValue !== '') {
            $like = '%' . $searchValue . '%';
            $query->where(function ($q) use ($like) {
                $q->where('nit', 'like', $like)
                  ->orWhere('razon_social', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('telefono', 'like', $like);
            });
        }

        $filteredRecords = $query->count();

        // Mapeo de columnas para ordenamiento
        $columnas = ['id', 'nit', 'razon_social', 'telefono', 'email', 'activo', 'created_at'];
        $orderColIndex = (int) ($params['order'][0]['column'] ?? 0);
        $orderDir = strtolower((string) ($params['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $orderColumn = $columnas[$orderColIndex] ?? 'id';

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

    public function crear(ADOEmpresa $ado): int
    {
        $datos = [
            'nit' => $ado->nit,
            'razon_social' => $ado->razon_social,
            'direccion' => $ado->direccion,
            'telefono' => $ado->telefono,
            'email' => $ado->email,
            'activo' => $ado->activo ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $this->insertGetId($this->tabla, $datos);
    }

    public function actualizar(ADOEmpresa $ado): bool
    {
        if (!$ado->id) {
            return false;
        }

        $datos = [
            'nit' => $ado->nit,
            'razon_social' => $ado->razon_social,
            'direccion' => $ado->direccion,
            'telefono' => $ado->telefono,
            'email' => $ado->email,
            'activo' => $ado->activo ? 1 : 0,
            'updated_at' => now(),
        ];

        return (bool) $this->update($this->tabla, ['id' => $ado->id], $datos);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        return (bool) $this->update(
            $this->tabla,
            ['id' => $id],
            ['activo' => $activo ? 1 : 0, 'updated_at' => now()]
        );
    }

    public function eliminar(int $id): bool
    {
        return (bool) $this->delete($this->tabla, ['id' => $id]);
    }

    public function contarUsuariosAsociados(int $empresaId): int
    {
        return $this->table('users')->where('empresa_id', $empresaId)->count();
    }
}
