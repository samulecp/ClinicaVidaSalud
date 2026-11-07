<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Especialidad;
use App\Services\DisponibilidadCitas;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    public function medicosPorEspecialidad(Request $request)
    {
        $datos = $request->validate([
            'especialidad_id' => ['required', 'exists:especialidades,id'],
        ]);

        $medicos = Medico::where('estado', true)
            ->whereHas('especialidades', function ($query) use ($datos) {
                $query->where(
                    'especialidades.id',
                    $datos['especialidad_id']
                );
            })
            ->orderBy('apellidos')
            ->get(['id', 'nombres', 'apellidos']);

        return response()->json($medicos);
    }

    public function turnos(
        Request $request,
        DisponibilidadCitas $disponibilidad
    ) {
        $datos = $request->validate([
            'medico_id' => ['required', 'exists:medicos,id'],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $medico = Medico::findOrFail($datos['medico_id']);

        if (!$medico->estado) {
            return response()->json([
                'message' => 'El médico no está activo.',
                'turnos' => [],
            ], 422);
        }

        $turnos = $disponibilidad->obtenerTurnos(
            (int) $medico->id,
            $datos['fecha']
        );

        return response()->json([
            'turnos' => $turnos,
        ]);
    }
}
