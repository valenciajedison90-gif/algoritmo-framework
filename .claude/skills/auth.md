# Skill: Flujo de Autenticación, Sesiones y Seguridad Multi-Tenant

## Flujo de Login Oficial
El proceso de login nunca se realiza invocando facades de autenticación desde el controlador. Sigue la secuencia:

1. **Petición HTTP:** El usuario envía credenciales desde la vista `resources/views/auth/login.blade.php`.
2. **Validación:** `LoginRequest` valida formato de correo y longitud de contraseña.
3. **Controlador:** `LoginController::login()` extrae credenciales, IP y User-Agent y crea una instancia de `ADOLogin`.
4. **Capa BLL:** `LoginBLL::autenticar(ADOLogin $ado)`:
   * Consulta al usuario mediante `LoginDAL::buscarParaAutenticacion($email)`.
   * Verifica la contraseña con `SecurityService::verifyPassword()` (`Hash::check`).
   * Valida si el usuario está activo (`activo === 1`).
   * Valida si la empresa vinculada está activa (`empresa.activo === 1`).
   * Si las reglas pasan, inicia sesión con `Auth::loginUsingId($id, $remember)`.
   * Actualiza la fecha de último acceso y remember token vía `LoginDAL::actualizarAcceso()`.
   * Registra el evento en `AuditService::registrar('AUTH', 'LOGIN', ...)`.
5. **Respuesta:** Redirige al dashboard o retorna JSON estructurado con el estado de la sesión.
