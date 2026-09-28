# Algoritmo Framework — Core (Laravel 12 / PHP 8.2)

<p align="center">
  <strong>Framework Empresarial con Clean Architecture N-Capas (BLL / DAL / ADO)</strong>
</p>

---

## 🌟 Descripción General
**Algoritmo Framework** es un núcleo modular e independiente para proyectos empresariales construidos sobre **Laravel 12** y **PHP 8.2+**. Nace de la modernización arquitectónica de sistemas legados empresariales estructurados en BLL/DAL, transformándolos en una solución moderna con tipado estricto, alta escalabilidad, auditoría atómica, protección multi-inquilino y seguridad basada en OWASP Top 10.

---

## 🏛 Arquitectura Oficial

Toda petición sigue el flujo estricto e inquebrantable:

$$\text{FormRequest} \longrightarrow \text{Controller} \longrightarrow \text{ADO} \longrightarrow \text{BLL} \longrightarrow \text{DAL} \longrightarrow \text{Base de Datos}$$

* **FormRequest:** Validación exclusiva de datos de entrada y formatos.
* **Controllers:** Orquestan la petición; no contienen lógica de negocio ni sentencias SQL.
* **ADO (Application Data Object):** Transporta la información tipada entre capas (`BaseADO`).
* **BLL (Business Logic Layer):** Reglas de negocio, validaciones complejas y transacciones (`BaseBLL`).
* **DAL (Data Access Layer):** Consultas SQL 100% parametrizadas y paginación server-side (`BaseDAL`).
* **Blade Templates:** Renderizado visual declarativo con Tailwind CSS y DataTables.

---

## 📦 Estructura del Repositorio

```
algoritmo-framework/
├── install.php             # Instalador oficial ejecutable desde Laravel
├── build_framework.py      # Script Python para validación y empaquetado zip
├── VERSION                 # Versión del framework (2.0.0)
├── README.md               # Documentación general
│
├── src/                    # Herramientas del Instalador
│   ├── Installer.php       # Orquestador de los 10 pasos de instalación
│   ├── Console.php         # Salida en consola con colores ANSI
│   ├── Filesystem.php      # Copia recursiva de archivos y carpetas
│   ├── Composer.php        # Gestión de autoloading y dependencias
│   └── MergeConfig.php     # Fusión de .env, package.json y rutas
│
├── .claude/                # Claude SDK
│   ├── CLAUDE.md           # Índice maestro del SDK
│   ├── rules/              # 11 Reglas oficiales de diseño y seguridad
│   ├── agents/             # 9 Agentes especializados
│   └── skills/             # 8 Habilidades de desarrollo
│
├── app/
│   ├── ADO/                # Application Data Objects (BaseADO, ADOEmpresa, ADOUsuario, ADOLogin)
│   ├── BLL/                # Business Logic Layer (BaseBLL, EmpresaBLL, UsuarioBLL, LoginBLL)
│   ├── DAL/                # Data Access Layer (BaseDAL, EmpresaDAL, UsuarioDAL, LoginDAL)
│   ├── Core/               # Núcleo: ResponseHelper, BusinessException, SecurityService, AuditService, PermissionService, MailService, LogService
│   ├── Http/Controllers/  # EmpresaController, UsuarioController, Auth\LoginController
│   └── Http/Requests/     # EmpresaRequest, UsuarioRequest, LoginRequest, CambioPasswordRequest
│
├── config/                 # algoritmo.php
├── database/               # Migraciones y Seeders (empresas, users, audits, EmpresaSeeder, AdminSeeder)
├── resources/views/        # Vistas Blade: auth/login, empresas/*, usuarios/*, layouts/app, dashboard/index
├── routes/                 # web.php (Rutas RESTful protegidas)
├── stubs/                  # Plantillas para generación de nuevos módulos (ado, bll, dal, controller, request)
└── docs/                   # Documentación técnica: analisis-legado.md, arquitectura.md, INSTALACION.md
```

---

## 🚀 Instalación Rápida

### 1. Crear proyecto limpio Laravel 12
```bash
composer create-project laravel/laravel MiSistema "^12.0"
cd MiSistema
```

### 2. Clonar el Core de Algoritmo Framework
```bash
git clone https://github.com/valenciajedison90-gif/algoritmo-framework.git .algoritmo
```

### 3. Ejecutar el Instalador Oficial
```bash
php .algoritmo/install.php
```

### 4. Iniciar el Servidor
```bash
php artisan serve
```

Acceda a `http://127.0.0.1:8000/login`

---

## 🔑 Credenciales por Defecto

| Rol | Correo Electrónico | Contraseña |
| :--- | :--- | :--- |
| **Super Administrador** | `admin@algoritmo.com` | `password123` |
| **Operador Demo** | `operador@chec.com.co` | `password123` |

---

## 🛠 Empaquetado Automático (Python)

Para validar la integridad del Core y generar el archivo distribuible `algoritmo-framework.zip`:
```bash
python build_framework.py
```

---

## 📄 Licencia
Este framework es software propietario y confidencial desarrollado para Algoritmo.