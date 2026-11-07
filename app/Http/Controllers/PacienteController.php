<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PacienteController extends Controller
{
    private function esRecepcion(): bool
    {
        return auth()->user()->rol === 'recepcionista';
    }

    private function rutaBase(): string
    {
        return $this->esRecepcion()
            ? 'recepcion.pacientes'
            : 'admin.pacientes';
    }

    private function vistaBase(): string
{
    return 'admin.pacientes';
}

    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $pacientes = Paciente::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('ci', 'ilike', "%{$buscar}%")
                        ->orWhere('nombres', 'ilike', "%{$buscar}%")
                        ->orWhere('apellidos', 'ilike', "%{$buscar}%");
                });
            })
            ->orderBy('apellidos')
            ->paginate(10)
            ->withQueryString();

        return view($this->vistaBase() . '.index', compact('pacientes', 'buscar'));
    }

    public function create()
    {
        return view($this->vistaBase() . '.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'ci' => ['required', 'string', 'max:20', 'unique:pacientes,ci'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $datos['estado'] = true;

        Paciente::create($datos);

        return redirect()
            ->route($this->rutaBase() . '.index')
            ->with('success', 'Paciente registrado correctamente.');
    }

    public function edit(Paciente $paciente)
    {
        return view($this->vistaBase() . '.edit', compact('paciente'));
    }

    public function update(Request $request, Paciente $paciente)
    {
        $datos = $request->validate([
            'ci' => [
                'required',
                'string',
                'max:20',
                Rule::unique('pacientes', 'ci')->ignore($paciente->id),
            ],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $paciente->update($datos);

        return redirect()
            ->route($this->rutaBase() . '.index')
            ->with('success', 'Datos del paciente actualizados correctamente.');
    }

    public function cambiarEstado(Paciente $paciente)
    {
        $paciente->estado = !$paciente->estado;
        $paciente->save();

        $mensaje = $paciente->estado
            ? 'Paciente activado correctamente.'
            : 'Paciente desactivado correctamente.';

        return redirect()
            ->route($this->rutaBase() . '.index')
            ->with('success', $mensaje);
    }
}
