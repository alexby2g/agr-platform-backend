<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Models\UsuarioSistema;
use Illuminate\Http\Request;

class UsuarioModuloController extends Controller
{
    public function index(UsuarioSistema $usuario)
    {
        return response()->json([
            'usuario' => $usuario->load('empresa'),
            'modulos_asignados' => $usuario->modulos()->pluck('modulos.id'),
            'modulos_empresa' => $usuario->empresa
                ? $usuario->empresa->modulos()->where('modulos.activo', true)->get()
                : Modulo::where('activo', true)->get()
        ]);
    }

    public function update(Request $request, UsuarioSistema $usuario)
    {
        $request->validate([
            'modulos' => 'array',
            'modulos.*' => 'exists:modulos,id'
        ]);

        $usuario->modulos()->sync($request->modulos ?? []);

        return response()->json([
            'message' => 'Permisos de módulos actualizados correctamente',
            'usuario' => $usuario->load('modulos')
        ]);
    }
}