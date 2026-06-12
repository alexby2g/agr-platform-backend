<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaModuloController extends Controller
{
    public function index($empresaId)
    {
        $empresa = Empresa::with('modulos')->findOrFail($empresaId);

        return response()->json(
            $empresa->modulos
        );
    }

    public function update(Request $request, $empresaId)
    {
        $request->validate([
            'modulos' => 'required|array'
        ]);

        $empresa = Empresa::findOrFail($empresaId);

        $empresa->modulos()->sync(
            $request->modulos
        );

        return response()->json([
            'message' => 'Módulos asignados correctamente'
        ]);
    }
}