<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Administrador General
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@algoritmo.com'],
            [
                'empresa_id' => 1,
                'name' => 'Administrador Algoritmo',
                'password' => Hash::make('password123'),
                'documento' => '1000000001',
                'telefono' => '3001234567',
                'rol' => 'superadmin',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Operador Demo CHEC
        DB::table('users')->updateOrInsert(
            ['email' => 'operador@chec.com.co'],
            [
                'empresa_id' => 1,
                'name' => 'Operador Terreno CHEC',
                'password' => Hash::make('password123'),
                'documento' => '1000000002',
                'telefono' => '3119876543',
                'rol' => 'operador',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
