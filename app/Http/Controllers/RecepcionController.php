<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;

class RecepcionController extends Controller
{
    public function dashboard()
    {
        $hoy = today();

        $citasHoy = Cita::whereDate('fecha', $hoy)
            ->count();

        $citasProgramadas = Cita::whereDate('fecha', $hoy)
            ->where('estado', 'Programada')
            ->count();

        $citasCanceladas = Cita::whereDate('fecha', $hoy)
            ->where('estado', 'Cancelada')
            ->count();

        $pacientesActivos = Paciente::where('estado', true)
            ->count();

        $proximasCitas = Cita::with([
                'paciente',
                'medico',
                'especialidad',
            ])
            ->whereDate('fecha', '>=', $hoy)
            ->where('estado', 'Programada')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->take(6)
            ->get();

        return view('recepcion.dashboard', compact(
            'citasHoy',
            'citasProgramadas',
            'citasCanceladas',
            'pacientesActivos',
            'proximasCitas'
        ));
    }
}
