# Skill: Implementación del Patrón BLL / DAL / ADO en Laravel 12

## Propósito
Guiar al desarrollador en la creación de un nuevo módulo respetando de principio a fin el flujo arquitectónico desacoplado de Algoritmo Framework.

## Pasos para Crear un Módulo
1. **Definir el ADO (`app/ADO/ADO{Modulo}.php`):**
   * Extender de `BaseADO`.
   * Declarar propiedades con tipos estrictos PHP 8.2 en el constructor.
   * Implementar `fromArray(array $datos): static` y `toArray(): array`.
2. **Definir la Capa DAL (`app/DAL/{Modulo}DAL.php`):**
   * Extender de `BaseDAL`.
   * Declarar la tabla `$tabla = 'nombre_tabla'`.
   * Implementar `obtenerPorId`, `listar`, `dataTable`, `crear`, `actualizar`, `eliminar`.
   * Toda consulta debe ser parametrizada mediante `$this->table()` o `$this->select()`.
3. **Definir la Capa BLL (`app/BLL/{Modulo}BLL.php`):**
   * Extender de `BaseBLL`.
   * Inyectar dependencias: `LogService`, `AuditService`, `{Modulo}DAL`.
   * Implementar reglas de validación, transacciones con `$this->ejecutarTransaccion()` y excepciones `BusinessException`.
   * Retornar siempre `$this->respuestaExitosa()` o `$this->respuestaError()`.
4. **Definir FormRequest (`app/Http/Requests/{Modulo}Request.php`):**
   * Establecer reglas de validación y mensajes amigables en español.
5. **Definir Controlador (`app/Http/Controllers/{Modulo}Controller.php`):**
   * Inyectar `{Modulo}BLL`.
   * Orquestar la solicitud: validar con el request, transformar a ADO, llamar a BLL y retornar respuesta JSON o Blade.
6. **Registrar Rutas (`routes/web.php`):**
   * Registrar endpoints RESTful y la ruta `/datatable`.
