<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración General de Algoritmo Framework
    |--------------------------------------------------------------------------
    |
    | Parámetros centrales del core, multi-tenant, auditoría y seguridad.
    |
    */

    'version' => '2.0.0',

    'nombre' => env('APP_NAME', 'Algoritmo Framework'),

    'multitenant' => [
        'habilitado' => true,
        'campo_tenant' => 'empresa_id',
    ],

    'auditoria' => [
        'habilitada' => env('AUDIT_ENABLED', true),
        'tabla' => 'audits',
        'limite_historial' => 50,
    ],

    'roles' => [
        'superadmin' => 'Super Administrador',
        'admin' => 'Administrador',
        'operador' => 'Operador',
        'consulta' => 'Solo Consulta',
    ],

    'seguridad' => [
        'longitud_minima_password' => 8,
        'requiere_mayuscula' => false,
        'requiere_numero' => false,
    ],
];