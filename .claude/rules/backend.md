# Regla Backend: Estándares de Codificación PHP 8.2+ y Laravel 12

## Estándares del Código PHP
1. **Tipado Estricto Obligatorio:** Todos los archivos PHP deben iniciar con:
   ```php
   <?php
   declare(strict_types=1);
   ```
2. **Cumplimiento PSR:** Se debe seguir estrictamente **PSR-12** (formato y espaciado), **PSR-4** (autoloading) y convenciones PSR para interfaces.
3. **Inyección de Dependencias (DI):** Los controladores y servicios reciben sus dependencias vía constructor. Nunca instanciar clases BLL o DAL con `new` dentro de métodos de acción.
4. **Manejo de Excepciones de Negocio:**
   * Utilizar siempre `BusinessException` cuando se infrinja una regla de negocio.
   * Nunca devolver códigos de error mágicos o strings vacíos.
5. **Estructura Uniforme de Respuestas:**
   * Toda respuesta JSON generada por la API o devuelta por BLL debe respetar:
     ```php
     [
         'estado' => (bool),
         'mensaje' => (string),
         'datos' => (mixed),
     ]
     ```
6. **Desacoplamiento de Eloquent:**
   * No mezclar Active Record de Eloquent dentro de controladores.
   * La persistencia y mapeo de datos se realiza a través de la capa `DAL` retornando instancias de `ADO`.