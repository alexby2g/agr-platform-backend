<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioSistemaController extends Controller
{
    public function index()
    {
        return response()->json(
            UsuarioSistema::with('empresa')
                ->orderBy('id', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'usuario' => 'required|string|unique:usuario_sistemas,usuario',
            'email' => 'nullable|email|unique:usuario_sistemas,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|in:super_admin,admin_empresa,usuario',
            'empresa_id' => 'nullable|exists:empresas,id'
        ]);

        $usuario = UsuarioSistema::create([
            'empresa_id' => $request->empresa_id,
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'activo' => true
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'usuario' => $usuario
        ]);
    }

    public function show(string $id)
    {
        return response()->json(
            UsuarioSistema::with('empresa')->findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $usuario = UsuarioSistema::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'usuario' => 'required|string|unique:usuario_sistemas,usuario,' . $usuario->id,
            'email' => 'nullable|email|unique:usuario_sistemas,email,' . $usuario->id,
            'rol' => 'required|in:super_admin,admin_empresa,usuario',
            'empresa_id' => 'nullable|exists:empresas,id'
        ]);

        $datos = [
            'empresa_id' => $request->empresa_id,
            'nombre' => $request->nombre,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'rol' => $request->rol,
            'activo' => $request->activo
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
        ]);
    }

    public function destroy(string $id)
    {
        $usuario = UsuarioSistema::findOrFail($id);

        if ($usuario->rol === 'super_admin') {
            return response()->json([
                'message' => 'No puedes eliminar un super administrador'
            ], 403);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}