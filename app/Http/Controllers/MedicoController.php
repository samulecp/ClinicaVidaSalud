<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MedicoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $medicos = Medico::with('especialidades')
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('registro_profesional', 'ilike', "%{$buscar}%")
                        ->orWhere('nombres', 'ilike', "%{$buscar}%")
                        ->orWhere('apellidos', 'ilike', "%{$buscar}%");
                });
            })
            ->orderBy('apellidos')
            ->paginate(10)
            ->withQueryString();

        return view('admin.medicos.index', compact('medicos', 'buscar'));
    }

    public function create()
    {
        $especialidades = Especialidad::where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.medicos.create', compact('especialidades'));
    }

    
public function store(Request $request)
{
    $datos = $request->validate([
        'registro_profesional' => [
            'required',
            'string',
            'max:50',
            'unique:medicos,registro_profesional'
        ],
        'nombres' => ['required', 'string', 'max:100'],
        'apellidos' => ['required', 'string', 'max:100'],
        'telefono' => ['nullable', 'string', 'max:20'],
        'correo' => [
            'required',
            'email',
            'max:150',
            'unique:users,email'
        ],
        'direccion' => ['nullable', 'string', 'max:255'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'especialidades' => ['required', 'array', 'min:1'],
        'especialidades.*' => ['required', 'exists:especialidades,id'],
    ]);

    DB::transaction(function () use ($datos) {

        $usuario = User::create([
            'name' => $datos['nombres'] . ' ' . $datos['apellidos'],
            'email' => $datos['correo'],
            'password' => $datos['password'],
            'rol' => 'medico',
            'estado' => true,
        ]);

        $medico = Medico::create([
            'user_id' => $usuario->id,
            'registro_profesional' => $datos['registro_profesional'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'telefono' => $datos['telefono'] ?? null,
            'correo' => $datos['correo'],
            'direccion' => $datos['direccion'] ?? null,
            'estado' => true,
        ]);

        $medico->especialidades()->sync($datos['especialidades']);
    });

    return redirect()
        ->route('admin.medicos.index')
        ->with('success', 'Médico y cuenta de acceso registrados correctamente.');
}


    public function edit(Medico $medico)
    {
        $especialidades = Especialidad::where('estado', true)
            ->orderBy('nombre')
            ->get();

        $medico->load('especialidades');

        return view('admin.medicos.edit', compact('medico', 'especialidades'));
    }

    public function update(Request $request, Medico $medico)
    {
        $datos = $request->validate([
            'registro_profesional' => [
                'required', 'string', 'max:50',
                Rule::unique('medicos', 'registro_profesional')
                    ->ignore($medico->id),
            ],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'especialidades' => ['required', 'array', 'min:1'],
            'especialidades.*' => ['exists:especialidades,id'],
        ]);

        $especialidades = $datos['especialidades'];
        unset($datos['especialidades']);

        $medico->update($datos);
        $medico->especialidades()->sync($especialidades);

        return redirect()
            ->route('admin.medicos.index')
            ->with('success', 'Médico actualizado correctamente.');
    }

    public function cambiarEstado(Medico $medico)
    {
        $medico->estado = !$medico->estado;
        $medico->save();

        return redirect()
            ->route('admin.medicos.index')
            ->with('success', $medico->estado
                ? 'Médico activado correctamente.'
                : 'Médico desactivado correctamente.');
    }
}
