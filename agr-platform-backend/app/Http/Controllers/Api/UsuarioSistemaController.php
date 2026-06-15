<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Support\AureaPermisos;

class UsuarioSistemaController extends Controller
{
    public function index()
    {
        return response()->json(
            UsuarioSistema::with('empresa')
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn ($usuario) => $this->presentarUsuario($usuario))
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

    private function presentarUsuario(UsuarioSistema $usuario): array
    {
        return [
            'id' => $usuario->id,
            'empresa_id' => $usuario->empresa_id,
            'empleado_id' => $usuario->empleado_id ?? null,
            'empresa' => $usuario->empresa,
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
