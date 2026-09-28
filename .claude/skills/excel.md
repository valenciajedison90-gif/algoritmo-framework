# Skill: Exportación e Importación de Archivos Excel y CSV

## Directrices para Exportación / Importación
1. **Flujo de Extracción de Datos:**
   * La consulta de registros a exportar se delega a `DAL`, utilizando generadores o cursores (`cursor()`, `chunk()`) para evitar agotar la memoria RAM con datasets masivos.
2. **Formateo y Sanitización:**
   * Los campos numéricos, fechas e importes monetarios se formatean a través de `FormatHelper`.
   * Proteger contra **CSV Injection**: escapar caracteres peligrosos al inicio de celdas (`=`, `+`, `-`, `@`).
3. **Descarga Segura:**
   * Las descargas de archivos binarios deben responder mediante `response()->streamDownload()` o `response()->download()` con encabezados MIME adecuados (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` o `text/csv; charset=UTF-8`).
