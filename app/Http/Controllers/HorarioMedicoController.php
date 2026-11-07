<?php

namespace App\Http\Controllers;

use App\Models\HorarioMedico;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HorarioMedicoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $horarios = HorarioMedico::with('medico')
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('medico', function ($q) use ($buscar) {
                    $q->where('nombres', 'ilike', "%{$buscar}%")
                        ->orWhere('apellidos', 'ilike', "%{$buscar}%")
                        ->orWhere('registro_profesional', 'ilike', "%{$buscar}%");
                });
            })
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->paginate(10)
            ->withQueryString();

        return view('admin.horarios.index', compact('horarios', 'buscar'));
    }

    public function create()
    {
        $medicos = Medico::where('estado', true)
            ->orderBy('apellidos')
            ->get();

        return view('admin.horarios.create', compact('medicos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'medico_id' => ['required', 'exists:medicos,id'],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'hora_fin.after' => 'La hora de finalización debe ser posterior a la hora de inicio.',
        ]);

        $this->verificarCruce($datos);

        $datos['estado'] = true;

        HorarioMedico::create($datos);

        return redirect()->route('admin.horarios.index')
            ->with('success', 'Horario registrado correctamente.');
    }

    public function edit(HorarioMedico $horario)
    {
        $medicos = Medico::where('estado', true)
            ->orWhere('id', $horario->medico_id)
            ->orderBy('apellidos')
            ->get();

        return view('admin.horarios.edit', compact('horario', 'medicos'));
    }

    public function update(Request $request, HorarioMedico $horario)
    {
        $datos = $request->validate([
            'medico_id' => ['required', 'exists:medicos,id'],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'hora_fin.after' => 'La hora de finalización debe ser posterior a la hora de inicio.',
        ]);

        $this->verificarCruce($datos, $horario->id);

        $horario->update($datos);

        return redirect()->route('admin.horarios.index')
            ->with('success', 'Horario actualizado correctamente.');
    }

    
public function cambiarEstado(HorarioMedico $horario)
{
    if (!$horario->estado) {
        $this->verificarCruce([
            'medico_id' => $horario->medico_id,
            'dia_semana' => $horario->dia_semana,
            'hora_inicio' => $horario->hora_inicio,
            'hora_fin' => $horario->hora_fin,
        ], $horario->id);
    }

    $horario->estado = !$horario->estado;
    $horario->save();

    return redirect()->route('admin.horarios.index')
        ->with('success', $horario->estado
            ? 'Horario activado correctamente.'
            : 'Horario desactivado correctamente.');
}


    private function verificarCruce(array $datos, ?int $ignorarId = null): void
    {
        $cruce = HorarioMedico::where('medico_id', $datos['medico_id'])
            ->where('dia_semana', $datos['dia_semana'])
            ->where('estado', true)
            ->where('hora_inicio', '<', $datos['hora_fin'])
            ->where('hora_fin', '>', $datos['hora_inicio'])
            ->when($ignorarId, function ($query, $id) {
                $query->where('id', '!=', $id);
            })
            ->exists();

        if ($cruce) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El médico ya tiene un horario que se cruza con el intervalo seleccionado ese día.',
            ]);
        }
    }
}
