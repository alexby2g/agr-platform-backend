<?php

namespace App\Http\Middleware;

use App\Models\UsuarioSistema;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRolSistema
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if (!$usuario->activo) {
            return response()->json(['message' => 'Usuario inactivo'], 403);
        }

        $rolUsuario = UsuarioSistema::normalizarRol($usuario->rol);
        $rolesPermitidos = collect($roles)
            ->flatMap(fn ($rol) => explode(',', $rol))
            ->map(fn ($rol) => UsuarioSistema::normalizarRol(trim($rol)))
            ->filter()
            ->values()
            ->all();

        if (empty($rolesPermitidos)) {
            return $next($request);
        }

        if (in_array($rolUsuario, $rolesPermitidos, true)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'No tienes permiso para realizar esta acción.',
        ], 403);
    }
}
