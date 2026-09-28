# Regla de Seguridad: OWASP Top 10 y Mitigación de Vulnerabilidades

## Directivas Obligatorias de Seguridad

1. **Prevención de Inyección SQL (A03:2021-Injection):**
   * Queda terminantemente prohibida la concatenación de variables en consultas SQL.
   * Toda consulta en la capa DAL debe utilizar sentencias preparadas con bindings de PDO (`?` o parámetros con nombre).
2. **Protección contra Falsificación de Petición en Sitios Cruzados (CSRF):**
   * Todos los formularios HTML deben incluir la directiva `@csrf`.
   * Todas las peticiones AJAX deben incluir la cabecera `X-CSRF-TOKEN`.
3. **Cifrado de Credenciales y Contraseñas:**
   * Las contraseñas se almacenan obligatoriamente hasheadas con Bcrypt o Argon2id a través de `Hash::make()` o `SecurityService::hashPassword()`.
   * Nunca guardar contraseñas en texto plano.
4. **Control de Acceso y Aislamiento de Inquilinos (A01:2021-Broken Access Control):**
   * Todas las rutas internas requieren middleware `auth`.
   * En entornos multi-empresa, la capa BLL y `PermissionService::validarAccesoEmpresa()` deben verificar que el usuario tenga acceso explícito al `empresa_id` del registro antes de ejecutar consultas o mutaciones.
5. **Carga Segura de Archivos:**
   * Validar extensiones y tipos MIME estrictamente en `FormRequest`.
   * Los archivos se guardan con identificadores únicos (UUID) en el almacenamiento seguro de Laravel (`storage/app/`), nunca con su nombre original en rutas públicas directas.
6. **Manejo de Errores y Fuga de Información:**
   * Los mensajes de error expuestos al cliente nunca deben revelar trazas del stack, credenciales ni nombres de servidores o tablas internas.