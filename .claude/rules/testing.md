# Regla de Testing: Pruebas Unitarias y de Integración

## Estrategia de Pruebas
1. **Pruebas de la Capa BLL (Unit Testing):**
   * Validar cada regla de negocio aislada usando mocks de la capa DAL o base de datos en memoria (SQLite `:memory:`).
   * Comprobar que ante violación de reglas se lancen excepciones `BusinessException` con el código HTTP y arreglo de errores esperado.
2. **Pruebas de la Capa DAL (Integration Testing):**
   * Verificar que las sentencias parametrizadas inserten, actualicen y retornen los objetos `ADO` con el casting correcto.
   * Probar que las transacciones fallen y hagan rollback de forma adecuada.
3. **Pruebas de Autenticación y Controladores (Feature Testing):**
   * Verificar el flujo completo del login: credenciales válidas, credenciales incorrectas, usuario inactivo, empresa inactiva.
   * Comprobar respuestas JSON con la estructura obligatoria:
     ```json
     {
       "estado": true,
       "mensaje": "...",
       "datos": {}
     }
     ```
