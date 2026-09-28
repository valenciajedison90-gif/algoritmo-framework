# Regla de Nomenclatura: Convenciones de Nombres en Algoritmo Framework

## Convenciones Estrictas de Código

| Elemento | Convención | Ejemplo |
| :--- | :--- | :--- |
| **Clase ADO** | `ADO{Entidad}` | `ADOEmpresa.php`, `ADOUsuario.php`, `ADOLogin.php` |
| **Clase BLL** | `{Entidad}BLL` | `EmpresaBLL.php`, `UsuarioBLL.php`, `LoginBLL.php` |
| **Clase DAL** | `{Entidad}DAL` | `EmpresaDAL.php`, `UsuarioDAL.php`, `LoginDAL.php` |
| **Controlador** | `{Entidad}Controller` | `EmpresaController.php`, `UsuarioController.php` |
| **FormRequest** | `{Entidad}Request` | `EmpresaRequest.php`, `UsuarioRequest.php` |
| **Servicios Core** | `{Nombre}Service` | `SecurityService.php`, `AuditService.php`, `MailService.php` |
| **Tablas de BD** | Plural en minúsculas snake_case | `empresas`, `users`, `audits` |
| **Columnas de BD** | snake_case | `razon_social`, `empresa_id`, `ultimo_acceso` |
| **Métodos PHP** | camelCase | `obtenerPorId()`, `guardar()`, `cambiarEstado()` |
| **Variables PHP** | camelCase | `$empresaId`, `$usuarioActual`, `$nuevoEstado` |
| **Rutas con nombre**| `{entidad}.{accion}` | `empresas.index`, `empresas.store`, `usuarios.datatable` |
| **Vistas Blade** | `{entidades}/{accion}.blade.php` | `empresas/index.blade.php`, `auth/login.blade.php` |