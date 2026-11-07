<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegistroPacienteController extends Controller
{
    public function create()
    {
        return view('auth.registro-paciente');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'ci' => [
                'required',
                'string',
                'max:30',
                Rule::unique('pacientes', 'ci'),
            ],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'sexo' => ['required', 'string', 'max:20'],
            'telefono' => ['required', 'string', 'max:30'],
            'correo' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'ci.unique' => 'Ya existe un paciente registrado con ese CI.',
            'correo.unique' => 'Este correo ya tiene una cuenta registrada.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'name' => $datos['nombres'] . ' ' . $datos['apellidos'],
                'email' => $datos['correo'],
                'password' => $datos['password'],
                'rol' => 'paciente',
                'estado' => true,
            ]);

            Paciente::create([
                'user_id' => $usuario->id,
                'ci' => $datos['ci'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'sexo' => $datos['sexo'],
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'],
                'direccion' => $datos['direccion'] ?? null,
                'estado' => true,
            ]);
        });

        return redirect()
            ->route('login')
            ->with('success', 'Tu cuenta fue creada correctamente. Ya puedes iniciar sesión.');
    }
}