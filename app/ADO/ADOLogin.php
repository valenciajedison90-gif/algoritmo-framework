<?php

declare(strict_types=1);

namespace App\ADO;

/**
 * Objeto de Transporte de Datos (ADO) para el proceso de Login y Autenticación.
 */
class ADOLogin extends BaseADO
{
    public function __construct(
        public string $email = '',
        public string $password = '',
        public bool $remember = false,
        public ?string $ip = null,
        public ?string $user_agent = null,
    ) {}

    public static function fromArray(array $datos): static
    {
        return new static(
            email: trim((string) ($datos['email'] ?? '')),
            password: (string) ($datos['password'] ?? ''),
            remember: (bool) ($datos['remember'] ?? false),
            ip: isset($datos['ip']) ? (string) $datos['ip'] : null,
            user_agent: isset($datos['user_agent']) ? (string) $datos['user_agent'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'remember' => $this->remember,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
        ];
    }
}
