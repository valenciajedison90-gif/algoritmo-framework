<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Comando Artisan para generar módulos CRUD completos bajo Clean Architecture
 *
 * Genera automáticamente:
 * - ADO (Data Transfer Object)
 * - DAL (Data Access Layer)
 * - BLL (Business Logic Layer con transacciones y auditoría)
 * - Controller (Orquestación HTTP limpia)
 * - FormRequest (Validación tipada)
 * - Vistas Blade (index con DataTables, create, edit, show)
 * - Migración de base de datos
 * - Registro automático en routes/web.php
 */
class MakeAlgoritmoCrud extends Command
{
    protected $signature = 'algoritmo:crud 
                            {name : Nombre del modelo en singular PascalCase (ej: Departamento, Municipio, Producto)}
                            {--table= : Nombre personalizado de la tabla en base de datos}
                            {--force : Sobrescribir archivos existentes si ya existen}';

    protected $description = 'Genera un módulo CRUD completo bajo Clean Architecture (ADO, BLL, DAL, Controller, Request, Vistas, Migración y Rutas)';

    public function handle(): int
    {
        $rawName = $this->argument('name');
        $modelName = Str::studly($rawName);
        $varName = Str::camel($modelName);
        $pluralName = Str::pluralStudly($modelName);
        $tableName = $this->option('table') ?: Str::snake(Str::pluralStudly($modelName));
        $viewPath = Str::kebab($pluralName);
        $force = (bool) $this->option('force');

        $this->components->info("Generando módulo CRUD para '{$modelName}' (Tabla: {$tableName})...");

        // 1. Generar ADO
        $this->generateAdo($modelName, $tableName, $force);

        // 2. Generar DAL
        $this->generateDal($modelName, $tableName, $force);

        // 3. Generar BLL
        $this->generateBll($modelName, $tableName, $force);

        // 4. Generar FormRequest
        $this->generateRequest($modelName, $tableName, $force);

        // 5. Generar Controller
        $this->generateController($modelName, $tableName, $varName, $viewPath, $force);

        // 6. Generar Vistas Blade (index, create, edit, show)
        $this->generateViews($modelName, $varName, $viewPath, $force);

        // 7. Generar Migración
        $this->generateMigration($modelName, $tableName);

        // 8. Registrar Rutas Web
        $this->registerRoutes($modelName, $viewPath);

        $this->newLine();
        $this->components->info("🎉 ¡Módulo CRUD para '{$modelName}' generado con éxito!");
        $this->line("  ├── ADO:        app/ADO/ADO{$modelName}.php");
        $this->line("  ├── DAL:        app/DAL/{$modelName}DAL.php");
        $this->line("  ├── BLL:        app/BLL/{$modelName}BLL.php");
        $this->line("  ├── Controller: app/Http/Controllers/{$modelName}Controller.php");
        $this->line("  ├── Request:    app/Http/Requests/{$modelName}Request.php");
        $this->line("  ├── Vistas:     resources/views/{$viewPath}/*.blade.php");
        $this->line("  └── Rutas:      /{$viewPath} (registrado en routes/web.php)");
        $this->newLine();
        $this->comment("👉 Ejecuta las migraciones para aplicar los cambios en la base de datos:");
        $this->info("   php artisan migrate");

        return self::SUCCESS;
    }

    protected function generateAdo(string $modelName, string $tableName, bool $force): void
    {
        $path = app_path("ADO/ADO{$modelName}.php");
        if (File::exists($path) && !$force) {
            $this->components->warn("ADO{$modelName}.php ya existe. Usa --force para sobrescribir.");
            return;
        }

        $extraProperties = "";
        $extraArrayMapping = "";
        $extraToArray = "";

        if ($modelName === 'Municipio') {
            $extraProperties = "\n        public ?int \$departamento_id = null,\n        public ?string \$codigo = null,\n        public ?string \$departamento_nombre = null,";
            $extraArrayMapping = "\n            departamento_id: isset(\$datos['departamento_id']) ? (int) \$datos['departamento_id'] : null,\n            codigo: isset(\$datos['codigo']) ? (string) \$datos['codigo'] : null,\n            departamento_nombre: isset(\$datos['departamento_nombre']) ? (string) \$datos['departamento_nombre'] : null,";
            $extraToArray = "\n            'departamento_id' => \$this->departamento_id,\n            'codigo' => \$this->codigo,\n            'departamento_nombre' => \$this->departamento_nombre,";
        } elseif ($modelName === 'Departamento') {
            $extraProperties = "\n        public ?string \$codigo = null,";
            $extraArrayMapping = "\n            codigo: isset(\$datos['codigo']) ? (string) \$datos['codigo'] : null,";
            $extraToArray = "\n            'codigo' => \$this->codigo,";
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\ADO;

/**
 * Objeto de Transporte de Datos (ADO) para {$modelName}.
 */
class ADO{$modelName} extends BaseADO
{
    public function __construct(
        public ?int \$id = null,
        public ?string \$nombre = null,{$extraProperties}
        public bool \$activo = true,
        public ?string \$created_at = null,
        public ?string \$updated_at = null,
    ) {}

    public static function fromArray(array \$datos): static
    {
        return new static(
            id: isset(\$datos['id']) ? (int) \$datos['id'] : null,
            nombre: isset(\$datos['nombre']) ? (string) \$datos['nombre'] : null,{$extraArrayMapping}
            activo: isset(\$datos['activo']) ? (bool) \$datos['activo'] : true,
            created_at: isset(\$datos['created_at']) ? (string) \$datos['created_at'] : null,
            updated_at: isset(\$datos['updated_at']) ? (string) \$datos['updated_at'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => \$this->id,
            'nombre' => \$this->nombre,{$extraToArray}
            'activo' => \$this->activo,
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];
    }
}
PHP;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->components->twoColumnDetail("ADO{$modelName}", "CREADO");
    }

    protected function generateDal(string $modelName, string $tableName, bool $force): void
    {
        $path = app_path("DAL/{$modelName}DAL.php");
        if (File::exists($path) && !$force) {
            $this->components->warn("{$modelName}DAL.php ya existe.");
            return;
        }

        $extraCrear = "";
        $extraActualizar = "";
        $idColumn = "id";
        $activeColumn = "activo";
        $queryJoins = "\$query = \$this->table(\$this->tabla);";
        $obtenerPorIdBody = "\$registro = \$this->table(\$this->tabla)->where('id', \$id)->first();";

        if ($modelName === 'Municipio') {
            $extraCrear = "\n            'departamento_id' => \$ado->departamento_id,\n            'codigo' => \$ado->codigo,";
            $extraActualizar = "\n            'departamento_id' => \$ado->departamento_id,\n            'codigo' => \$ado->codigo,";
            $idColumn = "m.id";
            $activeColumn = "m.activo";
            $queryJoins = "\$query = \$this->table(\$this->tabla . ' as m')\n            ->leftJoin('departamentos as d', 'm.departamento_id', '=', 'd.id')\n            ->select('m.*', 'd.nombre as departamento_nombre');";
            $obtenerPorIdBody = "{$queryJoins}\n        \$registro = \$query->where('m.id', \$id)->first();";
            $searchLogic = <<<PHP
        if (\$searchValue !== '') {
            \$like = '%' . \$searchValue . '%';
            \$query->where(function (\$q) use (\$like) {
                \$q->where('m.nombre', 'like', \$like)
                  ->orWhere('m.codigo', 'like', \$like)
                  ->orWhere('d.nombre', 'like', \$like);
            });
        }
PHP;
        } elseif ($modelName === 'Departamento') {
            $extraCrear = "\n            'codigo' => \$ado->codigo,";
            $extraActualizar = "\n            'codigo' => \$ado->codigo,";
            $searchLogic = <<<PHP
        if (\$searchValue !== '') {
            \$like = '%' . \$searchValue . '%';
            \$query->where(function (\$q) use (\$like) {
                \$q->where('nombre', 'like', \$like)
                  ->orWhere('codigo', 'like', \$like);
            });
        }
PHP;
        } else {
            $searchLogic = <<<PHP
        if (\$searchValue !== '') {
            \$like = '%' . \$searchValue . '%';
            \$query->where(function (\$q) use (\$like) {
                \$q->where('nombre', 'like', \$like);
            });
        }
PHP;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\DAL;

use App\ADO\ADO{$modelName};

/**
 * Data Access Layer para {$modelName}.
 */
class {$modelName}DAL extends BaseDAL
{
    protected string \$tabla = '{$tableName}';

    public function obtenerPorId(int \$id): ?ADO{$modelName}
    {
        {$obtenerPorIdBody}
        return \$registro ? ADO{$modelName}::fromArray((array) \$registro) : null;
    }

    public function listar(array \$filtros = []): array
    {
        {$queryJoins}
        if (isset(\$filtros['activo']) && \$filtros['activo'] !== '') {
            \$query->where('{$activeColumn}', (bool) \$filtros['activo']);
        }
        \$registros = \$query->orderBy('{$idColumn}', 'desc')->get();
        return array_map(fn(\$item) => ADO{$modelName}::fromArray((array) \$item), \$registros->toArray());
    }

    public function dataTable(array \$params): array
    {
        \$draw = (int) (\$params['draw'] ?? 1);
        \$start = (int) (\$params['start'] ?? 0);
        \$length = (int) (\$params['length'] ?? 10);
        \$searchValue = (string) (\$params['search']['value'] ?? '');

        {$queryJoins}
        \$totalRecords = \$this->table(\$this->tabla)->count();

{$searchLogic}

        \$filteredRecords = \$query->count();
        \$data = \$query->orderBy('{$idColumn}', 'desc')->offset(\$start)->limit(\$length)->get();

        return [
            'draw' => \$draw,
            'recordsTotal' => \$totalRecords,
            'recordsFiltered' => \$filteredRecords,
            'data' => \$data->toArray(),
        ];
    }

    public function crear(ADO{$modelName} \$ado): int
    {
        \$datos = [
            'nombre' => \$ado->nombre,{$extraCrear}
            'activo' => \$ado->activo ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        return \$this->insertGetId(\$this->tabla, \$datos);
    }

    public function actualizar(ADO{$modelName} \$ado): bool
    {
        \$datos = [
            'nombre' => \$ado->nombre,{$extraActualizar}
            'activo' => \$ado->activo ? 1 : 0,
            'updated_at' => now(),
        ];
        return (bool) \$this->update(\$this->tabla, ['id' => \$ado->id], \$datos);
    }

    public function eliminar(int \$id): bool
    {
        return (bool) \$this->delete(\$this->tabla, ['id' => \$id]);
    }
}
PHP;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->components->twoColumnDetail("{$modelName}DAL", "CREADO");
    }

    protected function generateBll(string $modelName, string $tableName, bool $force): void
    {
        $path = app_path("BLL/{$modelName}BLL.php");
        if (File::exists($path) && !$force) {
            $this->components->warn("{$modelName}BLL.php ya existe.");
            return;
        }

        $upperTable = strtoupper($tableName);

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\BLL;

use App\ADO\ADO{$modelName};
use App\Core\AuditService;
use App\Core\LogService;
use App\DAL\\{$modelName}DAL;

/**
 * Business Logic Layer para {$modelName}.
 */
class {$modelName}BLL extends BaseBLL
{
    public function __construct(
        LogService \$logger,
        AuditService \$auditor,
        protected {$modelName}DAL \$dal
    ) {
        parent::__construct(\$logger, \$auditor);
    }

    public function obtener(int \$id): array
    {
        \$registro = \$this->dal->obtenerPorId(\$id);
        if (!\$registro) {
            return \$this->respuestaError("El registro solicitado no existe.", ['id' => \$id]);
        }
        return \$this->respuestaExitosa("Registro obtenido correctamente.", \$registro);
    }

    public function listar(array \$filtros = []): array
    {
        \$registros = \$this->dal->listar(\$filtros);
        return \$this->respuestaExitosa("Listado obtenido correctamente.", \$registros);
    }

    public function dataTable(array \$params): array
    {
        return \$this->dal->dataTable(\$params);
    }

    public function guardar(ADO{$modelName} \$ado): array
    {
        return \$this->ejecutarTransaccion(function () use (\$ado) {
            if (\$ado->id === null || \$ado->id === 0) {
                \$id = \$this->dal->crear(\$ado);
                \$ado->id = \$id;
                \$this->auditor->registrar('{$upperTable}', 'CREAR', \$id, null, \$ado->toArray(), "{$modelName} creado");
                \$this->logger->info("{$modelName} creado con ID: {\$id}");
                return \$this->respuestaExitosa("{$modelName} creado exitosamente.", \$ado);
            }

            \$anterior = \$this->dal->obtenerPorId(\$ado->id);
            \$this->dal->actualizar(\$ado);
            \$this->auditor->registrar('{$upperTable}', 'ACTUALIZAR', \$ado->id, \$anterior?->toArray(), \$ado->toArray(), "{$modelName} actualizado");
            \$this->logger->info("{$modelName} actualizado con ID: {\$ado->id}");
            return \$this->respuestaExitosa("{$modelName} actualizado exitosamente.", \$ado);
        });
    }

    public function eliminar(int \$id): array
    {
        return \$this->ejecutarTransaccion(function () use (\$id) {
            \$registro = \$this->dal->obtenerPorId(\$id);
            if (!\$registro) {
                \$this->lanzarExcepcion("El registro no existe.", 404);
            }
            \$this->dal->eliminar(\$id);
            \$this->auditor->registrar('{$upperTable}', 'ELIMINAR', \$id, \$registro->toArray(), null, "{$modelName} eliminado");
            \$this->logger->info("{$modelName} eliminado con ID: {\$id}");
            return \$this->respuestaExitosa("{$modelName} eliminado correctamente.");
        });
    }
}
PHP;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->components->twoColumnDetail("{$modelName}BLL", "CREADO");
    }

    protected function generateRequest(string $modelName, string $tableName, bool $force): void
    {
        $path = app_path("Http/Requests/{$modelName}Request.php");
        if (File::exists($path) && !$force) {
            return;
        }

        $extraRules = "";
        if ($modelName === 'Municipio') {
            $extraRules = "\n            'departamento_id' => 'required|integer',\n            'codigo' => 'nullable|string|max:10',";
        } elseif ($modelName === 'Departamento') {
            $extraRules = "\n            'codigo' => 'nullable|string|max:10',";
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$modelName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',{$extraRules}
            'activo' => 'nullable|boolean',
        ];
    }
}
PHP;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->components->twoColumnDetail("{$modelName}Request", "CREADO");
    }

    protected function generateController(string $modelName, string $tableName, string $varName, string $viewPath, bool $force): void
    {
        $path = app_path("Http/Controllers/{$modelName}Controller.php");
        if (File::exists($path) && !$force) {
            return;
        }

        $extraFormData = "";
        if ($modelName === 'Municipio') {
            $extraFormData = "\n        \$departamentos = \Illuminate\Support\Facades\DB::table('departamentos')->where('activo', 1)->orderBy('nombre')->get();";
        }

        $compactVars = "\${$varName}";
        if ($modelName === 'Municipio') {
            $compactVars = "'{$varName}', 'departamentos'";
        } else {
            $compactVars = "'{$varName}'";
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ADO\ADO{$modelName};
use App\BLL\\{$modelName}BLL;
use App\Http\Requests\\{$modelName}Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class {$modelName}Controller extends Controller
{
    public function __construct(
        protected {$modelName}BLL \$bll
    ) {}

    public function index(): View
    {
        return view('{$viewPath}.index');
    }

    public function dataTable(Request \$request): JsonResponse
    {
        return response()->json(\$this->bll->dataTable(\$request->all()));
    }

    public function create(): View
    {
        \${$varName} = new ADO{$modelName}();{$extraFormData}
        return view('{$viewPath}.create', compact({$compactVars}));
    }

    public function store({$modelName}Request \$request): JsonResponse|RedirectResponse
    {
        \$ado = ADO{$modelName}::fromArray(\$request->validated());
        \$respuesta = \$this->bll->guardar(\$ado);

        if (\$request->wantsJson()) {
            return response()->json(\$respuesta, \$respuesta['estado'] ? 201 : 422);
        }

        if (!\$respuesta['estado']) {
            return back()->withInput()->with('error', \$respuesta['mensaje']);
        }

        return redirect()->route('{$viewPath}.index')->with('success', \$respuesta['mensaje']);
    }

    public function show(int \$id): View|RedirectResponse
    {
        \$respuesta = \$this->bll->obtener(\$id);
        if (!\$respuesta['estado']) {
            return redirect()->route('{$viewPath}.index')->with('error', \$respuesta['mensaje']);
        }
        \${$varName} = \$respuesta['datos'];
        return view('{$viewPath}.show', compact('{$varName}'));
    }

    public function edit(int \$id): View|RedirectResponse
    {
        \$respuesta = \$this->bll->obtener(\$id);
        if (!\$respuesta['estado']) {
            return redirect()->route('{$viewPath}.index')->with('error', \$respuesta['mensaje']);
        }
        \${$varName} = \$respuesta['datos'];{$extraFormData}
        return view('{$viewPath}.edit', compact({$compactVars}));
    }

    public function update({$modelName}Request \$request, int \$id): JsonResponse|RedirectResponse
    {
        \$datos = \$request->validated();
        \$datos['id'] = \$id;
        \$ado = ADO{$modelName}::fromArray(\$datos);
        \$respuesta = \$this->bll->guardar(\$ado);

        if (\$request->wantsJson()) {
            return response()->json(\$respuesta, \$respuesta['estado'] ? 200 : 422);
        }

        if (!\$respuesta['estado']) {
            return back()->withInput()->with('error', \$respuesta['mensaje']);
        }

        return redirect()->route('{$viewPath}.index')->with('success', \$respuesta['mensaje']);
    }

    public function destroy(int \$id, Request \$request): JsonResponse|RedirectResponse
    {
        \$respuesta = \$this->bll->eliminar(\$id);
        if (\$request->wantsJson()) {
            return response()->json(\$respuesta, \$respuesta['estado'] ? 200 : 422);
        }
        return redirect()->route('{$viewPath}.index')->with(\$respuesta['estado'] ? 'success' : 'error', \$respuesta['mensaje']);
    }
}
PHP;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->components->twoColumnDetail("{$modelName}Controller", "CREADO");
    }

    protected function generateViews(string $modelName, string $varName, string $viewPath, bool $force): void
    {
        $dir = resource_path("views/{$viewPath}");
        File::ensureDirectoryExists($dir);

        // Definición de columnas y campos según el modelo
        if ($modelName === 'Municipio') {
            $tableHeaders = <<<HTML
                    <th class="p-3">ID</th>
                    <th class="p-3">Código</th>
                    <th class="p-3">Municipio</th>
                    <th class="p-3">Departamento</th>
                    <th class="p-3">Estado</th>
                    <th class="p-3 text-right">Acciones</th>
HTML;
            $dtColumns = <<<JS
            { data: 'id', name: 'id' },
            { data: 'codigo', name: 'codigo' },
            { data: 'nombre', name: 'nombre' },
            { data: 'departamento_nombre', name: 'departamento_nombre', defaultContent: '-' },
JS;
            $createFields = <<<BLADE
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Departamento *</label>
            <select name="departamento_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
                <option value="">-- Seleccionar Departamento --</option>
                @foreach(\$departamentos as \$depto)
                    <option value="{{ \$depto->id }}" {{ old('departamento_id') == \$depto->id ? 'selected' : '' }}>
                        {{ \$depto->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Código DANE / Código</label>
            <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: 05001"
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Municipio *</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Medellín"
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
BLADE;
            $editFields = <<<BLADE
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Departamento *</label>
            <select name="departamento_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
                <option value="">-- Seleccionar Departamento --</option>
                @foreach(\$departamentos as \$depto)
                    <option value="{{ \$depto->id }}" {{ old('departamento_id', \${$varName}->departamento_id) == \$depto->id ? 'selected' : '' }}>
                        {{ \$depto->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Código DANE / Código</label>
            <input type="text" name="codigo" value="{{ old('codigo', \${$varName}->codigo) }}"
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Municipio *</label>
            <input type="text" name="nombre" value="{{ old('nombre', \${$varName}->nombre) }}" required
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
BLADE;
            $showDetails = <<<BLADE
    <h3 class="text-base font-bold text-slate-800">{{ \${$varName}->nombre }}</h3>
    <p class="text-xs text-slate-600"><strong>Código:</strong> {{ \${$varName}->codigo ?? 'N/A' }}</p>
    <p class="text-xs text-slate-600"><strong>Departamento:</strong> {{ \${$varName}->departamento_nombre ?? ('ID #' . \${$varName}->departamento_id) }}</p>
    <p class="text-xs text-slate-500">ID: {{ \${$varName}->id }}</p>
BLADE;
        } elseif ($modelName === 'Departamento') {
            $tableHeaders = <<<HTML
                    <th class="p-3">ID</th>
                    <th class="p-3">Código DANE</th>
                    <th class="p-3">Departamento</th>
                    <th class="p-3">Estado</th>
                    <th class="p-3 text-right">Acciones</th>
HTML;
            $dtColumns = <<<JS
            { data: 'id', name: 'id' },
            { data: 'codigo', name: 'codigo' },
            { data: 'nombre', name: 'nombre' },
JS;
            $createFields = <<<BLADE
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Código DANE / Código</label>
            <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: 05"
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Departamento *</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Antioquia"
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
BLADE;
            $editFields = <<<BLADE
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Código DANE / Código</label>
            <input type="text" name="codigo" value="{{ old('codigo', \${$varName}->codigo) }}"
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Departamento *</label>
            <input type="text" name="nombre" value="{{ old('nombre', \${$varName}->nombre) }}" required
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
BLADE;
            $showDetails = <<<BLADE
    <h3 class="text-base font-bold text-slate-800">{{ \${$varName}->nombre }}</h3>
    <p class="text-xs text-slate-600"><strong>Código DANE:</strong> {{ \${$varName}->codigo ?? 'N/A' }}</p>
    <p class="text-xs text-slate-500">ID: {{ \${$varName}->id }}</p>
BLADE;
        } else {
            $tableHeaders = <<<HTML
                    <th class="p-3">ID</th>
                    <th class="p-3">Nombre</th>
                    <th class="p-3">Estado</th>
                    <th class="p-3 text-right">Acciones</th>
HTML;
            $dtColumns = <<<JS
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
JS;
            $createFields = <<<BLADE
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre *</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
BLADE;
            $editFields = <<<BLADE
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre *</label>
            <input type="text" name="nombre" value="{{ old('nombre', \${$varName}->nombre) }}" required
                   class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
        </div>
BLADE;
            $showDetails = <<<BLADE
    <h3 class="text-base font-bold text-slate-800">{{ \${$varName}->nombre }}</h3>
    <p class="text-xs text-slate-500">ID: {{ \${$varName}->id }}</p>
BLADE;
        }

        // index.blade.php
        $indexContent = <<<BLADE
@extends('layouts.app')

@section('title', 'Listado de {$modelName}s')
@section('header', 'Gestión de {$modelName}s')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Catálogo de {$modelName}s</h3>
            <p class="text-xs text-slate-500 mt-0.5">Administración y control del módulo de {$modelName}s</p>
        </div>
        <a href="{{ route('{$viewPath}.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-brand-500/20 transition-all">
            + Nuevo {$modelName}
        </a>
    </div>

    <div class="p-6">
        <table id="tabla-{$viewPath}" class="w-full text-xs text-left text-slate-600">
            <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                <tr>
{$tableHeaders}
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
\$(document).ready(function() {
    \$('#tabla-{$viewPath}').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('{$viewPath}.datatable') }}",
        columns: [
{$dtColumns}
            { 
                data: 'activo', 
                name: 'activo',
                render: function(data) {
                    return data ? '<span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full font-semibold text-[10px]">Activo</span>' 
                                : '<span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded-full font-semibold text-[10px]">Inactivo</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-right',
                render: function(data, type, row) {
                    return `
                        <div class="inline-flex space-x-2">
                            <a href="/{$viewPath}/\${row.id}/edit" class="text-brand-600 hover:text-brand-800 font-semibold">Editar</a>
                            <form action="/{$viewPath}/\${row.id}" method="POST" class="inline" onsubmit="return confirm('¿Seguro de eliminar este registro?');">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold ml-2">Eliminar</button>
                            </form>
                        </div>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });
});
</script>
@endpush
BLADE;
        File::put("{$dir}/index.blade.php", $indexContent);

        // create.blade.php
        $createContent = <<<BLADE
@extends('layouts.app')

@section('title', 'Nuevo {$modelName}')
@section('header', 'Crear {$modelName}')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100">
        <h3 class="text-base font-bold text-slate-800">Registrar {$modelName}</h3>
    </div>
    <form action="{{ route('{$viewPath}.store') }}" method="POST" class="p-6 space-y-4">
        @csrf
{$createFields}
        <div class="flex items-center space-x-2">
            <input type="checkbox" name="activo" value="1" id="activo" checked class="rounded text-brand-600">
            <label for="activo" class="text-xs text-slate-700">Registro Activo</label>
        </div>
        <div class="pt-4 flex items-center justify-between">
            <a href="{{ route('{$viewPath}.index') }}" class="text-xs text-slate-500 hover:underline">← Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl">
                Guardar {$modelName}
            </button>
        </div>
    </form>
</div>
@endsection
BLADE;
        File::put("{$dir}/create.blade.php", $createContent);

        // edit.blade.php
        $editContent = <<<BLADE
@extends('layouts.app')

@section('title', 'Editar {$modelName}')
@section('header', 'Editar {$modelName}')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100">
        <h3 class="text-base font-bold text-slate-800">Actualizar {$modelName} #{{ \${$varName}->id }}</h3>
    </div>
    <form action="{{ route('{$viewPath}.update', \${$varName}->id) }}" method="POST" class="p-6 space-y-4">
        @csrf
        @method('PUT')
{$editFields}
        <div class="flex items-center space-x-2">
            <input type="checkbox" name="activo" value="1" id="activo" {{ \${$varName}->activo ? 'checked' : '' }} class="rounded text-brand-600">
            <label for="activo" class="text-xs text-slate-700">Registro Activo</label>
        </div>
        <div class="pt-4 flex items-center justify-between">
            <a href="{{ route('{$viewPath}.index') }}" class="text-xs text-slate-500 hover:underline">← Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl">
                Actualizar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
BLADE;
        File::put("{$dir}/edit.blade.php", $editContent);

        // show.blade.php
        $showContent = <<<BLADE
@extends('layouts.app')

@section('title', 'Detalle de {$modelName}')
@section('header', 'Detalle de {$modelName}')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
{$showDetails}
    <div class="pt-2">
        <a href="{{ route('{$viewPath}.index') }}" class="text-xs text-brand-600 hover:underline">← Volver al listado</a>
    </div>
</div>
@endsection
BLADE;
        File::put("{$dir}/show.blade.php", $showContent);

        $this->components->twoColumnDetail("Vistas Blade ({$viewPath})", "CREADAS");
    }

    protected function generateMigration(string $modelName, string $tableName): void
    {
        $existing = glob(database_path("migrations/*_create_{$tableName}_table.php"));
        if (!empty($existing)) {
            $this->components->twoColumnDetail("Migración para '{$tableName}'", "YA EXISTE");
            return;
        }

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";
        $path = database_path("migrations/{$fileName}");

        $extraColumns = "";
        if ($modelName === 'Municipio') {
            $extraColumns = "\n            \$table->foreignId('departamento_id')->constrained('departamentos')->onDelete('cascade');\n            \$table->string('codigo', 10)->nullable();";
        } elseif ($modelName === 'Departamento') {
            $extraColumns = "\n            \$table->string('codigo', 10)->nullable();";
        }

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('{$tableName}')) {
            Schema::create('{$tableName}', function (Blueprint \$table) {
                \$table->id();{$extraColumns}
                \$table->string('nombre', 150);
                \$table->boolean('activo')->default(true);
                \$table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;

        File::put($path, $content);
        $this->components->twoColumnDetail("Migración: {$fileName}", "CREADA");
    }

    protected function registerRoutes(string $modelName, string $viewPath): void
    {
        $routesPath = base_path('routes/web.php');
        if (!File::exists($routesPath)) {
            return;
        }

        $content = File::get($routesPath);

        // Comprobar si ya está registrada
        if (str_contains($content, "{$modelName}Controller")) {
            return;
        }

        $routeDefinition = <<<PHP

    // Módulo {$modelName}
    Route::get('/{$viewPath}/datatable', [\App\Http\Controllers\\{$modelName}Controller::class, 'dataTable'])->name('{$viewPath}.datatable');
    Route::resource('{$viewPath}', \App\Http\Controllers\\{$modelName}Controller::class);
PHP;

        // Inyectar antes del cierre de auth middleware
        if (str_contains($content, "Route::resource('usuarios'")) {
            $content = str_replace(
                "Route::resource('usuarios', UsuarioController::class);",
                "Route::resource('usuarios', UsuarioController::class);" . $routeDefinition,
                $content
            );
            File::put($routesPath, $content);
            $this->components->twoColumnDetail("Rutas Web ({$viewPath})", "REGISTRADAS");
        }
    }
}
