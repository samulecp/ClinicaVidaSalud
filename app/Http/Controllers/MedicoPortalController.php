<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\HistorialCita;
use App\Models\Cita;
use Illuminate\Http\Request;

class MedicoPortalController extends Controller
{
    private function medicoAutenticado()
    {
        $medico = auth()->user()->medico;

        abort_if(!$medico, 403, 'Tu usuario no tiene un médico vinculado.');

        return $medico;
    }

    public function dashboard()
    {
        $medico = $this->medicoAutenticado();

        $hoy = now()->toDateString();

        $citasHoy = Cita::with(['paciente', 'especialidad'])
            ->where('medico_id', $medico->id)
            ->whereDate('fecha', $hoy)
            ->orderBy('hora_inicio')
            ->get();

        $proximasCitas = Cita::with(['paciente', 'especialidad'])
            ->where('medico_id', $medico->id)
            ->whereDate('fecha', '>', $hoy)
            ->where('estado', 'Programada')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->take(5)
            ->get();

        return view('medico.dashboard', compact(
            'medico',
            'citasHoy',
            'proximasCitas'
        ));
    }

    public function agenda(Request $request)
    {
        $medico = $this->medicoAutenticado();

        $fecha = $request->input('fecha', now()->toDateString());

        $citas = Cita::with(['paciente', 'especialidad'])
            ->where('medico_id', $medico->id)
            ->whereDate('fecha', $fecha)
            ->orderBy('hora_inicio')
            ->get();

        return view('medico.agenda', compact(
            'medico',
            'citas',
            'fecha'
        ));
    }

    
public function show(Cita $cita)
{
    $medico = $this->medicoAutenticado();

    abort_if($cita->medico_id !== $medico->id, 403);

    $cita->load(['paciente', 'especialidad']);

    $historial = Cita::with(['medico', 'especialidad'])
        ->where('paciente_id', $cita->paciente_id)
        ->where('estado', 'Atendida')
        ->where('id', '!=', $cita->id)
        ->orderByDesc('fecha')
        ->orderByDesc('hora_inicio')
        ->get();

    return view('medico.citas.show', compact(
        'cita',
        'historial'
    ));
}


public function atender(Request $request, Cita $cita)
{
    $medico = $this->medicoAutenticado();

    abort_if($cita->medico_id !== $medico->id, 403);

    if ($cita->estado !== 'Programada') {
        return back()->with('error', 'Esta cita no puede ser atendida.');
    }

    if (!$cita->pagos()->where('estado', 'Aprobado')->exists()) {
        return back()->with(
            'error',
            'No se puede atender esta cita porque no tiene un pago aprobado.'
        );
    }

    $datos = $request->validate([
        'diagnostico' => ['required', 'string', 'max:10000'],
        'tratamiento' => ['required', 'string', 'max:10000'],
        'observaciones_medicas' => ['nullable', 'string', 'max:10000'],
    ]);

    DB::transaction(function () use ($cita, $datos) {

        $citaBloqueada = Cita::whereKey($cita->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($citaBloqueada->estado !== 'Programada') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cita' => 'Esta cita ya no está disponible para atención.',
            ]);
        }

        if (!$citaBloqueada->pagos()->where('estado', 'Aprobado')->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'pago' => 'No se puede atender una cita sin pago aprobado.',
            ]);
        }

        $citaBloqueada->update([
            ...$datos,
            'estado' => 'Atendida',
        ]);

        HistorialCita::create([
            'cita_id' => $citaBloqueada->id,
            'usuario_id' => auth()->id(),
            'accion' => 'Consulta atendida',
            'detalle' => 'Se registró el diagnóstico y tratamiento médico.',
        ]);
    });

    return redirect()
        ->route('medico.agenda')
        ->with('success', 'Consulta registrada correctamente.');
}


public function inasistencia(Cita $cita)
{
    $medico = $this->medicoAutenticado();

    abort_if($cita->medico_id !== $medico->id, 403);

    if ($cita->estado !== 'Programada') {
        return back()->with('error', 'Esta cita no puede cambiar de estado.');
    }

    DB::transaction(function () use ($cita) {
        $cita->update([
            'estado' => 'Inasistencia',
        ]);

        HistorialCita::create([
            'cita_id' => $cita->id,
            'usuario_id' => auth()->id(),
            'accion' => 'Inasistencia registrada',
            'detalle' => 'El paciente no asistió a la consulta.',
        ]);
    });

    return redirect()
        ->route('medico.agenda')
        ->with('success', 'Inasistencia registrada correctamente.');
}

}
