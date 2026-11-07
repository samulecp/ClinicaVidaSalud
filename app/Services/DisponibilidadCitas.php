<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\ConfiguracionSistema;
use App\Models\HorarioMedico;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DisponibilidadCitas
{
    /**
     * Devuelve los turnos libres de un médico para una fecha.
     */
    public function obtenerTurnos(
        int $medicoId,
        string $fecha,
        ?int $citaExcluirId = null
    ): array {
        $fechaCarbon = Carbon::parse($fecha)->startOfDay();

        // No ofrecer fechas anteriores a hoy.
        if ($fechaCarbon->lt(today())) {
            return [];
        }

        $duracion = ConfiguracionSistema::actual()
            ->duracion_cita_minutos;

        $diaSemana = $fechaCarbon->dayOfWeekIso;

        $horarios = HorarioMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('estado', true)
            ->orderBy('hora_inicio')
            ->get();

        $citas = Cita::where('medico_id', $medicoId)
    ->whereDate('fecha', $fecha)
    ->where(function ($query) {
        $query->where('estado', '!=', 'Pendiente de pago')
            ->orWhere(function ($subquery) {
                $subquery->where('estado', 'Pendiente de pago')
                    ->where(function ($expiracion) {
                        $expiracion->whereNull('pago_expira_en')
                            ->orWhere('pago_expira_en', '>', now());
                    });
            });
    })
    ->where('estado', '!=', 'Cancelada')
            ->when($citaExcluirId, function ($query) use ($citaExcluirId) {
                $query->where('id', '!=', $citaExcluirId);
            })
            ->get(['hora_inicio', 'hora_fin']);

        $turnosDisponibles = [];

        foreach ($horarios as $horario) {
            $inicioHorario = Carbon::parse($horario->hora_inicio);
            $finHorario = Carbon::parse($horario->hora_fin);

            $inicioTurno = $inicioHorario->copy();

            while (
                $inicioTurno->copy()->addMinutes($duracion)
                    ->lte($finHorario)
            ) {
                $finTurno = $inicioTurno->copy()
                    ->addMinutes($duracion);

                // Si es hoy, no ofrecer horas que ya pasaron.
                if (
                    $fechaCarbon->isToday() &&
                    $inicioTurno->lte(now())
                ) {
                    $inicioTurno->addMinutes($duracion);
                    continue;
                }

                $ocupado = $citas->contains(function ($cita) use (
                    $inicioTurno,
                    $finTurno
                ) {
                    $inicioExistente = Carbon::parse($cita->hora_inicio);
                    $finExistente = Carbon::parse($cita->hora_fin);

                    return $inicioTurno->lt($finExistente)
                        && $finTurno->gt($inicioExistente);
                });

                if (!$ocupado) {
                    $turnosDisponibles[] = [
                        'hora_inicio' => $inicioTurno->format('H:i'),
                        'hora_fin' => $finTurno->format('H:i'),
                    ];
                }

                $inicioTurno->addMinutes($duracion);
            }
        }

        return $turnosDisponibles;
    }

    /**
     * Comprueba que la hora solicitada sea uno de los turnos libres.
     */
    public function buscarTurno(
        int $medicoId,
        string $fecha,
        string $horaInicio,
        ?int $citaExcluirId = null
    ): ?array {
        foreach (
            $this->obtenerTurnos($medicoId, $fecha, $citaExcluirId)
            as $turno
        ) {
            if ($turno['hora_inicio'] === $horaInicio) {
                return $turno;
            }
        }

        return null;
    }
}
