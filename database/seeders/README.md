# Seeders de Algoritmo Framework

Este directorio contiene los seeders de datos iniciales del framework:

1. `DatabaseSeeder.php`: Orquestador principal que invoca los seeders de empresas y administradores.
2. `EmpresaSeeder.php`: Inserta organizaciones demo representativas (CHEC S.A. E.S.P. y Algoritmo S.A.S.).
3. `AdminSeeder.php`: Provisiona las cuentas iniciales del sistema:
   * **Super Administrador:** `admin@algoritmo.com` / `password123`
   * **Operador CHEC:** `operador@chec.com.co` / `password123`

## Ejecución
```bash
php artisan db:seed
```
O durante la migración:
```bash
php artisan migrate --seed
```