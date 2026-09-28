# Skill: Envío de Notificaciones y Correos Electrónicos

## Directrices para Notificaciones
1. **Uso de MailService:**
   * Utilizar siempre `MailService` de `app/Core/MailService.php`.
   * Queda terminantemente prohibido utilizar librerías externas no administradas o hardcodear destinatarios de prueba como se hacía en el legado (`MBLL_Correo.php`).
2. **Plantillas HTML Blade:**
   * Diseñar los correos mediante vistas Blade organizadas en `resources/views/emails/`.
   * Incluir estilos inline o compatibles con clientes de correo habituales (Outlook, Gmail).
3. **Manejo de Errores:**
   * El envío de correos no debe romper transacciones de negocio críticas; capturar excepciones y registrar fallos en `LogService`.
