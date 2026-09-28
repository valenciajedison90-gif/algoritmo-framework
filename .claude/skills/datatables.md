# Skill: Integración de DataTables Server-Side

## Patrón de Implementación
Para garantizar alto rendimiento con millones de registros sin saturar la memoria del servidor:

### 1. Parámetros del Request de DataTables
El cliente envía vía GET:
* `draw`: Contador de peticiones para sincronización asíncrona.
* `start`: Índice inicial (offset).
* `length`: Cantidad de filas por página (limit).
* `search[value]`: Cadena de búsqueda global.
* `order[0][column]` y `order[0][dir]`: Columna y dirección de ordenamiento.

### 2. Implementación en la Capa DAL
```php
public function dataTable(array $params): array
{
    $draw = (int) ($params['draw'] ?? 1);
    $start = (int) ($params['start'] ?? 0);
    $length = (int) ($params['length'] ?? 10);
    $search = (string) ($params['search']['value'] ?? '');

    $query = $this->table($this->tabla);
    $totalRecords = $query->count();

    if ($search !== '') {
        $query->where('nombre', 'like', "%{$search}%");
    }

    $filteredRecords = $query->count();
    $data = $query->offset($start)->limit($length)->get();

    return [
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data->toArray(),
    ];
}
```

### 3. Configuración en la Vista Blade
```javascript
$('#mi-tabla').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/mi-ruta/datatable',
    columns: [
        { data: 'id' },
        { data: 'nombre' },
        { data: 'created_at' }
    ]
});
```
