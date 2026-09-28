# Skill: Generación de Reportes y Consultas Analíticas

## Arquitectura de Reportes
1. **Flujo Desacoplado:**
   * Las consultas complejas, agrupamientos, agregaciones y filtros temporales residen exclusivamente en la capa `DAL` (ej: `obtenerEstadisticasMensuales()`, `generarReporteConsolidado()`).
2. **Capa BLL:**
   * La capa `BLL` procesa los totales, calcula variaciones porcentuales, valida que el rango de fechas no exceda los límites operativos permitidos y aplica permisos de consulta.
3. **Presentación de Reportes:**
   * Renderizado en vistas Blade optimizadas para impresión (`@media print`) o exportación binaria estructurada.
