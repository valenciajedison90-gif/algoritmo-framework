# Análisis del Proyecto Legado (PHP 7) y Estrategia de Migración a Laravel 12

## 1. Introducción y Contexto

El sistema analizado (`recuperacionphp7`) es una aplicación empresarial desarrollada originalmente para la **Central Hidroeléctrica de Caldas (CHEC - Grupo EPM)**, destinada a la gestión, trazabilidad y control de **solicitudes de recuperación de energía, órdenes de trabajo (OT) e inspección de medidores**.

El objetivo de este documento es detallar la estructura arquitectónica del código legado, sus convenciones, patrones de diseño, vulnerabilidades y el plan de migración técnica hacia el nuevo **Algoritmo Framework** sobre **Laravel 12** y **PHP 8.2+**.

---

## 2. Anatomía de la Arquitectura Legada (PHP 7)

El proyecto legado implementaba una variación de la arquitectura en capas empresariales (**N-Tier**), comúnmente observada en migraciones de aplicaciones .NET o Java hacia PHP procedural/OOP clásico:

```
recuperacionphp7/
├── App_code/
│   ├── BLL/                    # Capa de Lógica de Negocio (Business Logic Layer)
│   │   ├── META/               # Clases estructurales del framework (Controlador, Correo, Error, Seguridad)
│   │   ├── BLL_*.php           # Servicios de negocio especializados
│   │   └── MBLL_*.php          # Modelos de entidad / Data Transfer Objects
│   ├── DAL/                    # Capa de Acceso a Datos (Data Access Layer)
│   │   ├── META/               # Capa de conexión (MDAL_Model)
│   │   ├── DAL_*.php           # Consultas SQL especializadas y reportes
│   │   └── MDAL_*.php          # Operaciones CRUD base (insert, update, delete)
│   ├── UTL/                    # Utilidades generales y librerías externas (jQuery, jqGrid, autoNumeric)
│   └── VIE/                    # Capa de Presentación (Generadores de vistas HTML y plantillas)
├── App_Themes/                 # Hojas de estilo CSS e imágenes de jQuery UI
├── include.common.php          # Inclusión masiva de clases y librerías
├── web.conf.php                # Constantes de configuración y credenciales de base de datos
├── web.conf.correo.php         # Parámetros de envío SMTP
└── controller.php              # Punto de entrada único AJAX (Front Controller)
```

---

## 3. Desglose de Componentes Legados y Equivalencias

A continuación se presenta la matriz de equivalencia entre el sistema PHP 7 y la arquitectura moderna en **Algoritmo Framework (Laravel 12)**:

| Componente Legado (PHP 7) | Rol Original | Equivalente en Algoritmo Framework (Laravel 12) | Justificación y Mejora |
| :--- | :--- | :--- | :--- |
| **`MBLL_*`** | Clases con propiedades de entidad, getters y setters manuales. | **`app/ADO/ADO*.php`** | Se transforman en **Application Data Objects (ADO/DTO)** con tipos estrictos PHP 8.2, inmutabilidad opcional, métodos de serialización `toArray()`, `toJson()` y casting. |
| **`MDAL_*`** | Métodos CRUD elementales (`insert`, `update`, `getUIDSiguiente`). | **`app/ADO/` + `app/DAL/BaseDAL.php`** | Se eliminan las metaclases duplicadas. El transporte de datos recae en el ADO y el almacenamiento en métodos limpios del DAL con Eloquent/Query Builder parametrizado. |
| **`BLL_*`** | Funciones estáticas y clases que orquestaban reglas y lógica de negocio. | **`app/BLL/*BLL.php`** | Se estandarizan como servicios inyectables (`EmpresaBLL`, `UsuarioBLL`, `LoginBLL`), heredando de `BaseBLL`. Responden siempre con la estructura unificada `{"estado": bool, "mensaje": string, "datos": mixed}`. |
| **`DAL_*`** | Consultas SQL directas con ADOdb y llamadas a Oracle. | **`app/DAL/*DAL.php`** | Heredan de `BaseDAL`, encapsulando sentencias 100% parametrizadas con PDO/Query Builder, transacciones atómicas (`beginTransaction`, `commit`, `rollback`) y paginación para DataTables. |
| **`META`** (`MBLL_Controller`, `MDAL_Model`, `MBLL_Response`, `MBLL_Error`) | Núcleo del framework rudimentario para despachar comandos y formatear JSON. | **`app/Core/`** | Se reemplaza por un Core profesional: `ResponseHelper`, `BusinessException`, `AuditService`, `PermissionService`, `MailService`, `LogService`. |
| **`MBLL_Seguridad`** | Helper para capturar variables `$_POST` o `$_GET` y leer `$_SESSION['documento']`. | **`app/Core/SecurityService.php`** + **`FormRequest`** | Validación robusta vía `FormRequest` de Laravel, protección CSRF, sanitización estricta, rate-limiting y gestión segura de sesión sin lectura directa de superglobales. |
| **`UTL/funciones.php`** | Formateo artesanal de fechas, números y cadenas. | **`app/Core/Helpers/`** + Carbon / Str de Laravel | Métodos utilitarios modernos con soporte de localización, fechas ISO, Carbon, y manipulación tipada. |
| **`VIE_*` + Templates HTML** | Sustitución manual de `{token}` mediante `file_get_contents` y concatenación de cadenas. | **Blade Templates + Tailwind CSS + DataTables** | Vistas declarativas, componentes limpios, sin lógica incrustada, layout base unificado y renderizado reactivo moderno. |

---

## 4. Análisis Detallado por Componente

### 4.1. Manejo de Peticiones y Controladores
* **Legado:** Un único archivo `controller.php` recibía un comando (`COMANDO`) y parámetros (`PARAMETROS`) vía POST. `MBLL_Controller::_analizarAccion()` contenía un `switch` gigante de más de 800 líneas.
* **Migración:** Se implementan controladores RESTful específicos (`EmpresaController`, `UsuarioController`, `LoginController`). Cada acción cuenta con su propio método (`index`, `store`, `update`, `destroy`, `dataTable`) y su validación dedicada mediante `FormRequest`.

### 4.2. Flujo de Datos y Capas
El flujo oficial e inquebrantable en Algoritmo Framework es:

$$\text{FormRequest} \longrightarrow \text{Controller} \longrightarrow \text{ADO} \longrightarrow \text{BLL} \longrightarrow \text{DAL} \longrightarrow \text{Base de Datos}$$

1. **`FormRequest`**: Valida tipos, obligatoriedad, formatos y reglas de entrada antes de llegar al controlador.
2. **`Controller`**: Únicamente orquesta. Toma los datos validados del request, instancia o mapea hacia el **ADO**, invoca el **BLL** correspondiente y retorna una respuesta JSON o vista Blade formateada vía `ResponseHelper`.
3. **`ADO` (Application Data Object)**: Objeto tipado (DTO) que viaja entre capas, previniendo el paso de arrays asociativos no tipados o variables sueltas.
4. **`BLL` (Business Logic Layer)**: Ejecuta todas las reglas de negocio, validaciones complejas, cálculo de estados, verificación de permisos y auditoría.
5. **`DAL` (Data Access Layer)**: Única capa autorizada para interactuar con el motor de base de datos.
6. **`Database`**: Tablas relacionales con integridad referencial, llaves foráneas y tipos consistentes.

### 4.3. Acceso a Datos y Concurrencia
* **Legado:** Empleaba ADOdb sobre Oracle `oci8`. Para generar IDs consecutivos utilizaba:
  ```sql
  select nvl(max(consecutivo) + 1, 1) from CHCRE_LOTE_SOLICITUD
  ```
  Esto generaba condiciones de carrera severas en entornos de alta concurrencia.
* **Migración:** Uso de claves primarias autoincrementales estándar (`id BIGINT UNSIGNED AUTO_INCREMENT` o secuencias nativas), transacciones explícitas (`DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`) y bloqueos pesimistas u optimistas cuando se requiera.

### 4.4. Manejo de Errores y Excepciones
* **Legado:** Se creaba un objeto `new MBLL_Error($msg)` que se retornaba y evaluaba manualmente con `if ($datos instanceof MBLL_Error)`. Si el desarrollador olvidaba validar el tipo, el flujo continuaba en estado inconsistente.
* **Migración:** Adopción del paradigma de excepciones de dominio con **`BusinessException`**. Si una regla de negocio se viola, se lanza la excepción inmediatamente con un código de error y mensaje semántico, garantizando que transacciones abiertas hagan rollback automático en `BaseBLL`.

---

## 5. Vulnerabilidades Resueltas en la Migración

| Vulnerabilidad en PHP 7 | Causa Raíz en Legado | Solución en Algoritmo Framework (Laravel 12) |
| :--- | :--- | :--- |
| **Credenciales en código fuente** | Constantes con contraseñas en `web.conf.php` y `web.conf.correo.php`. | Variables de entorno en `.env` gestionadas por `config/*.php`. Ninguna clave en repositorio. |
| **Inyección SQL (SQLi)** | Concatenación directa de parámetros en `DAL_LoteSolicitud.php` y `DAL_DetalleLoteSolicitud.php`. | Sentencias preparadas universales con bindings de PDO en `BaseDAL`. Cero concatenación directa. |
| **Carga de Archivos Maliciosos** | `doajaxfileupload.php` subía sin verificar extensión ni contenido MIME. | Validación estricta en `FormRequest` (mimes, max size), almacenamiento aislado en `storage/app/` y nombres aleatorizados con UUID. |
| **Broken Access Control** | `controller.php` ejecutaba acciones sin validar la sesión `$_SESSION['documento']`. | Middlewares `auth`, protección CSRF automática y verificación de tenant/empresa activa en `SecurityService` y `LoginBLL`. |
| **Hardcoded Email Hijack** | `MBLL_Correo.php` forzaba todos los envíos a `fredygarvas@gmail.com`. | `MailService` dinámico con plantillas Blade y cola de correos (Queues), parametrizable por entorno. |
| **Headers Already Sent** | `verAdjunto_Recuperacion.php` hacía `echo` antes de emitir encabezados HTTP `header()`. | Flujo de respuestas binarias y descargas controlado mediante `response()->download()` o `response()->file()`. |

---

## 6. Convenciones de Nomenclatura del Framework

* **Entidades / ADO:** `app/ADO/ADO{Modulo}.php` (Ejemplo: `ADOEmpresa.php`, `ADOUsuario.php`).
* **Lógica de Negocio:** `app/BLL/{Modulo}BLL.php` (Ejemplo: `EmpresaBLL.php`, `UsuarioBLL.php`).
* **Acceso a Datos:** `app/DAL/{Modulo}DAL.php` (Ejemplo: `EmpresaDAL.php`, `UsuarioDAL.php`).
* **Controladores:** `app/Http/Controllers/{Modulo}Controller.php`.
* **Form Requests:** `app/Http/Requests/{Modulo}Request.php`.
* **Servicios de Soporte:** `app/Core/{Servicio}Service.php`.
* **Formato de Respuesta API / JSON:**
  ```json
  {
    "estado": true,
    "mensaje": "Operación ejecutada exitosamente.",
    "datos": {}
  }
  ```
