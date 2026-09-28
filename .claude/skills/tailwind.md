# Skill: Estilizado con Tailwind CSS y Diseño Empresarial

## Directrices de Diseño UI con Tailwind
1. **Layout y Estructura:**
   * Utilizar flexbox y CSS Grid para alineaciones (`flex flex-col md:flex-row`, `grid grid-cols-1 md:grid-cols-2 gap-6`).
2. **Cards y Contenedores:**
   * `bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6`.
3. **Botones de Acción:**
   * **Primario:** `px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition shadow-sm`.
   * **Secundario / Cancelar:** `px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition`.
   * **Peligro / Eliminar:** `px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition`.
4. **Badges de Estado:**
   * **Activo:** `px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400`.
   * **Inactivo:** `px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400`.
