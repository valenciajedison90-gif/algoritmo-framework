# Guía de Instalación y Despliegue — Algoritmo Framework

Esta guía explica cómo instalar y ejecutar el **Core de Algoritmo Framework** sobre un proyecto limpio de **Laravel 12**.

---

## Requisitos Previos
* **PHP:** >= 8.2 (con extensiones `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `sqlite3` o `pdo_mysql`)
* **Composer:** >= 2.5
* **Node.js & NPM:** (Opcional, la UI funciona directamente con Tailwind CDN)
* **Base de Datos:** SQLite, MySQL 8.0+, MariaDB 10.3+, PostgreSQL 14+ o Oracle Database

---

## Paso 1: Crear Proyecto Laravel 12 Limpio
En la terminal, cree una nueva instalación limpia de Laravel:
```bash
composer create-project laravel/laravel MiSistema "^12.0"
cd MiSistema
```

---

## Paso 2: Descargar o Clonar Algoritmo Framework
Clone el repositorio `algoritmo-framework` dentro de una carpeta temporal o subdirectorio `.algoritmo`:
```bash
git clone https://github.com/valenciajedison90-gif/algoritmo-framework.git .algoritmo
```

---

## Paso 3: Ejecutar el Instalador Oficial
Desde la raíz de su proyecto Laravel (`MiSistema`), ejecute:
```bash
php .algoritmo/install.php
```

El instalador ejecutará automáticamente los 10 pasos oficiales:
1. Verificación del archivo `artisan`.
2. Copia de carpetas de Clean Architecture (`app/`, `config/`, `database/`, `resources/`, `routes/`, `stubs/`, `.claude/`).
3. Fusión de configuraciones y variables de entorno en `.env`.
4. Actualización de autoloading en `composer.json`.
5. Actualización de scripts en `package.json`.
6. Optimización del cargador de clases (`composer dump-autoload -o`).
7. Verificación de dependencias frontend.
8. Generación de la clave criptográfica (`php artisan key:generate`).
9. Ejecución de migraciones y seeders (`php artisan migrate --seed`).
10. Despliegue de credenciales iniciales en consola.

---

## Paso 4: Iniciar el Servidor de Desarrollo
```bash
php artisan serve
```

Abra su navegador en:
```
http://127.0.0.1:8000/login
```

### Credenciales por Defecto
* **Super Administrador:**
  * Usuario: `admin@algoritmo.com`
  * Contraseña: `password123`
* **Operador Demo:**
  * Usuario: `operador@chec.com.co`
  * Contraseña: `password123`