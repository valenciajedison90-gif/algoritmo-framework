<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ADO\ADOEmpresa;
use App\BLL\EmpresaBLL;
use App\Core\ResponseHelper;
use App\Http\Requests\EmpresaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de Empresas.
 * Responsabilidad única: Orquestar la petición entre FormRequest, ADO, BLL y la Vista/JSON.
 */
class EmpresaController extends Controller
{
    protected EmpresaBLL $bll;

    public function __construct(EmpresaBLL $bll)
    {
        $this->bll = $bll;
    }

    public function index(): View
    {
        return view('empresas.index');
    }

    public function dataTable(Request $request): JsonResponse
    {
        $resultado = $this->bll->dataTable($request->all());
        return response()->json($resultado);
    }

    public function create(): View
    {
        $empresa = new ADOEmpresa();
        return view('empresas.create', compact('empresa'));
    }

    public function store(EmpresaRequest $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validated();
        $datos['activo'] = $request->has('activo');

        $ado = ADOEmpresa::fromArray($datos);
        $respuesta = $this->bll->guardar($ado);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 201 : 422);
        }

        if (!$respuesta['estado']) {
            return back()->withInput()->with('error', $respuesta['mensaje']);
        }

        return redirect()->route('empresas.index')->with('success', $respuesta['mensaje']);
    }

    public function show(int $id): View|RedirectResponse
    {
        $respuesta = $this->bll->obtener($id);
        if (!$respuesta['estado']) {
            return redirect()->route('empresas.index')->with('error', $respuesta['mensaje']);
        }

        $empresa = $respuesta['datos'];
        return view('empresas.show', compact('empresa'));
    }

    public function edit(int $id): View|RedirectResponse
    {
        $respuesta = $this->bll->obtener($id);
        if (!$respuesta['estado']) {
            return redirect()->route('empresas.index')->with('error', $respuesta['mensaje']);
        }

        $empresa = $respuesta['datos'];
        return view('empresas.edit', compact('empresa'));
    }

    public function update(EmpresaRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $datos = $request->validated();
        $datos['id'] = $id;
        $datos['activo'] = $request->has('activo');

        $ado = ADOEmpresa::fromArray($datos);
        $respuesta = $this->bll->guardar($ado);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 200 : 422);
        }

        if (!$respuesta['estado']) {
            return back()->withInput()->with('error', $respuesta['mensaje']);
        }

        return redirect()->route('empresas.index')->with('success', $respuesta['mensaje']);
    }

    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $respuesta = $this->bll->eliminar($id);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 200 : 422);
        }

        return redirect()->route('empresas.index')->with(
            $respuesta['estado'] ? 'success' : 'error',
            $respuesta['mensaje']
        );
    }

    public function toggleStatus(int $id, Request $request): JsonResponse
    {
        $nuevoEstado = (bool) $request->input('activo', false);
        $respuesta = $this->bll->cambiarEstado($id, $nuevoEstado);

        return response()->json($respuesta, $respuesta['estado'] ? 200 : 422);
    }
}
