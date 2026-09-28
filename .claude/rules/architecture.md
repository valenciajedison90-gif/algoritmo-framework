# Regla de Arquitectura: Clean Architecture N-Capas (Algoritmo Framework)

## Principio Fundamental
El sistema sigue de manera inquebrantable el flujo unidireccional desacoplado:

$$\text{FormRequest} \longrightarrow \text{Controller} \longrightarrow \text{ADO} \longrightarrow \text{BLL} \longrightarrow \text{DAL} \longrightarrow \text{Base de Datos}$$

## Responsabilidades por Capa

### 1. FormRequest (`app/Http/Requests/`)
* Valida sintaxis, formatos, rangos, tipos y obligatoriedad de los datos de entrada.
* Define mensajes de error en español amigables para el usuario.
* Nunca ejecuta lógica de negocio ni consultas a base de datos complejas.

### 2. Controller (`app/Http/Controllers/`)
* Únicamente orquesta la petición HTTP.
* Extrae los datos validados del `FormRequest`.
* Instancia el objeto `ADO` correspondiente.
* Invoca el método pertinente en la capa `BLL`.
* Retorna la vista Blade o la respuesta JSON según la cabecera `Accept` o requerimiento.
* **Prohibido:** No contiene sentencias SQL, consultas con Eloquent, reglas de negocio ni autenticación directa mediante facades como `Auth`.

### 3. ADO - Application Data Object (`app/ADO/`)
* Objeto de transferencia tipado (Data Transfer Object) para PHP 8.2+.
* Hereda de `BaseADO`.
* Contiene propiedades públicas tipadas, método estático `fromArray(array $datos)` y método `toArray(): array`.
* Unifica los antiguos `MBLL_*` y `MDAL_*` del legado PHP 7.

### 4. BLL - Business Logic Layer (`app/BLL/`)
* Reside el 100% de las reglas y validaciones de negocio del dominio.
* Hereda de `BaseBLL`.
* Orquesta transacciones atómicas seguras mediante `ejecutarTransaccion()`.
* Valida unicidad semántica, estados permitidos, reglas multi-tenant y coherencia.
* Emite auditoría a través de `AuditService`.
* Retorna siempre la estructura estándar obligatoria:
  ```json
  {
    "estado": true,
    "mensaje": "Mensaje explicativo",
    "datos": {}
  }
  ```

### 5. DAL - Data Access Layer (`app/DAL/`)
* Es la **única capa autorizada** para interactuar con el motor de base de datos.
* Hereda de `BaseDAL`.
* Utiliza sentencias 100% parametrizadas con PDO / Query Builder.
* Controla transacciones (`beginTransaction`, `commit`, `rollback`).
* Maneja paginación y búsqueda server-side para DataTables.

### 6. Base de Datos
* Tablas con nombres en plural en minúsculas (ej: `empresas`, `users`, `audits`).
* Llaves primarias autoincrementales (`id BIGINT UNSIGNED`).
* Integridad referencial con restricciones foráneas y campos `created_at` / `updated_at`.