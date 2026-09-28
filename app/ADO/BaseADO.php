<?php

declare(strict_types=1);

namespace App\ADO;

use JsonSerializable;

/**
 * Base Application Data Object (ADO / DTO) de Algoritmo Framework.
 * Reemplaza y unifica los MBLL_* y MDAL_* del sistema legado en objetos tipados.
 */
abstract class BaseADO implements JsonSerializable
{
    /**
     * Instancia el ADO a partir de un arreglo asociativo o fila de base de datos.
     */
    abstract public static function fromArray(array $datos): static;

    /**
     * Convierte el ADO en un arreglo asociativo estándar.
     */
    abstract public function toArray(): array;

    /**
     * Serialización nativa a JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Retorna la representación JSON del objeto.
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
