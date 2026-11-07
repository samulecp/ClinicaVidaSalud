<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;

class PacientePortalController extends Controller
{
    public function dashboard()
    {
        $usuario = auth()->user();
        $paciente = $usuario->paciente;

        if (!$paciente || !$paciente->estado) {
            abort(403, 'No tienes una ficha de paciente activa.');
        }

        $proximasCitas = Cita::with([
                'medico',
                'especialidad',
                'pagos',
            ])
            ->where('paciente_id', $paciente->id)
            ->whereDate('fecha', '>=', today())
            ->whereIn('estado', [
                'Programada',
                'Confirmada',
                'Pendiente de pago',
            ])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->take(5)
            ->get();

        $totalProximas = Cita::where('paciente_id', $paciente->id)
            ->whereDate('fecha', '>=', today())
            ->whereIn('estado', ['Programada', 'Confirmada'])
            ->count();

        $totalPendientesPago = Cita::where('paciente_id', $paciente->id)
            ->where('estado', 'Pendiente de pago')
            ->count();

        $totalAtendidas = Cita::where('paciente_id', $paciente->id)
            ->where('estado', 'Atendida')
            ->count();

        return view('paciente.dashboard', compact(
            'paciente',
            'proximasCitas',
            'totalProximas',
            'totalPendientesPago',
            'totalAtendidas'
        ));
    }

    public function citas()
    {
        $usuario = auth()->user();
        $paciente = $usuario->paciente;

        if (!$paciente || !$paciente->estado) {
            abort(403, 'No tienes una ficha de paciente activa.');
        }

        $citas = Cita::with([
                'medico',
                'especialidad',
                'pagos',
            ])
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->paginate(10);

        return view('paciente.citas', compact('paciente', 'citas'));
    }
}
