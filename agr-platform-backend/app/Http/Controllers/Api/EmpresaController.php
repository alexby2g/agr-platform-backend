<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        return response()->json(
            Empresa::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|unique:empresas,slug'
        ]);

        $empresa = Empresa::create([
            'nombre' => $request->nombre,
            'slug' => $request->slug,
            'logo' => $request->logo,
            'color_primario' => $request->color_primario ?? '#4f46e5',
            'color_secundario' => $request->color_secundario ?? '#9333ea',
            'activo' => true
        ]);

        return response()->json([
            'message' => 'Empresa creada correctamente',
            'empresa' => $empresa
        ]);
    }

    public function show(string $id)
    {
        $empresa = Empresa::findOrFail($id);

        return response()->json($empresa);
    }

    public function update(Request $request, string $id)
    {
        $empresa = Empresa::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|unique:empresas,slug,' . $empresa->id
        ]);

        $empresa->update([
            'nombre' => $request->nombre,
            'slug' => $request->slug,
            'logo' => $request->logo,
            'color_primario' => $request->color_primario,
            'color_secundario' => $request->color_secundario,
            'activo' => $request->activo
        ]);

        return response()->json([
            'message' => 'Empresa actualizada correctamente',
            'empresa' => $empresa
        ]);
    }

    public function destroy(string $id)
    {
        $empresa = Empresa::findOrFail($id);

        $empresa->delete();

        return response()->json([
            'message' => 'Empresa eliminada correctamente'
        ]);
    }
}