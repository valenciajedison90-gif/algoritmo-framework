# Regla de Rendimiento y Optimización

## Directivas de Performance
1. **Evitar Consultas N+1:**
   * En la capa DAL, utilizar `JOIN` explícitos en lugar de iterar colecciones ejecutando consultas adicionales por cada elemento.
2. **Paginación del Lado del Servidor:**
   * Nunca cargar tablas completas en memoria para DataTables con miles de filas. Utilizar siempre `offset` y `limit` en la base de datos a través de `dataTable()` de DAL.
3. **Optimización del Autoload de Composer:**
   * En producción ejecutar `composer dump-autoload -o --no-dev`.
4. **Caché de Configuración y Rutas:**
   * En despliegues ejecutar `php artisan config:cache`, `php artisan route:cache` y `php artisan view:cache`.
