<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioSistema;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'password' => 'required'
        ]);

        $usuario = UsuarioSistema::with('empresa')
            ->where('usuario', $request->usuario)
            ->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 401);
        }

        if (!$usuario->activo) {
            return response()->json([
                'message' => 'Usuario inactivo. Contacta al administrador.'
            ], 403);
        }

        if (!Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        $token = $usuario->createToken('agr-platform')->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => [
                'id' => $usuario->id,
                'empresa_id' => $usuario->empresa_id,
                'empresa' => $usuario->empresa,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'activo' => $usuario->activo
            ],
            'rol' => $usuario->rol
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(
            $request->user()->load('empresa')
        );
    }

    public function launcher(Request $request)
    {
        $usuario = $request->user()->load('empresa');

        if (!$usuario->activo) {
            return response()->json([
                'message' => 'Usuario inactivo'
            ], 403);
        }

        if ($usuario->rol === 'super_admin') {
            $modulos = Modulo::where('activo', true)
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $modulos = $usuario->empresa
                ? $usuario->empresa
                    ->modulos()
                    ->where('modulos.activo', true)
                    ->orderBy('modulos.id', 'asc')
                    ->get()
                : collect();
        }

        return response()->json([
            'usuario' => [
                'id' => $usuario->id,
                'empresa_id' => $usuario->empresa_id,
                'empresa' => $usuario->empresa,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'activo' => $usuario->activo
            ],
            'modulos' => $modulos
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'message' => 'Sesión cerrada'
        ]);
    }
}