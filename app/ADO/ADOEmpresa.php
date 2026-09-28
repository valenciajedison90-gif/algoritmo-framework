<?php

declare(strict_types=1);

namespace App\ADO;

/**
 * Objeto de Transporte de Datos (ADO) para la entidad Empresa.
 */
class ADOEmpresa extends BaseADO
{
    public function __construct(
        public ?int $id = null,
        public string $nit = '',
        public string $razon_social = '',
        public ?string $direccion = null,
        public ?string $telefono = null,
        public ?string $email = null,
        public bool $activo = true,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        ?string $nombre = null,
    ) {
        if (!empty($nombre) && empty($this->razon_social)) {
            $this->razon_social = $nombre;
        }
    }

    public static function fromArray(array $datos): static
    {
        return new static(
            id: isset($datos['id']) ? (int) $datos['id'] : null,
            nit: (string) ($datos['nit'] ?? ''),
            razon_social: (string) ($datos['razon_social'] ?? $datos['nombre'] ?? ''),
            direccion: isset($datos['direccion']) ? (string) $datos['direccion'] : null,
            telefono: isset($datos['telefono']) ? (string) $datos['telefono'] : null,
            email: isset($datos['email']) ? (string) $datos['email'] : null,
            activo: isset($datos['activo']) ? (bool) $datos['activo'] : true,
            created_at: isset($datos['created_at']) ? (string) $datos['created_at'] : null,
            updated_at: isset($datos['updated_at']) ? (string) $datos['updated_at'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nit' => $this->nit,
            'razon_social' => $this->razon_social,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}