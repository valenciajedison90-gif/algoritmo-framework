# Regla de Interfaz de Usuario (UI): Experiencia de Usuario y Componentes

## Directivas de Interfaz
1. **Consistencia Visual:**
   * Utilizar la paleta de colores oficial: Índigo (`indigo-600`) para acciones principales, Esmeralda (`emerald-500`) para estados activos y confirmaciones, Rojo (`red-500`) para eliminaciones y alertas críticas.
2. **Jerarquía Visual y Tipografía:**
   * Títulos principales en `text-2xl font-bold text-gray-900 dark:text-white`.
   * Subtítulos explicativos en `text-sm text-gray-500 dark:text-gray-400`.
   * Etiquetas de campos en `block text-sm font-medium text-gray-700 dark:text-gray-300`.
3. **Feedback Inmediato:**
   * Todo botón de acción debe incluir transiciones suaves (`transition duration-150 ease-in-out`) y estados `hover`, `focus` y `active`.
   * Las operaciones destructivas deben solicitar confirmación antes de ejecutarse.
4. **Diseño Responsivo:**
   * Todas las vistas deben adaptarse naturalmente a pantallas móviles, tablets y monitores de escritorio mediante clases utilitarias de Tailwind (`sm:`, `md:`, `lg:`).
