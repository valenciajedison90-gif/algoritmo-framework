<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Servicio Central de Seguridad y Criptografía de Algoritmo Framework.
 * Reemplaza y moderniza el legado MBLL_Seguridad.
 */
class SecurityService
{
    /**
     * Hashea una contraseña usando el algoritmo seguro configurado (Bcrypt / Argon2).
     */
    public function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Verifica si una contraseña en texto plano coincide con el hash almacenado.
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return Hash::check($plainPassword, $hashedPassword);
    }

    /**
     * Genera un token criptográficamente seguro.
     */
    public function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Sanitiza una cadena de texto para prevenir XSS y ataques de inyección.
     */
    public function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim(strip_tags($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza un arreglo asociativo recursivamente.
     */
    public function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $cleanKey = is_string($key) ? $this->sanitizeString($key) : $key;
            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$cleanKey] = $this->sanitizeString($value);
            } else {
                $sanitized[$cleanKey] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Obtiene el ID del usuario actualmente autenticado.
     */
    public function getAuthenticatedUserId(): ?int
    {
        return Auth::check() ? (int) Auth::id() : null;
    }

    /**
     * Obtiene el usuario autenticado actual.
     */
    public function getAuthenticatedUser(): mixed
    {
        return Auth::user();
    }

    /**
     * Obtiene el ID de la empresa del usuario actual (soporte multi-tenant).
     */
    public function getCurrentEmpresaId(): ?int
    {
        $user = Auth::user();
        if ($user && isset($user->empresa_id)) {
            return (int) $user->empresa_id;
        }
        return null;
    }

    /**
     * Verifica si el usuario actual posee un rol específico.
     */
    public function hasRole(string $role): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return isset($user->rol) && strtolower((string)$user->rol) === strtolower($role);
    }
}
