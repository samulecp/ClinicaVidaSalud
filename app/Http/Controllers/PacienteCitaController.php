<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ConfiguracionSistema;
use App\Models\HistorialCita;
use App\Models\Medico;
use App\Models\Pago;
use App\Models\Especialidad;
use App\Services\DisponibilidadCitas;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class PacienteCitaController extends Controller
{
    /**
     * Mostrar formulario de reserva.
     */
    public function create()
    {
        $paciente = auth()->user()->paciente;

        abort_unless(
            $paciente && $paciente->estado && auth()->user()->estado,
            403,
            'Tu cuenta de paciente no está activa.'
        );

        $especialidades = Especialidad::where('estado', true)
            ->orderBy('nombre')
            ->get();

        $precioConsulta = ConfiguracionSistema::actual()->precio_consulta;

        return view('paciente.reservar', compact('especialidades', 'precioConsulta'));
    }

    /**
     * Crear una cita pendiente de verificación de pago mediante QR.
     */
    public function store(Request $request, DisponibilidadCitas $disponibilidad)
    {
        $usuario = $request->user();
        $paciente = $usuario->paciente;

        abort_unless(
            $paciente && $paciente->estado && $usuario->estado,
            403,
            'Tu cuenta de paciente no está activa.'
        );

        $datos = $request->validate([
            'especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:tomorrow',
            ],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ], [
            'fecha.after_or_equal' => 'No puedes reservar una cita en una fecha pasada.',
            'fecha.before_or_equal' => 'Solo puedes reservar para hoy o mañana.',
            'hora_inicio.date_format' => 'Selecciona un horario válido.',
        ]);

        if (
            $datos['fecha'] === today()->format('Y-m-d') &&
            $datos['hora_inicio'] <= now()->format('H:i')
        ) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'No puedes reservar una hora que ya pasó.',
            ]);
        }

        $resultado = DB::transaction(function () use (
            $datos,
            $paciente,
            $usuario,
            $disponibilidad
        ) {
            $medico = Medico::whereKey($datos['medico_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $especialidad = Especialidad::findOrFail($datos['especialidad_id']);

            if (!$medico->estado || !$especialidad->estado) {
                throw ValidationException::withMessages([
                    'medico_id' => 'El médico o la especialidad no están activos.',
                ]);
            }

            if (!$medico->especialidades()->where(
                'especialidades.id',
                $especialidad->id
            )->exists()) {
                throw ValidationException::withMessages([
                    'medico_id' => 'El médico no pertenece a la especialidad seleccionada.',
                ]);
            }

            $turno = $disponibilidad->buscarTurno(
                (int) $medico->id,
                $datos['fecha'],
                $datos['hora_inicio']
            );

            if (!$turno) {
                throw ValidationException::withMessages([
                    'hora_inicio' => 'Este horario ya no está disponible. Selecciona otro.',
                ]);
            }

            $precio = ConfiguracionSistema::actual()->precio_consulta;

            $cita = Cita::create([
                'codigo' => 'CIT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'paciente_id' => $paciente->id,
                'medico_id' => $medico->id,
                'especialidad_id' => $especialidad->id,
                'fecha' => $datos['fecha'],
                'hora_inicio' => $turno['hora_inicio'],
                'hora_fin' => $turno['hora_fin'],
                'estado' => 'Pendiente de pago',
                'motivo' => $datos['motivo'] ?? null,
                'observaciones' => null,
                'pago_expira_en' => null,
            ]);

            Pago::create([
                'cita_id' => $cita->id,
                'usuario_id' => $usuario->id,
                'monto' => $precio,
                'moneda' => 'BOB',
                'metodo' => 'qr',
                'estado' => 'Pendiente',
            ]);

            HistorialCita::create([
                'cita_id' => $cita->id,
                'usuario_id' => $usuario->id,
                'accion' => 'Reserva pendiente de pago',
                'fecha_anterior' => null,
                'hora_anterior' => null,
                'fecha_nueva' => $datos['fecha'],
                'hora_nueva' => $turno['hora_inicio'],
                'detalle' => 'Reserva creada. El paciente debe pagar mediante QR y enviar el comprobante por WhatsApp para que recepción verifique y apruebe el pago.',
            ]);

            return $cita;
        });

        return redirect()
            ->route('paciente.citas')
            ->with('success', 'Tu reserva fue registrada. Realiza el pago mediante QR y envía el comprobante por WhatsApp para que recepción lo verifique.');
    }


    /**
     * Cancelar una cita desde el portal del paciente.
     */
    public function cancelar(Request $request, Cita $cita)
    {
        $paciente = $request->user()->paciente;

        abort_unless(
            $paciente && (int) $cita->paciente_id === (int) $paciente->id,
            403,
            'No tienes permiso para cancelar esta cita.'
        );

        DB::transaction(function () use ($request, $cita, $paciente) {
            Medico::whereKey($cita->medico_id)
                ->lockForUpdate()
                ->firstOrFail();

            $citaBloqueada = Cita::whereKey($cita->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $citaBloqueada->paciente_id !== (int) $paciente->id) {
                abort(403);
            }

            if (!in_array($citaBloqueada->estado, [
                'Programada',
                'Confirmada',
                'Pendiente de pago',
            ])) {
                throw ValidationException::withMessages([
                    'cancelacion' => 'Esta cita ya no se puede cancelar.',
                ]);
            }

            $fechaAnterior = $citaBloqueada->fecha->format('Y-m-d');
            $horaAnterior = $citaBloqueada->hora_inicio;

            $citaBloqueada->update([
                'estado' => 'Cancelada',
                'pago_expira_en' => null,
            ]);


            HistorialCita::create([
                'cita_id' => $citaBloqueada->id,
                'usuario_id' => $request->user()->id,
                'accion' => 'Cita cancelada por paciente',
                'fecha_anterior' => $fechaAnterior,
                'hora_anterior' => $horaAnterior,
                'fecha_nueva' => null,
                'hora_nueva' => null,
                'detalle' => 'El paciente canceló su cita desde el portal web.',
            ]);
        });

        return redirect()
            ->route('paciente.citas')
            ->with('success', 'Tu cita fue cancelada correctamente. El horario vuelve a estar disponible.');
    }
}
