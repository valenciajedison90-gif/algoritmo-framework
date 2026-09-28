# Claude SDK - Algoritmo Framework (Laravel 12)

Este directorio contiene la especificación completa, directivas, agentes y skills para el desarrollo guiado por IA sobre **Algoritmo Framework**.

---

## 1. Arquitectura Central
Toda interacción y código generado debe obedecer rigurosamente la secuencia:

$$\text{FormRequest} \longrightarrow \text{Controller} \longrightarrow \text{ADO} \longrightarrow \text{BLL} \longrightarrow \text{DAL} \longrightarrow \text{Base de Datos}$$

* **Controllers:** Únicamente orquestan la petición HTTP.
* **ADO:** Transporta la información tipada (`BaseADO`).
* **BLL:** Ejecuta las reglas de negocio y transacciones (`BaseBLL`).
* **DAL:** Gestiona el acceso a datos y consultas parametrizadas (`BaseDAL`).
* **Blade:** Renderiza la interfaz de usuario sin lógica incrustada.

---

## 2. Índice de Reglas (`.claude/rules/`)
* [architecture.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/architecture.md): Principios de Clean Architecture y flujo obligatorio.
* [backend.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/backend.md): Estándares PHP 8.2+, PSR-12 y respuestas uniformes.
* [frontend.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/frontend.md): Plantillas Blade, Tailwind CSS y DataTables server-side.
* [security.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/security.md): OWASP Top 10, CSRF, sanitización y control multi-tenant.
* [database.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/database.md): Convenciones de esquemas, migraciones y atomicidad.
* [testing.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/testing.md): Baterías de pruebas para BLL y DAL.
* [performance.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/performance.md): Optimización, prevención de N+1 y caché.
* [naming.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/naming.md): Convenciones estrictas de nomenclatura.
* [ui.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/ui.md): Experiencia de usuario, componentes y accesibilidad.
* [auditing.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/auditing.md): Registro inmutable de eventos con AuditService.
* [git.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/rules/git.md): Convención de commits y exclusiones del repositorio.

---

## 3. Índice de Agentes (`.claude/agents/`)
* [architect.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/architect.md): Arquitecto Principal de Software.
* [backend.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/backend.md): Desarrollador Backend PHP 8.2.
* [frontend.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/frontend.md): Especialista Frontend Blade y Tailwind.
* [database.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/database.md): Ingeniero de Base de Datos.
* [security.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/security.md): Auditor y Oficial de Seguridad.
* [reviewer.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/reviewer.md): Revisor de Calidad de Código.
* [testing.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/testing.md): Ingeniero de Testing y QA.
* [devops.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/devops.md): Ingeniero DevOps y Despliegues.
* [documentation.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/agents/documentation.md): Redactor Técnico y Documentador.

---

## 4. Índice de Skills (`.claude/skills/`)
* [laravel-bll-dal.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/laravel-bll-dal.md): Implementación del patrón desacoplado.
* [auth.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/auth.md): Flujo de autenticación sin facades directos en el controller.
* [crud.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/crud.md): Creación estandarizada de operaciones CRUD.
* [datatables.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/datatables.md): Tablas con paginación server-side.
* [tailwind.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/tailwind.md): Directrices de diseño UI.
* [reports.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/reports.md): Agregaciones y reportes en DAL/BLL.
* [excel.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/excel.md): Exportación e importación segura.
* [notifications.md](file:///C:/Users/jealv/Documents/ejemplosPHP/MiSistema/.algoritmo/.claude/skills/notifications.md): Envío de correos y notificaciones con MailService.