# Regla de Auditoría y Trazabilidad

## Principios de Auditoría
1. **Registro Obligatorio de Mutaciones:**
   * Toda creación, modificación, eliminación o cambio de estado de registros sensibles en el sistema debe invocar a `AuditService::registrar()`.
2. **Estructura del Registro:**
   * Se debe capturar: `modulo`, `accion`, `registro_id`, `valores_anteriores` (en formato JSON), `valores_nuevos` (en formato JSON), `detalles`, `ip_address` y `user_agent`.
3. **Inmutabilidad de la Auditoría:**
   * La tabla `audits` es estrictamente de adición (*append-only*). No se deben implementar métodos de actualización o eliminación en la capa DAL para los registros de auditoría.
