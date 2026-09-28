# Regla de Control de Versiones (Git)

## Convenciones de Commits y Ramas
1. **Conventional Commits:**
   * Utilizar prefijos semánticos claros:
     * `feat:` Nueva funcionalidad o módulo.
     * `fix:` Corrección de bug o fallo de seguridad.
     * `refactor:` Mejora interna de código sin alterar comportamiento.
     * `docs:` Documentación, guías o manuales.
     * `test:` Adición o mejora de pruebas unitarias/integración.
     * `chore:` Ajustes de configuración, dependencias o tooling.
2. **Archivos Prohibidos en el Repositorio Core:**
   * Nunca commitear carpetas propias del entorno de Laravel: `vendor/`, `node_modules/`, `storage/`, `bootstrap/cache/`, `.env` con claves reales.
   * El repositorio `algoritmo-framework` aloja únicamente el Core del framework.
