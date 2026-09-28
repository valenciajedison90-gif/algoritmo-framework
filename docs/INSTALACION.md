# Guía de Instalación y Despliegue — Algoritmo Framework

Esta guía explica cómo instalar, desplegar y configurar el **Core de Algoritmo Framework** sobre un proyecto limpio de **Laravel 12**.

El sistema cuenta con un **Doble Método de Instalación**:
1. **Asistente de Instalación Web Interactivo (Estilo Moodle / WordPress):** Detecta automáticamente si la base de datos está conectada. Si no lo está, la primera pantalla que abre el sistema no es el login, sino un asistente guiado paso a paso para diagnosticar el servidor, ingresar credenciales de BD, probar la conexión en tiempo real, migrar tablas y crear la cuenta administradora.
2. **Instalador de Consola Automatizado (CLI):** Diseñado para pipelines CI/CD o desarrolladores que prefieren línea de comandos.

---

## Requisitos del Servidor
* **PHP:** >= 8.2 (extensiones `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`)
* **Controlador de Base de Datos:** `pdo_mysql` (MySQL/MariaDB), `pdo_pgsql` (PostgreSQL), `pdo_sqlite` (SQLite) o `pdo_sqlsrv` (SQL Server)
* **Permisos de Escritura:** `.env`, `storage/`, `bootstrap/cache/`
* **Composer:** >= 2.5

---

## Método 1: Instalación Web Asistida (Recomendado — Estilo Moodle)

### Paso 1: Clonar y copiar el Core sobre Laravel 12
En la raíz de su proyecto Laravel 12:
```bash
# Clonar el framework dentro de .algoritmo (o descargarlo)
git clone https://github.com/valenciajedison90-gif/algoritmo-framework.git .algoritmo

# Sincronizar los archivos del Core
php .algoritmo/install.php
```

### Paso 2: Iniciar el servidor web
```bash
php artisan serve
```

### Paso 3: Abrir en el navegador
Visite en su navegador:
```
http://127.0.0.1:8000
```

> **Detección Automática:**  
> Gracias al middleware `EnsureSystemIsInstalled`, el sistema verifica si la base de datos está operativa y si existe el archivo de bloqueo `storage/framework/installed.lock`. Si la base de datos no está conectada o no existen las tablas, **será redirigido automáticamente a `/install` en lugar de mostrar un error 500 o pedir un login inaccesible**.

### Pasos del Asistente Web:
1. **Diagnóstico del Entorno:**
   - Verifica versión de PHP (>= 8.2), extensiones requeridas y permisos de escritura en carpetas.
2. **Configuración y Prueba de Base de Datos en Tiempo Real:**
   - Permite seleccionar el motor (MySQL / MariaDB, PostgreSQL, SQLite, SQL Server).
   - Ingreso de Host, Puerto, Nombre de BD, Usuario y Contraseña.
   - Botón **"⚡ Probar Conexión"**: Ejecuta una verificación AJAX en vivo con PDO antes de guardar.
   - Opción para crear la base de datos automáticamente en el servidor si no existe.
   - Guarda los parámetros directamente en el archivo `.env`.
3. **Inicialización y Cuenta Administrador:**
   - Registra los datos de la Empresa Principal (Nombre, NIT, Teléfono, Correo).
   - Registra el usuario Super Administrador (Nombre, Documento, Correo, Contraseña).
   - Ejecuta las migraciones de Laravel para crear las tablas (`empresas`, `users`, `audits`).
   - Inserta los registros iniciales utilizando la arquitectura estricta (ADO -> BLL -> DAL).
4. **Finalización y Bloqueo:**
   - Genera el archivo de seguridad `storage/framework/installed.lock` para deshabilitar el instalador.
   - Redirige al inicio de sesión (`/login`).

---

## Método 2: Instalación Totalmente Desatendida por CLI

Si ya configuró sus variables de base de datos en `.env`:
```bash
php .algoritmo/install.php
```
El script ejecutará las migraciones y seeders por consola y dejará el sistema listo para operar con las credenciales por defecto:

* **Super Administrador:**
  * Usuario: `admin@algoritmo.com`
  * Contraseña: `admin123`
* **Dashboard:** `http://127.0.0.1:8000/dashboard`

---

## Reconfigurar o Reinstalar el Sistema

Si en algún momento necesita volver a ejecutar el Asistente de Instalación Web:
1. Elimine el archivo de bloqueo:
   ```bash
   # En Windows PowerShell
   Remove-Item storage/framework/installed.lock

   # En Linux / macOS
   rm storage/framework/installed.lock
   ```
2. Abra `http://127.0.0.1:8000/install` en su navegador.