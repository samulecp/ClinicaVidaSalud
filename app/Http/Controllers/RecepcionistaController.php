<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RecepcionistaController extends Controller
{
    public function index()
    {
        $recepcionistas = User::where('rol', 'recepcionista')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.recepcionistas.index', compact('recepcionistas'));
    }

    public function create()
    {
        return view('admin.recepcionistas.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un correo válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol' => 'recepcionista',
            'estado' => true,
        ]);

        return redirect()
            ->route('admin.recepcionistas.index')
            ->with('success', 'La cuenta de recepcionista fue creada correctamente.');
    }
}
