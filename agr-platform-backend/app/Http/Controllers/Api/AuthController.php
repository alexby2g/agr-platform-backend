<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioSistema;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\AureaPermisos;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'password' => 'required',
        ]);

        $usuario = UsuarioSistema::with(['empresa', 'modulos'])
            ->where('usuario', $request->usuario)
            ->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado',
            ], 401);
        }

        if (!$usuario->activo) {
            return response()->json([
                'message' => 'Usuario inactivo. Contacta al administrador.',
            ], 403);
        }

        if (!Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'message' => 'Contraseña incorrecta',
            ], 401);
        }

        $rolNormalizado = UsuarioSistema::normalizarRol($usuario->rol);

        if ($usuario->rol !== $rolNormalizado) {
            $usuario->update(['rol' => $rolNormalizado]);
            $usuario->rol = $rolNormalizado;
        }

        $usuario->forceFill([
            'ultimo_acceso' => now(),
        ])->save();

        $token = $usuario->createToken('agr-platform')->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => $this->presentarUsuario($usuario->load(['empresa', 'modulos'])),
            'rol' => $usuario->rol,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(
            $this->presentarUsuario($request->user()->load(['empresa', 'modulos']))
        );
    }

    public function launcher(Request $request)
    {
        $usuario = $request->user()->load(['empresa', 'modulos']);

        if (!$usuario->activo) {
            return response()->json([
                'message' => 'Usuario inactivo',
            ], 403);
        }

        $rolNormalizado = UsuarioSistema::normalizarRol($usuario->rol);

        if ($usuario->rol !== $rolNormalizado) {
            $usuario->update(['rol' => $rolNormalizado]);
            $usuario->rol = $rolNormalizado;
        }

        if ($usuario->esSuperAdmin()) {
            $modulos = Modulo::where('activo', true)
                ->orderBy('id', 'asc')
                ->get();
        } elseif ($usuario->esAdministrador()) {
            $modulos = $usuario->empresa
                ? $usuario->empresa
                    ->modulos()
                    ->where('modulos.activo', true)
                    ->orderBy('modulos.id', 'asc')
                    ->get()
                : collect();
        } else {
            $modulos = $usuario
                ->modulos()
                ->where('modulos.activo', true)
                ->orderBy('modulos.id', 'asc')
                ->get();
        }

        return response()->json([
            'usuario' => $this->presentarUsuario($usuario),
            'modulos' => $modulos->values(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'message' => 'Sesión cerrada',
        ]);
    }

    private function presentarUsuario(UsuarioSistema $usuario): array
    {
        return [
            'id' => $usuario->id,
            'empresa_id' => $usuario->empresa_id,
            'empresa' => $usuario->empresa,
            'nombre' => $usuario->nombre,
            'usuario' => $usuario->usuario,
            'email' => $usuario->email,
            'rol' => UsuarioSistema::normalizarRol($usuario->rol),
            'rol_nombre' => $usuario->rol_nombre,
            'activo' => (bool) $usuario->activo,
            'ultimo_acceso' => $usuario->ultimo_acceso,
            'modulos_asignados' => $usuario->modulos->pluck('id')->values(),
            'permisos' => AureaPermisos::paraUsuario($usuario),
        ];
    }
}