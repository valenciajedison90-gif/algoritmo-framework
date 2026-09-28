<?php

declare(strict_types=1);

namespace App\DAL;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Base Data Access Layer (DAL) de Algoritmo Framework.
 * Centraliza las conexiones parametrizadas y el manejo seguro de transacciones.
 */
abstract class BaseDAL
{
    protected ?string $connectionName = null;

    /**
     * Retorna la conexión activa de base de datos.
     */
    public function connection(): ConnectionInterface
    {
        return DB::connection($this->connectionName);
    }

    /**
     * Inicia una transacción atómica de base de datos.
     */
    public function beginTransaction(): void
    {
        $this->connection()->beginTransaction();
    }

    /**
     * Confirma la transacción en curso.
     */
    public function commit(): void
    {
        $this->connection()->commit();
    }

    /**
     * Revierte la transacción en curso ante cualquier fallo.
     */
    public function rollback(): void
    {
        $this->connection()->rollBack();
    }

    /**
     * Obtiene un Query Builder apuntando a una tabla específica en la conexión configurada.
     */
    public function table(string $table): Builder
    {
        return $this->connection()->table($table);
    }

    /**
     * Ejecuta una consulta SELECT parametrizada y retorna una sola fila como objeto stdClass.
     */
    public function selectOne(string $query, array $bindings = []): ?object
    {
        return $this->connection()->selectOne($query, $bindings);
    }

    /**
     * Ejecuta una consulta SELECT parametrizada y retorna una colección de filas.
     */
    public function select(string $query, array $bindings = []): array
    {
        return $this->connection()->select($query, $bindings);
    }

    /**
     * Ejecuta un INSERT parametrizado en una tabla y retorna el ID generado.
     */
    public function insertGetId(string $table, array $values): int
    {
        return (int) $this->table($table)->insertGetId($values);
    }

    /**
     * Ejecuta un UPDATE parametrizado con condiciones.
     */
    public function update(string $table, array $where, array $values): int
    {
        return $this->table($table)->where($where)->update($values);
    }

    /**
     * Ejecuta un DELETE parametrizado con condiciones.
     */
    public function delete(string $table, array $where): int
    {
        return $this->table($table)->where($where)->delete();
    }
}