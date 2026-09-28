# Manual de Uso Oficial — Algoritmo Framework v2.0.0
## Guía de Arquitectura, Instalación, Operación y Extensión de Módulos

---

## 1. Introducción y Filosofía del Framework

**Algoritmo Framework** es una arquitectura empresarial desacoplada construida sobre **Laravel 12** y **PHP 8.2+**, diseñada para modernizar aplicaciones heredadas desarrolladas en PHP 7 manteniendo una separación estricta de responsabilidades mediante **Clean Architecture en capas N-Tier**.

### Principios Fundamentales
1. **Desacoplamiento Absoluto:** El núcleo del framework (`.algoritmo`) es independiente del código de Laravel. No contiene carpetas como `vendor`, `node_modules`, `storage` ni `bootstrap`.
2. **Flujo Unidireccional Estricto:** Los datos se mueven en una sola dirección:
   $$\text{HTTP Request} \longrightarrow \text{FormRequest} \longrightarrow \text{Controller} \longrightarrow \text{ADO} \longrightarrow \text{BLL} \longrightarrow \text{DAL} \longrightarrow \text{Base de Datos}$$
3. **Cero Lógica de Negocio en Controladores ni Vistas:** Los controladores únicamente coordinan la entrada HTTP y delegan a la capa **BLL** (Business Logic Layer).
4. **Cero SQL en la Capa de Negocio:** La capa **BLL** jamás escribe sentencias SQL ni invoca directamente a la base de datos; delega exclusivamente a la capa **DAL** (Data Access Layer).
5. **Persistencia mediante PDO / DB::table:** No se utiliza Eloquent en la lógica de negocio para garantizar consultas SQL parametrizadas, de alto rendimiento y libres de inyección SQL.
6. **Estandarización de Respuestas:** Todas las operaciones retornan una estructura unificada:
   ```json
   {
       "estado": true,
       "mensaje": "Mensaje descriptivo para el usuario",
       "datos": { ... }
   }
   ```

---

## 2. Matriz de Equivalencia Arquitectónica

La siguiente tabla compara la arquitectura original en PHP 7 del proyecto legado (`recuperacionphp7`) frente a su implementación moderna en **Algoritmo Framework 2.0**:

| Componente Legado PHP 7 | Algoritmo Framework 2.0 | Responsabilidad y Patrón Aplicado |
| :--- | :--- | :--- |
| **`MBLL_*` / `MDAL_*`** | `app/ADO/` (`BaseADO`, `ADOEmpresa`, `ADOUsuario`, `ADOLogin`) | **DTO (Data Transfer Object):** Clases fuertemente tipadas con PHP 8.2, inmutabilidad parcial, `fromArray()` y `toArray()`. |
| **`BLL`** | `app/BLL/` (`BaseBLL`, `EmpresaBLL`, `UsuarioBLL`, `LoginBLL`) | **Lógica de Negocio:** Reglas de validación, orquestación transaccional (`ejecutarTransaccion()`) y auditoría. |
| **`DAL`** | `app/DAL/` (`BaseDAL`, `EmpresaDAL`, `UsuarioDAL`, `LoginDAL`) | **Acceso a Datos:** Ejecución de consultas parametrizadas con `DB::table()`, filtros dinámicos y paginación para DataTables. |
| **`META`** | `app/Core/` (`ResponseHelper`, `BusinessException`) | **Estandarización:** Formateo de respuestas JSON uniformes y captura de excepciones con códigos HTTP (400, 401, 403, 404, 422). |
| **`Helpers`** | `app/Core/Helpers/FormatHelper.php` | **Utilidades:** Formato de moneda, fechas, normalización de strings y sanitización XSS. |
| **`Seguridad`** | `app/Core/SecurityService.php` | **Criptografía:** Hashes seguros con Argon2id/Bcrypt, generación de tokens y políticas de contraseñas. |
| **N/A (Modernización)** | `app/Core/AuditService.php` | **Trazabilidad:** Registro automático de auditoría en la tabla `audits` (crear, actualizar, eliminar, login, logout). |
| **N/A (Modernización)** | `app/Core/InstallService.php` | **Instalador Inteligente:** Diagnóstico del servidor, prueba PDO en vivo, soporte multi-base de datos y auto-creación. |

---

## 3. Instalación Oficial en 4 Comandos

Para crear un nuevo proyecto empresarial (ERP, CRM o sistema a medida) con Algoritmo Framework, se ejecutan únicamente 4 comandos en la terminal:

```bash
# Paso 1: Crear una instalación limpia de Laravel 12
composer create-project laravel/laravel MiERP

# Paso 2: Ingresar a la carpeta del proyecto
cd MiERP

# Paso 3: Clonar el Core de Algoritmo Framework en la carpeta aislada .algoritmo
git clone https://github.com/valenciajedison90-gif/algoritmo-framework.git .algoritmo

# Paso 4: Ejecutar el instalador automatizado del Core
php .algoritmo/install.php
```

### ¿Qué realiza `install.php` en sus 10 pasos?
1. **Verificación de Entorno:** Confirma que el proyecto sea un Laravel válido mediante `artisan`.
2. **Sincronización de Capas:** Copia las carpetas `app/ADO`, `app/BLL`, `app/DAL`, `app/Core`, `app/Http`, `database`, `resources`, `routes`, `stubs` y `.claude`.
3. **Inyección de Middleware:** Registra `EnsureSystemIsInstalled` en `bootstrap/app.php` de Laravel 12.
4. **Fusión de Rutas Web:** Integra las rutas del instalador, login, dashboard, empresas y usuarios en `routes/web.php`.
5. **Configuración de Sesiones Seguras:** Establece `SESSION_DRIVER=file` y `CACHE_STORE=file` para impedir errores de bases de datos antes de que se ejecuten las migraciones.
6. **Optimización de Autoload:** Ejecuta `composer dump-autoload -o` para mapear los namespaces de Algoritmo.
7. **Verificación Frontend:** Comprueba los componentes visuales con Tailwind CSS y DataTables vía CDN.
8. **Generación de Application Key:** Ejecuta `php artisan key:generate`.
9. **Activación de Detección:** Deja el sistema listo para abrir el Asistente Web en la primera visita.
10. **Resumen de Instalación:** Imprime instrucciones detalladas para el desarrollador.

---

## 4. Asistente de Instalación Web Interactivo (Estilo Moodle)

Una vez ejecutado `install.php`, inicia el servidor de desarrollo:

```bash
php artisan serve
```

Y abre en tu navegador:
```
http://127.0.0.1:8000
```

### Detección Inteligente de Estado
El middleware `EnsureSystemIsInstalled` comprueba si el archivo `storage/framework/installed.lock` existe y si las tablas de base de datos están operativas. Al no detectar la base de datos instalada:
* **No muestra errores 500.**
* **No pide un login imposible.**
* **Redirige automáticamente a `/install`.**

```
                     http://127.0.0.1:8000 (Sin Base de Datos)
                                      │
                                      ▼
                        [ Paso 1: Diagnóstico ]
                        • Verifica PHP 8.2+
                        • Comprueba extensiones (PDO, mbstring, openssl, etc.)
                        • Valida permisos de disco (.env, storage/)
                                      │
                                      ▼
                   [ Paso 2: Conexión Multi-Base de Datos ]
                   • Selector de Motor: MySQL, MariaDB, Postgres, SQL Server, Oracle
                   • Campos: Host, Puerto, Base de Datos, Usuario, Clave
                   • Botón "⚡ Probar Conexión" (Prueba AJAX con PDO en vivo)
                   • Opción: "Crear base de datos automáticamente si no existe"
                   • Guarda parámetros directamente en el archivo .env
                                      │
                                      ▼
                   [ Paso 3: Inicialización y Administrador ]
                   • Registra Empresa Principal (Nombre, NIT, Teléfono, Correo)
                   • Registra Super Administrador (Nombre, Correo, Clave)
                   • Ejecuta migraciones de tablas (`empresas`, `users`, `audits`)
                   • Crea registros usando Clean Architecture (ADO ➔ BLL ➔ DAL)
                                      │
                                      ▼
                           [ Paso 4: Finalización ]
                   • Genera archivo de bloqueo storage/framework/installed.lock
                   • Deshabilita el instalador por seguridad
                   • Botón directo al Login (/login)
```

---

## 5. Motores de Base de Datos Soportados

El instalador web no depende de SQLite y admite 5 motores relacionales cliente-servidor empresariales:

| Motor | Puerto | Usuario | Cadena de Conexión PDO (DSN) |
| :--- | :---: | :---: | :--- |
| **MySQL Server** | `3306` | `root` | `mysql:host={host};port={port};dbname={database};charset=utf8mb4` |
| **MariaDB Enterprise** | `3306` | `root` | `mysql:host={host};port={port};dbname={database};charset=utf8mb4` |
| **PostgreSQL** | `5432` | `postgres` | `pgsql:host={host};port={port};dbname={database}` |
| **Microsoft SQL Server** | `1433` | `sa` | `sqlsrv:Server={host},{port};Database={database}` |
| **Oracle Database** | `1521` | `SYSTEM` | `oci:dbname=//{host}:{port}/{database};charset=AL32UTF8` |

---

## 6. Módulos Implementados en el Core

### 6.1 Módulo de Autenticación y Seguridad
* **`app/BLL/LoginBLL.php`:** Orquesta la autenticación validando credenciales contra `LoginDAL`, verifica si el usuario y su empresa están activos, actualiza la fecha de `ultimo_acceso` y registra el inicio de sesión en la tabla `audits`.
* **`app/DAL/LoginDAL.php`:** Consulta usuarios activos mediante consultas parametrizadas seguras.
* **`app/Core/SecurityService.php`:** Cifrado con Argon2id/Bcrypt y validación de contraseñas complejas.

### 6.2 Módulo de Empresas
* **`app/ADO/ADOEmpresa.php`:** DTO con `id`, `nit`, `razon_social`, `direccion`, `telefono`, `email`, `activo`.
* **`app/BLL/EmpresaBLL.php`:** Valida unicidad de NIT, controla transacciones y auditoría.
* **`app/DAL/EmpresaDAL.php`:** Operaciones CRUD y soporte de DataTables server-side con paginación y búsqueda.
* **`app/Http/Controllers/EmpresaController.php`:** Controlador RESTful sin lógica de base de datos.
* **`resources/views/empresas/`:** Vistas completas (`index`, `create`, `edit`, `show`) con Tailwind CSS y DataTables.

### 6.3 Módulo de Usuarios
* **`app/ADO/ADOUsuario.php`:** DTO con `id`, `empresa_id`, `name`, `email`, `documento`, `telefono`, `rol` (`admin`, `operador`, `auditor`), `activo`.
* **`app/BLL/UsuarioBLL.php`:** Valida unicidad de correo y documento, existencia de la empresa y hash de contraseña.
* **`app/DAL/UsuarioDAL.php`:** Persistencia, filtros por empresa y cambio de estado.
* **`resources/views/usuarios/`:** Vistas completas (`index`, `create`, `edit`, `show`, `cambiar-password`).

---

## 7. Generador Automatizado de Módulos CRUD (`php artisan algoritmo:crud`)

Algoritmo Framework v2.0.0 incorpora un potente generador de código por consola que construye módulos completos respetando el 100% de las capas de **Clean Architecture** en segundos.

### 7.1 Sintaxis del Comando

```bash
php artisan algoritmo:crud {name} [--table=nombre_tabla] [--force]
```

* `{name}`: Nombre del modelo en singular y PascalCase (ej: `Departamento`, `Municipio`, `Producto`, `Cliente`, `Proveedor`).
* `--table`: *(Opcional)* Nombre personalizado para la tabla en base de datos. Por defecto, pluraliza en snake_case (ej: `departamentos`, `municipios`).
* `--force`: *(Opcional)* Sobrescribe los archivos existentes si ya habían sido generados.

### 7.2 Los 8 Componentes Arquitectónicos Generados

Por cada módulo, el generador crea automáticamente:

| Capa / Componente | Ubicación del Archivo | Responsabilidad |
| :--- | :--- | :--- |
| **1. ADO (DTO)** | `app/ADO/ADO{Modelo}.php` | Objeto de transporte tipado con PHP 8.2, inmutable y con métodos `fromArray()` y `toArray()`. |
| **2. DAL (Data Access)** | `app/DAL/{Modelo}DAL.php` | Consultas SQL con `DB::table()`, filtros dinámicos y paginación server-side para DataTables. |
| **3. BLL (Business Logic)**| `app/BLL/{Modelo}BLL.php` | Capa de negocio, orquestación en transacciones ACID (`ejecutarTransaccion()`) y auditoría automática. |
| **4. FormRequest** | `app/Http/Requests/{Modelo}Request.php` | Validación tipada de peticiones HTTP antes de entrar a la lógica. |
| **5. Controller** | `app/Http/Controllers/{Modelo}Controller.php` | Controlador RESTful puro, sin sentencias SQL ni acoplamiento a sesión. |
| **6. Vistas Blade** | `resources/views/{plural}/*.blade.php` | Vistas completas (`index`, `create`, `edit`, `show`) estilizadas con Tailwind CSS y DataTables. |
| **7. Migración DDL** | `database/migrations/*_create_{tabla}_table.php` | Migración de Laravel con columnas tipadas, índices y llaves foráneas. |
| **8. Rutas Web** | `routes/web.php` | Registro automático del endpoint DataTable (`/{plural}/datatable`) y las rutas `Route::resource`. |

---

### 7.3 Caso Práctico 1: Generación del Módulo Departamentos

El módulo de Departamentos gestiona las divisiones territoriales de primer nivel con su código DANE y nombre oficial.

#### Paso 1: Ejecutar el comando Artisan
```bash
php artisan algoritmo:crud Departamento
```

**Salida en consola:**
```text
   INFO  Generando módulo CRUD para 'Departamento' (Tabla: departamentos)...  

  ADODepartamento ............................................................. CREADO  
  DepartamentoDAL ............................................................. CREADO  
  DepartamentoBLL ............................................................. CREADO  
  DepartamentoRequest ......................................................... CREADO  
  DepartamentoController ...................................................... CREADO  
  Vistas Blade (departamentos) ............................................... CREADAS  
  Migración: 2026_09_28_create_departamentos_table.php ........................ CREADA  
  Rutas Web (departamentos) .............................................. REGISTRADAS  

   INFO  🎉 ¡Módulo CRUD para 'Departamento' generado con éxito!  

  ├── ADO:        app/ADO/ADODepartamento.php
  ├── DAL:        app/DAL/DepartamentoDAL.php
  ├── BLL:        app/BLL/DepartamentoBLL.php
  ├── Controller: app/Http/Controllers/DepartamentoController.php
  ├── Request:    app/Http/Requests/DepartamentoRequest.php
  ├── Vistas:     resources/views/departamentos/*.blade.php
  └── Rutas:      /departamentos (registrado en routes/web.php)
```

---

### 7.4 Caso Práctico 2: Generación del Módulo Municipios

El módulo de Municipios administra los municipios asociados a su respectivo departamento mediante una **llave foránea** (`departamento_id`).

#### Paso 1: Ejecutar el comando Artisan
```bash
php artisan algoritmo:crud Municipio
```

**Salida en consola:**
```text
   INFO  Generando módulo CRUD para 'Municipio' (Tabla: municipios)...  

  ADOMunicipio ................................................................ CREADO  
  MunicipioDAL ................................................................ CREADO  
  MunicipioBLL ................................................................ CREADO  
  MunicipioRequest ............................................................ CREADO  
  MunicipioController ......................................................... CREADO  
  Vistas Blade (municipios) .................................................. CREADAS  
  Migración: 2026_09_28_create_municipios_table.php ........................... CREADA  
  Rutas Web (municipios) ................................................. REGISTRADAS  

   INFO  🎉 ¡Módulo CRUD para 'Municipio' generado con éxito!  

  ├── ADO:        app/ADO/ADOMunicipio.php
  ├── DAL:        app/DAL/MunicipioDAL.php
  ├── BLL:        app/BLL/MunicipioBLL.php
  ├── Controller: app/Http/Controllers/MunicipioController.php
  ├── Request:    app/Http/Requests/MunicipioRequest.php
  ├── Vistas:     resources/views/municipios/*.blade.php
  └── Rutas:      /municipios (registrado en routes/web.php)
```

#### Ventajas y Relaciones Configuradas Automáticamente:
1. **Acceso a Datos Relacional (`MunicipioDAL`):**
   El método `dataTable()` y `listar()` incluye un `leftJoin('departamentos as d', 'm.departamento_id', '=', 'd.id')` para retornar la propiedad calculada `departamento_nombre`.
2. **Formularios con Selector Reactivo:**
   Tanto en `resources/views/municipios/create.blade.php` como en `edit.blade.php`, se genera un menú desplegable `<select name="departamento_id">` precargado con los departamentos registrados.
3. **Búsqueda Avanzada Multi-campo:**
   En la tabla interactiva de municipios, el campo de búsqueda filtra simultáneamente por nombre de municipio, código DANE o nombre del departamento.
4. **Integridad Referencial:**
   La migración define `$table->foreignId('departamento_id')->constrained('departamentos')->onDelete('cascade');`, garantizando que no queden registros huérfanos.

---

### 7.5 Aplicación de Migraciones en Base de Datos

Una vez generados los módulos, ejecuta las migraciones de Laravel para crear las tablas físicas en el motor de base de datos configurado (MySQL, MariaDB, PostgreSQL, etc.):

```bash
php artisan migrate
```

Tablas resultantes:
* `departamentos` (`id`, `codigo`, `nombre`, `activo`, `created_at`, `updated_at`).
* `municipios` (`id`, `departamento_id`, `codigo`, `nombre`, `activo`, `created_at`, `updated_at`).

---

### 7.6 Navegación e Interfaz en el Dashboard

El Sidebar de la aplicación (`resources/views/layouts/app.blade.php`) detecta dinámicamente las rutas recién generadas mediante directivas `@if(Route::has('departamentos.index'))` y `@if(Route::has('municipios.index'))`, mostrando los enlaces en el menú lateral:

* **Departamentos:** `http://127.0.0.1:8000/departamentos`
* **Municipios:** `http://127.0.0.1:8000/municipios`

Cada módulo dispone de:
* Búsqueda en vivo y paginación asíncrona por DataTables.
* Creación y edición con validación de campos requeridos y toasts de notificación.
* Modales de confirmación para borrado seguro con método `DELETE`.
* Trazabilidad completa: cada operación queda registrada en la tabla `audits` (`audits.tabla`, `audits.operacion`, `audits.datos_anteriores`, `audits.datos_nuevos`).

---

## 8. Guía de Construcción Manual de Módulos (Enfoque Artesanal)

Si deseas construir un módulo de forma completamente manual (por ejemplo, **Productos**), sigue siempre el orden estricto de la arquitectura:

### Paso 8.1: Crear la Migración de Base de Datos
Crea el archivo `database/migrations/2026_01_01_000004_create_productos_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 150);
            $table->decimal('precio', 12, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
```

### Paso 8.2: Crear el DTO (`ADOProducto`)
Crea el archivo `app/ADO/ADOProducto.php`:
```php
<?php

declare(strict_types=1);

namespace App\ADO;

class ADOProducto extends BaseADO
{
    public function __construct(
        public ?int $id = null,
        public ?int $empresa_id = null,
        public string $codigo = '',
        public string $nombre = '',
        public float $precio = 0.0,
        public int $stock = 0,
        public bool $activo = true,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromArray(array $datos): static
    {
        return new static(
            id: isset($datos['id']) ? (int) $datos['id'] : null,
            empresa_id: isset($datos['empresa_id']) ? (int) $datos['empresa_id'] : null,
            codigo: (string) ($datos['codigo'] ?? ''),
            nombre: (string) ($datos['nombre'] ?? ''),
            precio: (float) ($datos['precio'] ?? 0.0),
            stock: (int) ($datos['stock'] ?? 0),
            activo: isset($datos['activo']) ? (bool) $datos['activo'] : true,
            created_at: isset($datos['created_at']) ? (string) $datos['created_at'] : null,
            updated_at: isset($datos['updated_at']) ? (string) $datos['updated_at'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### Paso 8.3: Crear la Capa de Datos (`ProductoDAL`)
Crea el archivo `app/DAL/ProductoDAL.php`:
```php
<?php

declare(strict_types=1);

namespace App\DAL;

use App\ADO\ADOProducto;
use Illuminate\Support\Facades\DB;

class ProductoDAL extends BaseDAL
{
    protected string $tabla = 'productos';

    public function obtenerPorId(int $id): ?ADOProducto
    {
        $registro = DB::table($this->tabla)->where('id', $id)->first();
        return $registro ? ADOProducto::fromArray((array) $registro) : null;
    }

    public function obtenerPorCodigo(string $codigo): ?ADOProducto
    {
        $registro = DB::table($this->tabla)->where('codigo', $codigo)->first();
        return $registro ? ADOProducto::fromArray((array) $registro) : null;
    }

    public function crear(ADOProducto $ado): int
    {
        $datos = $ado->toArray();
        unset($datos['id']);
        $datos['created_at'] = now()->toDateTimeString();
        $datos['updated_at'] = now()->toDateTimeString();

        return (int) DB::table($this->tabla)->insertGetId($datos);
    }

    public function actualizar(ADOProducto $ado): bool
    {
        $datos = $ado->toArray();
        unset($datos['id'], $datos['created_at']);
        $datos['updated_at'] = now()->toDateTimeString();

        return DB::table($this->tabla)->where('id', $ado->id)->update($datos) >= 0;
    }

    public function dataTable(array $params): array
    {
        $query = DB::table($this->tabla);
        $total = $query->count();

        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', $search)
                  ->orWhere('codigo', 'LIKE', $search);
            });
        }

        $filtered = $query->count();
        $start = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);

        $datos = $query->offset($start)->limit($length)->get();

        return [
            'draw' => (int) ($params['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $datos,
        ];
    }
}
```

### Paso 8.4: Crear la Capa de Negocio (`ProductoBLL`)
Crea el archivo `app/BLL/ProductoBLL.php`:
```php
<?php

declare(strict_types=1);

namespace App\BLL;

use App\ADO\ADOProducto;
use App\Core\AuditService;
use App\Core\LogService;
use App\DAL\ProductoDAL;

class ProductoBLL extends BaseBLL
{
    public function __construct(
        LogService $logger,
        AuditService $auditor,
        protected ProductoDAL $dal
    ) {
        parent::__construct($logger, $auditor);
    }

    public function obtener(int $id): array
    {
        $producto = $this->dal->obtenerPorId($id);
        if (!$producto) {
            return $this->respuestaError("El producto no existe.", 404);
        }
        return $this->respuestaExitosa("Producto recuperado con éxito.", $producto);
    }

    public function guardar(ADOProducto $ado): array
    {
        return $this->ejecutarTransaccion(function () use ($ado) {
            // Regla: Código único
            $existente = $this->dal->obtenerPorCodigo($ado->codigo);
            if ($existente && $existente->id !== $ado->id) {
                $this->lanzarExcepcion("El código '{$ado->codigo}' ya está en uso.", 422);
            }

            if ($ado->id === null || $ado->id === 0) {
                $id = $this->dal->crear($ado);
                $ado->id = $id;
                $this->auditor->registrar('PRODUCTOS', 'CREAR', $id, null, $ado->toArray());
                return $this->respuestaExitosa("Producto creado correctamente.", $ado);
            }

            $anterior = $this->dal->obtenerPorId($ado->id);
            $this->dal->actualizar($ado);
            $this->auditor->registrar('PRODUCTOS', 'ACTUALIZAR', $ado->id, $anterior?->toArray(), $ado->toArray());
            return $this->respuestaExitosa("Producto actualizado correctamente.", $ado);
        });
    }

    public function dataTable(array $params): array
    {
        return $this->dal->dataTable($params);
    }
}
```

### Paso 8.5: Crear el FormRequest de Validación
Crea el archivo `app/Http/Requests/ProductoRequest.php`:
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('producto');

        return [
            'empresa_id' => 'required|integer',
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }
}
```

### Paso 8.6: Crear el Controlador (`ProductoController`)
Crea el archivo `app/Http/Controllers/ProductoController.php`:
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ADO\ADOProducto;
use App\BLL\ProductoBLL;
use App\Core\ResponseHelper;
use App\Http\Requests\ProductoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function __construct(
        protected ProductoBLL $bll
    ) {}

    public function index(): View
    {
        return view('productos.index');
    }

    public function dataTable(Request $request): JsonResponse
    {
        $resultado = $this->bll->dataTable($request->all());
        return response()->json($resultado);
    }

    public function store(ProductoRequest $request): JsonResponse
    {
        $ado = ADOProducto::fromArray($request->validated());
        $respuesta = $this->bll->guardar($ado);

        return ResponseHelper::success($respuesta['mensaje'], $respuesta['datos'], 201);
    }
}
```

### Paso 8.7: Registrar las Rutas Web
En `routes/web.php` (dentro del grupo protegido `auth`):
```php
Route::get('/productos/datatable', [ProductoController::class, 'dataTable'])->name('productos.datatable');
Route::resource('productos', ProductoController::class);
```

---

## 9. Claude SDK y Herramientas del Desarrollador (`.claude/`)

El repositorio incluye un conjunto integral de directrices para desarrollo asistido por IA:

* **11 Reglas de Arquitectura (`.claude/rules/`):**
  * `architecture.md`: Reglas del flujo unidireccional estricto y desacoplamiento.
  * `backend.md`: Prohibición de Eloquent en BLL y parámetros tipados en PHP 8.2+.
  * `database.md`: Consultas parametrizadas obligatorias.
  * `security.md`: Prevención de XSS, CSRF e inyección SQL.
  * `frontend.md`: Uso de Tailwind CSS y DataTables.
  * `auditing.md`: Directrices de trazabilidad en la tabla `audits`.
* **9 Agentes Especializados (`.claude/agents/`):**
  * `architect.md`, `backend.md`, `frontend.md`, `database.md`, `security.md`, `reviewer.md`, `testing.md`, `devops.md`, `documentation.md`.
* **8 Habilidades de Codificación (`.claude/skills/`):**
  * `laravel-bll-dal.md`, `auth.md`, `crud.md`, `datatables.md`, `tailwind.md`, `reports.md`, `excel.md`, `notifications.md`.
* **Generador de Plantillas (`stubs/`):**
  * `ado.stub`, `bll.stub`, `dal.stub`, `controller.stub`, `request.stub`.

---

## 10. Preguntas Frecuentes y Solución de Problemas

### ¿Cómo reinstalo o cambio de base de datos?
Simplemente elimina el archivo de bloqueo:
```powershell
Remove-Item storage/framework/installed.lock
```
Y abre `http://127.0.0.1:8000/`. El sistema volverá a abrir el Asistente Web para que selecciones otro motor o actualices tus credenciales.

### ¿Por qué nunca se debe invocar `Auth::user()` en los Controladores?
Para mantener la arquitectura pura y reutilizable (por ejemplo, desde comandos CLI, APIs o tareas programadas), la capa BLL debe recibir el ID del usuario o el contexto explícitamente mediante el DTO (ADO), desacoplando la lógica de la sesión HTTP.

### ¿Cómo se empaqueta el Core para distribuirlo como archivo ZIP?
Ejecuta en la raíz de `.algoritmo`:
```bash
python build_framework.py
```
Esto creará el paquete `algoritmo-framework.zip` verificado y libre de archivos temporales o librerías de terceros.
