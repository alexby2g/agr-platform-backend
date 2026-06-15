<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\UsuarioSistema;
use App\Support\AureaPermisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioSistemaController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user();

        $query = UsuarioSistema::with('empresa')
            ->orderBy('id', 'desc');

        if ($auth && !$auth->esSuperAdmin()) {
            $query->where('empresa_id', $auth->empresa_id)
                ->where('rol', '!=', UsuarioSistema::ROL_SUPER_ADMIN);
        }

        return response()->json(
            $query->get()->map(fn ($usuario) => $this->presentarUsuario($usuario))
        );
    }

    public function store(Request $request)
    {
        $request->merge([
            'rol' => UsuarioSistema::normalizarRol($request->rol),
            'activo' => $request->has('activo') ? filter_var($request->activo, FILTER_VALIDATE_BOOLEAN) : true,
            'empresa_id' => $request->empresa_id ?: null,
        ]);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:255', 'unique:usuario_sistemas,usuario'],
            'email' => ['nullable', 'email', 'max:255', 'unique:usuario_sistemas,email'],
            'password' => ['required', 'string', 'min:6'],
            'rol' => ['required', Rule::in(UsuarioSistema::rolesPermitidos())],
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'activo' => ['boolean'],
        ]);

        $usuario = UsuarioSistema::create([
            'empresa_id' => $request->empresa_id,
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'activo' => $request->activo,
        ])->load('empresa');

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'usuario' => $this->presentarUsuario($usuario),
        ], 201);
    }

    public function show(string $id)
    {
        $usuario = UsuarioSistema::with('empresa')->findOrFail($id);

        return response()->json(
            $this->presentarUsuario($usuario)
        );
    }

    public function update(Request $request, string $id)
    {
        $usuario = UsuarioSistema::findOrFail($id);

        $request->merge([
            'rol' => UsuarioSistema::normalizarRol($request->rol),
            'activo' => $request->has('activo') ? filter_var($request->activo, FILTER_VALIDATE_BOOLEAN) : $usuario->activo,
            'empresa_id' => $request->empresa_id ?: null,
        ]);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:255', Rule::unique('usuario_sistemas', 'usuario')->ignore($usuario->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('usuario_sistemas', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'rol' => ['required', Rule::in(UsuarioSistema::rolesPermitidos())],
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'activo' => ['boolean'],
        ]);

        $datos = [
            'empresa_id' => $request->empresa_id,
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'rol' => $request->rol,
            'activo' => $request->activo,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);
        $usuario->load('empresa');

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $this->presentarUsuario($usuario),
        ]);
    }

    public function destroy(string $id)
    {
        $usuario = UsuarioSistema::findOrFail($id);

        if ($usuario->rol === UsuarioSistema::ROL_SUPER_ADMIN) {
            return response()->json([
                'message' => 'No puedes eliminar un super administrador',
            ], 403);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
        ]);
    }

    /**
     * Usuarios administrables dentro de la empresa del usuario autenticado.
     * Super admin puede ver todas las empresas o filtrar por empresa_id.
     * Administrador solo puede ver usuarios de su propia empresa.
     */
    public function usuariosEmpresa(Request $request)
    {
        $auth = $request->user()->load('empresa');
        $empresaId = $this->empresaIdPermitida($request);

        $query = UsuarioSistema::with('empresa')
            ->where('rol', '!=', UsuarioSistema::ROL_SUPER_ADMIN)
            ->orderBy('id', 'desc');

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        return response()->json([
            'empresa' => $auth->empresa,
            'empresas' => $auth->esSuperAdmin()
                ? Empresa::orderBy('nombre')->get()
                : [],
            'usuarios' => $query->get()->map(fn ($usuario) => $this->presentarUsuario($usuario))->values(),
        ]);
    }

    public function storeEmpresa(Request $request)
    {
        $auth = $request->user();
        $empresaId = $this->empresaIdPermitida($request);

        if (!$empresaId) {
            return response()->json([
                'message' => 'Debes seleccionar una empresa válida.',
            ], 422);
        }

        $rol = UsuarioSistema::normalizarRol($request->input('rol', UsuarioSistema::ROL_EMPLEADO));

        if (!in_array($rol, [UsuarioSistema::ROL_ADMINISTRADOR, UsuarioSistema::ROL_EMPLEADO], true)) {
            $rol = UsuarioSistema::ROL_EMPLEADO;
        }

        if (!$auth->esSuperAdmin() && $rol === UsuarioSistema::ROL_ADMINISTRADOR) {
            $rol = UsuarioSistema::ROL_EMPLEADO;
        }

        $request->merge([
            'rol' => $rol,
            'empresa_id' => $empresaId,
            'activo' => $request->has('activo') ? filter_var($request->activo, FILTER_VALIDATE_BOOLEAN) : true,
        ]);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:255', 'unique:usuario_sistemas,usuario'],
            'email' => ['nullable', 'email', 'max:255', 'unique:usuario_sistemas,email'],
            'password' => ['required', 'string', 'min:6'],
            'rol' => ['required', Rule::in([UsuarioSistema::ROL_ADMINISTRADOR, UsuarioSistema::ROL_EMPLEADO])],
            'empresa_id' => ['required', 'exists:empresas,id'],
            'activo' => ['boolean'],
        ]);

        $usuario = UsuarioSistema::create([
            'empresa_id' => $empresaId,
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $rol,
            'activo' => $request->activo,
        ])->load('empresa');

        return response()->json([
            'message' => 'Usuario de empresa creado correctamente',
            'usuario' => $this->presentarUsuario($usuario),
        ], 201);
    }

    public function updateEmpresa(Request $request, string $id)
    {
        $auth = $request->user();
        $usuario = UsuarioSistema::with('empresa')->findOrFail($id);
        $empresaId = $this->empresaIdPermitida($request, $usuario);

        if (!$this->puedeGestionarUsuarioEmpresa($auth, $usuario)) {
            return response()->json([
                'message' => 'No puedes modificar este usuario.',
            ], 403);
        }

        $rol = UsuarioSistema::normalizarRol($request->input('rol', $usuario->rol));

        if (!in_array($rol, [UsuarioSistema::ROL_ADMINISTRADOR, UsuarioSistema::ROL_EMPLEADO], true)) {
            $rol = UsuarioSistema::ROL_EMPLEADO;
        }

        if (!$auth->esSuperAdmin() && $rol === UsuarioSistema::ROL_ADMINISTRADOR) {
            $rol = UsuarioSistema::ROL_EMPLEADO;
        }

        $request->merge([
            'rol' => $rol,
            'empresa_id' => $empresaId,
            'activo' => $request->has('activo') ? filter_var($request->activo, FILTER_VALIDATE_BOOLEAN) : $usuario->activo,
        ]);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:255', Rule::unique('usuario_sistemas', 'usuario')->ignore($usuario->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('usuario_sistemas', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'rol' => ['required', Rule::in([UsuarioSistema::ROL_ADMINISTRADOR, UsuarioSistema::ROL_EMPLEADO])],
            'empresa_id' => ['required', 'exists:empresas,id'],
            'activo' => ['boolean'],
        ]);

        $datos = [
            'empresa_id' => $empresaId,
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'rol' => $rol,
            'activo' => $request->activo,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);
        $usuario->load('empresa');

        return response()->json([
            'message' => 'Usuario de empresa actualizado correctamente',
            'usuario' => $this->presentarUsuario($usuario),
        ]);
    }

    public function destroyEmpresa(Request $request, string $id)
    {
        $auth = $request->user();
        $usuario = UsuarioSistema::findOrFail($id);

        if ((int) $auth->id === (int) $usuario->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario.',
            ], 403);
        }

        if (!$this->puedeGestionarUsuarioEmpresa($auth, $usuario)) {
            return response()->json([
                'message' => 'No puedes eliminar este usuario.',
            ], 403);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario de empresa eliminado correctamente',
        ]);
    }

    private function empresaIdPermitida(Request $request, ?UsuarioSistema $usuarioObjetivo = null): ?int
    {
        $auth = $request->user();

        if ($auth->esSuperAdmin()) {
            return (int) ($request->input('empresa_id') ?: $usuarioObjetivo?->empresa_id ?: 0) ?: null;
        }

        return $auth->empresa_id ? (int) $auth->empresa_id : null;
    }

    private function puedeGestionarUsuarioEmpresa(UsuarioSistema $auth, UsuarioSistema $usuario): bool
    {
        if ($usuario->rol === UsuarioSistema::ROL_SUPER_ADMIN) {
            return false;
        }

        if ($auth->esSuperAdmin()) {
            return true;
        }

        return $auth->esAdministrador()
            && $auth->empresa_id
            && (int) $auth->empresa_id === (int) $usuario->empresa_id;
    }

    private function presentarUsuario(UsuarioSistema $usuario): array
    {
        return [
            'id' => $usuario->id,
            'empresa_id' => $usuario->empresa_id,
            'empleado_id' => $usuario->empleado_id ?? null,
            'empresa' => $usuario->empresa,
            'empresa_nombre' => $usuario->empresa?->nombre,
            'nombre' => $usuario->nombre,
            'usuario' => $usuario->usuario,
            'email' => $usuario->email,
            'rol' => UsuarioSistema::normalizarRol($usuario->rol),
            'rol_nombre' => $usuario->rol_nombre,
            'activo' => (bool) $usuario->activo,
            'ultimo_acceso' => $usuario->ultimo_acceso,
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
            'permisos' => AureaPermisos::paraUsuario($usuario),
        ];
    }
}
