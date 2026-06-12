<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function index()
    {
        return response()->json(
            Modulo::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|unique:modulos,slug',
            'icono' => 'required|string|max:100',
            'ruta_frontend' => 'required|string|max:255',
            'color' => 'nullable|string|max:20'
        ]);

        $modulo = Modulo::create([
            'nombre' => $request->nombre,
            'slug' => $request->slug,
            'icono' => $request->icono,
            'ruta_frontend' => $request->ruta_frontend,
            'color' => $request->color ?? '#111827',
            'activo' => true
        ]);

        return response()->json([
            'message' => 'Módulo creado correctamente',
            'modulo' => $modulo
        ]);
    }

    public function show(string $id)
    {
        $modulo = Modulo::findOrFail($id);

        return response()->json($modulo);
    }

    public function update(Request $request, string $id)
    {
        $modulo = Modulo::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|unique:modulos,slug,' . $modulo->id,
            'icono' => 'required|string|max:100',
            'ruta_frontend' => 'required|string|max:255',
            'color' => 'nullable|string|max:20'
        ]);

        $modulo->update([
            'nombre' => $request->nombre,
            'slug' => $request->slug,
            'icono' => $request->icono,
            'ruta_frontend' => $request->ruta_frontend,
            'color' => $request->color ?? '#111827',
            'activo' => $request->activo
        ]);

        return response()->json([
            'message' => 'Módulo actualizado correctamente',
            'modulo' => $modulo
        ]);
    }

    public function destroy(string $id)
    {
        $modulo = Modulo::findOrFail($id);

        $modulo->delete();

        return response()->json([
            'message' => 'Módulo eliminado correctamente'
        ]);
    }
}