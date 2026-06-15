<?php

namespace App\Http\Middleware;

use App\Models\UsuarioSistema;
use App\Support\AureaPermisos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermisoAurea
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if (!$usuario->activo) {
            return response()->json(['message' => 'Usuario inactivo'], 403);
        }

        $usuario->loadMissing(['empresa', 'modulos']);
        $rol = UsuarioSistema::normalizarRol($usuario->rol);

        if ($rol !== UsuarioSistema::ROL_SUPER_ADMIN) {
            if (!$usuario->empresa || !$usuario->empresa->activo) {
                return response()->json([
                    'message' => 'La empresa de este usuario no está activa.',
                ], 403);
            }

            if ($usuario->empresa->vencida) {
                return response()->json([
                    'message' => 'El plan de la empresa está vencido. Contacta al administrador de AGR Studio.',
                ], 403);
            }
        }

        $tieneModuloAurea = $usuario
            ->obtenerModulosPermitidos()
            ->contains(fn ($modulo) => $modulo->slug === 'aurea');

        if (!$tieneModuloAurea) {
            return response()->json([
                'message' => 'No tienes acceso al módulo AUREA Beauty.',
            ], 403);
        }

        if (AureaPermisos::usuarioPuede($usuario, $permiso)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'No tienes permiso para realizar esta acción en AUREA Beauty.',
            'permiso_requerido' => $permiso,
        ], 403);
    }
}
