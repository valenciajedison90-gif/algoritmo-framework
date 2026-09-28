<?php

declare(strict_types=1);

namespace App\ADO;

/**
 * Objeto de Transporte de Datos (ADO) para Usuarios del Sistema.
 */
class ADOUsuario extends BaseADO
{
    public function __construct(
        public ?int $id = null,
        public ?int $empresa_id = null,
        public string $name = '',
        public string $email = '',
        public ?string $password = null,
        public ?string $documento = null,
        public ?string $telefono = null,
        public string $rol = 'operador',
        public bool $activo = true,
        public ?string $ultimo_acceso = null,
        public ?string $remember_token = null,
        public ?string $empresa_nombre = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromArray(array $datos): static
    {
        return new static(
            id: isset($datos['id']) ? (int) $datos['id'] : null,
            empresa_id: isset($datos['empresa_id']) ? (int) $datos['empresa_id'] : null,
            name: (string) ($datos['name'] ?? ''),
            email: (string) ($datos['email'] ?? ''),
            password: isset($datos['password']) ? (string) $datos['password'] : null,
            documento: isset($datos['documento']) ? (string) $datos['documento'] : null,
            telefono: isset($datos['telefono']) ? (string) $datos['telefono'] : null,
            rol: (string) ($datos['rol'] ?? 'operador'),
            activo: isset($datos['activo']) ? (bool) $datos['activo'] : true,
            ultimo_acceso: isset($datos['ultimo_acceso']) ? (string) $datos['ultimo_acceso'] : null,
            remember_token: isset($datos['remember_token']) ? (string) $datos['remember_token'] : null,
            empresa_nombre: isset($datos['empresa_nombre']) ? (string) $datos['empresa_nombre'] : null,
            created_at: isset($datos['created_at']) ? (string) $datos['created_at'] : null,
            updated_at: isset($datos['updated_at']) ? (string) $datos['updated_at'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'name' => $this->name,
            'email' => $this->email,
            'documento' => $this->documento,
            'telefono' => $this->telefono,
            'rol' => $this->rol,
            'activo' => $this->activo,
            'ultimo_acceso' => $this->ultimo_acceso,
            'empresa_nombre' => $this->empresa_nombre,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}