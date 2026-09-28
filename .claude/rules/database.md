# Regla de Base de Datos: Estructura, Migraciones y Transaccionalidad

## Estándares de Diseño de Datos
1. **Convención de Tablas y Columnas:**
   * Nombres de tablas en minúsculas y plural en español o inglés consistente (ej: `empresas`, `users`, `audits`).
   * Clave primaria estándar: `id` autoincremental de tipo `BIGINT UNSIGNED`.
   * Llaves foráneas: `{tabla_singular}_id` (ej: `empresa_id`, `user_id`).
   * Índices en campos de búsqueda frecuente: `documento`, `email`, `activo`, `nit`, `modulo`, `accion`.
2. **Migraciones:**
   * Cada cambio en la base de datos debe tener su archivo de migración versionado en `database/migrations/`.
   * El método `down()` debe revertir fielmente los cambios hechos en `up()`.
3. **Seeders:**
   * Los datos base y usuarios administradores por defecto se provisionan mediante seeders que utilicen `updateOrInsert` para permitir ejecuciones idempotentes sin duplicar registros.
4. **Transacciones Atómicas:**
   * Toda operación que afecte dos o más registros, o que combine una mutación de negocio con auditoría en la capa DAL/BLL, debe ejecutarse bajo una transacción atómica (`DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`).
