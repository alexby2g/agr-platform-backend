<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:empresas,slug'],
            'logo' => ['nullable', 'string', 'max:2048'],
            'color_primario' => ['nullable', 'string', 'max:20'],
            'color_secundario' => ['nullable', 'string', 'max:20'],
            'plan' => ['nullable', Rule::in(Empresa::planesPermitidos())],
            'fecha_vencimiento' => ['nullable', 'date'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $empresa = Empresa::create([
            'nombre' => $data['nombre'],
            'slug' => $data['slug'],
            'logo' => $data['logo'] ?? null,
            'color_primario' => $data['color_primario'] ?? '#ec4899',
            'color_secundario' => $data['color_secundario'] ?? '#9333ea',
            'plan' => $data['plan'] ?? Empresa::PLAN_BASICO,
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
            'activo' => $data['activo'] ?? true,
        ]);

        return response()->json([
            'message' => 'Empresa creada correctamente',
            'empresa' => $empresa
        ], 201);
    }

    public function show(string $id)
    {
        $empresa = Empresa::findOrFail($id);

        return response()->json($empresa);
    }

    public function update(Request $request, string $id)
    {
        $empresa = Empresa::findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('empresas', 'slug')->ignore($empresa->id)],
            'logo' => ['nullable', 'string', 'max:2048'],
            'color_primario' => ['nullable', 'string', 'max:20'],
            'color_secundario' => ['nullable', 'string', 'max:20'],
            'plan' => ['nullable', Rule::in(Empresa::planesPermitidos())],
            'fecha_vencimiento' => ['nullable', 'date'],
            'activo' => ['required', 'boolean'],
        ]);

        $empresa->update([
            'nombre' => $data['nombre'],
            'slug' => $data['slug'],
            'logo' => $data['logo'] ?? null,
            'color_primario' => $data['color_primario'] ?? '#ec4899',
            'color_secundario' => $data['color_secundario'] ?? '#9333ea',
            'plan' => $data['plan'] ?? Empresa::PLAN_BASICO,
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
            'activo' => $data['activo'],
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
