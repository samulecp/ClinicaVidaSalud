<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;

class ConfiguracionSistemaController extends Controller
{
    public function edit()
    {
        $configuracion = ConfiguracionSistema::actual();

        return view('admin.configuracion.edit', compact('configuracion'));
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'duracion_cita_minutos' => [
                'required',
                'integer',
                'min:5',
                'max:180',
            ],
        ], [
            'duracion_cita_minutos.required' => 'Debes indicar la duración de la cita.',
            'duracion_cita_minutos.integer' => 'La duración debe ser un número entero.',
            'duracion_cita_minutos.min' => 'La duración mínima es de 5 minutos.',
            'duracion_cita_minutos.max' => 'La duración máxima es de 180 minutos.',
        ]);

        $configuracion = ConfiguracionSistema::actual();

        $configuracion->update($datos);

        return redirect()
            ->route('admin.configuracion.edit')
            ->with('success', 'La duración global de las citas se actualizó correctamente.');
    }
}