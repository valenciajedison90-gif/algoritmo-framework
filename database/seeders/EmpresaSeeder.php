<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = [
            [
                'id' => 1,
                'nit' => '890800001-1',
                'razon_social' => 'Central Hidroeléctrica de Caldas S.A. E.S.P. - CHEC',
                'direccion' => 'Carrera 23 # 64-10, Manizales, Caldas',
                'telefono' => '(606) 8899000',
                'email' => 'contacto@chec.com.co',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nit' => '900123456-7',
                'razon_social' => 'Empresa de Servicios Tecnológicos Algoritmo S.A.S.',
                'direccion' => 'Calle 100 # 15-20, Bogotá D.C.',
                'telefono' => '(601) 7458000',
                'email' => 'admin@algoritmo.com',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($empresas as $empresa) {
            DB::table('empresas')->updateOrInsert(['id' => $empresa['id']], $empresa);
        }
    }
}
