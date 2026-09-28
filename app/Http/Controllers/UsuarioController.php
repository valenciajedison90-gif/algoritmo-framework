<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ADO\ADOUsuario;
use App\BLL\EmpresaBLL;
use App\BLL\UsuarioBLL;
use App\Http\Requests\CambioPasswordRequest;
use App\Http\Requests\UsuarioRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de Usuarios del Sistema.
 * Orquesta peticiones CRUD, cambio de contraseñas y asignación a empresas y roles.
 */
class UsuarioController extends Controller
{
    protected UsuarioBLL $bll;
    protected EmpresaBLL $empresaBll;

    public function __construct(UsuarioBLL $bll, EmpresaBLL $empresaBll)
    {
        $this->bll = $bll;
        $this->empresaBll = $empresaBll;
    }

    public function index(): View
    {
        return view('usuarios.index');
    }

    public function dataTable(Request $request): JsonResponse
    {
        $resultado = $this->bll->dataTable($request->all());
        return response()->json($resultado);
    }

    public function create(): View
    {
        $usuario = new ADOUsuario();
        $empresasRes = $this->empresaBll->listar(['activo' => 1]);
        $empresas = $empresasRes['datos'] ?? [];

        return view('usuarios.create', compact('usuario', 'empresas'));
    }

    public function store(UsuarioRequest $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validated();
        $datos['activo'] = $request->has('activo');
        $passwordPlano = $request->input('password');

        $ado = ADOUsuario::fromArray($datos);
        $respuesta = $this->bll->guardar($ado, $passwordPlano);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 201 : 422);
        }

        if (!$respuesta['estado']) {
            return back()->withInput()->with('error', $respuesta['mensaje']);
        }

        return redirect()->route('usuarios.index')->with('success', $respuesta['mensaje']);
    }

    public function show(int $id): View|RedirectResponse
    {
        $respuesta = $this->bll->obtener($id);
        if (!$respuesta['estado']) {
            return redirect()->route('usuarios.index')->with('error', $respuesta['mensaje']);
        }

        $usuario = $respuesta['datos'];
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(int $id): View|RedirectResponse
    {
        $respuesta = $this->bll->obtener($id);
        if (!$respuesta['estado']) {
            return redirect()->route('usuarios.index')->with('error', $respuesta['mensaje']);
        }

        $usuario = $respuesta['datos'];
        $empresasRes = $this->empresaBll->listar(['activo' => 1]);
        $empresas = $empresasRes['datos'] ?? [];

        return view('usuarios.edit', compact('usuario', 'empresas'));
    }

    public function update(UsuarioRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $datos = $request->validated();
        $datos['id'] = $id;
        $datos['activo'] = $request->has('activo');
        $passwordPlano = $request->filled('password') ? $request->input('password') : null;

        $ado = ADOUsuario::fromArray($datos);
        $respuesta = $this->bll->guardar($ado, $passwordPlano);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 200 : 422);
        }

        if (!$respuesta['estado']) {
            return back()->withInput()->with('error', $respuesta['mensaje']);
        }

        return redirect()->route('usuarios.index')->with('success', $respuesta['mensaje']);
    }

    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $respuesta = $this->bll->eliminar($id);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 200 : 422);
        }

        return redirect()->route('usuarios.index')->with(
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

    public function showCambiarPassword(int $id): View|RedirectResponse
    {
        $respuesta = $this->bll->obtener($id);
        if (!$respuesta['estado']) {
            return redirect()->route('usuarios.index')->with('error', $respuesta['mensaje']);
        }

        $usuario = $respuesta['datos'];
        return view('usuarios.cambiar-password', compact('usuario'));
    }

    public function cambiarPassword(CambioPasswordRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $passwordActual = $request->input('password_actual');
        $nuevaPassword = (string) $request->input('nueva_password');

        $respuesta = $this->bll->cambiarPassword($id, $nuevaPassword, $passwordActual, false);

        if ($request->wantsJson()) {
            return response()->json($respuesta, $respuesta['estado'] ? 200 : 422);
        }

        if (!$respuesta['estado']) {
            return back()->with('error', $respuesta['mensaje']);
        }

        return redirect()->route('usuarios.show', $id)->with('success', $respuesta['mensaje']);
    }
}
