# Regla Frontend: Vistas Blade, Tailwind CSS y DataTables

## Principios de Presentación
1. **Blade Libre de Lógica:**
   * Las vistas Blade son puramente declarativas.
   * Prohibido ejecutar consultas de base de datos o llamadas BLL dentro de `@php ... @endphp`.
   * Toda la data debe llegar inyectada desde el Controlador.
2. **Diseño y Estilos:**
   * Utilizar **Tailwind CSS** para todo el diseño responsive y dark mode.
   * Diseños accesibles, componentes limpios (Cards, Tablas, Modales, Formularios con labels claros).
3. **DataTables Server-Side:**
   * Todas las tablas de listados masivos deben implementar jQuery DataTables con procesamiento del lado del servidor (`serverSide: true`, `processing: true`).
   * Las columnas deben disponer de renderers que manejen valores nulos con `-` o textos informativos.
4. **Acciones Asíncronas (AJAX / Fetch):**
   * Toda petición mutante (POST, PUT, PATCH, DELETE) debe enviar el token CSRF en la cabecera `X-CSRF-TOKEN: '{{ csrf_token() }}'` y `Accept: application/json`.
   * El feedback visual de éxito o error debe desplegarse dinámicamente sin recargar la página completa cuando se trabaje vía AJAX.