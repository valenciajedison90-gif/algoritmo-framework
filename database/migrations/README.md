# Migraciones de Algoritmo Framework

Este directorio contiene las migraciones oficiales del Core para Laravel 12:

1. `2026_01_01_000001_create_empresas_table.php`: Estructura para empresas u organizaciones asociadas (multi-tenant).
2. `2026_01_01_000002_create_users_table.php`: Extensión o creación de usuarios con vinculación a empresa, rol (`superadmin`, `admin`, `operador`, `consulta`), documento y último acceso.
3. `2026_01_01_000003_create_audits_table.php`: Tabla inmutable para trazabilidad y auditoría de eventos sensibles.

## Ejecución
```bash
php artisan migrate
```
O para reiniciar con datos de prueba:
```bash
php artisan migrate:fresh --seed
```