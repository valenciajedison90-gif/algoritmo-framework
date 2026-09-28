<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use Throwable;

/**
 * Excepción de dominio para reglas de negocio en Algoritmo Framework.
 */
class BusinessException extends Exception
{
    protected array $errores;
    protected int $httpStatusCode;

    public function __construct(
        string $message = "Error en la operación de negocio",
        int $code = 422,
        array $errores = [],
        ?Throwable $previous = null,
        int $httpStatusCode = 422
    ) {
        parent::__construct($message, $code, $previous);
        $this->errores = $errores;
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function toResponseArray(): array
    {
        return [
            'estado' => false,
            'mensaje' => $this->getMessage(),
            'datos' => [
                'codigo' => $this->getCode(),
                'errores' => $this->errores,
            ],
        ];
    }
}