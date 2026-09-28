# Skill: Creación de Módulos CRUD Completos en Algoritmo Framework

## Estructura Estándar de Operaciones CRUD
Para cualquier entidad del sistema (ej: Clientes, Productos, Documentos):

1. **Listar / Index:**
   * Vista `index.blade.php` con tabla y botón para creación.
   * Endpoint `GET /entidad/datatable` conectado a `{Entidad}BLL::dataTable()` y `{Entidad}DAL::dataTable()`.
2. **Crear / Store:**
   * Vista `create.blade.php` con formulario HTML y directiva `@csrf`.
   * FormRequest `{Entidad}Request` para validar.
   * Método `{Entidad}Controller::store()` mapea a `ADO{Entidad}` y llama a `{Entidad}BLL::guardar($ado)`.
3. **Ver / Show:**
   * Vista `show.blade.php` con lista descriptiva (`<dl>`) renderizando propiedades del objeto `ADO`.
4. **Editar / Update:**
   * Vista `edit.blade.php` con formulario pre-cargado y directiva `@method('PUT')`.
   * Método `{Entidad}Controller::update()` asigna el ID al ADO y llama a `{Entidad}BLL::guardar($ado)`.
5. **Eliminar / Destroy:**
   * Llamada AJAX o formulario con `@method('DELETE')`.
   * `{Entidad}BLL::eliminar($id)` valida reglas de integridad antes de invocar a DAL.
