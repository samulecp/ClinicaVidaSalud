<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use RuntimeException;

class ClinicaDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | 1. Obtener los datos existentes que se conservarán
            |--------------------------------------------------------------------------
            */

            $pacientes = DB::table('pacientes')
                ->where('estado', true)
                ->orderBy('id')
                ->get(['id', 'user_id']);

            $medicos = DB::table('medicos')
                ->where('estado', true)
                ->orderBy('id')
                ->get(['id']);

            $horarios = DB::table('horarios_medicos')
                ->where('estado', true)
                ->get([
                    'medico_id',
                    'dia_semana',
                    'hora_inicio',
                    'hora_fin',
                ]);

            $especialidadesMedicos = DB::table('medico_especialidad as me')
                ->join(
                    'especialidades as e',
                    'e.id',
                    '=',
                    'me.especialidad_id'
                )
                ->where('e.estado', true)
                ->where('e.id', '!=', 2)
                ->orderBy('e.id')
                ->get([
                    'me.medico_id',
                    'me.especialidad_id',
                ]);

            if ($pacientes->isEmpty()) {
                throw new RuntimeException(
                    'No existen pacientes activos para generar las citas.'
                );
            }

            if ($medicos->isEmpty() || $horarios->isEmpty()) {
                throw new RuntimeException(
                    'No existen médicos activos con horarios disponibles.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Agrupar horarios y especialidades por médico
            |--------------------------------------------------------------------------
            */

            $horariosPorMedico = [];

            foreach ($horarios as $horario) {
                $horariosPorMedico[$horario->medico_id]
                    [$horario->dia_semana][] = $horario;
            }

            $especialidadPorMedico = [];

            foreach ($especialidadesMedicos as $relacion) {
                $especialidadPorMedico[$relacion->medico_id]
                    [] = $relacion->especialidad_id;
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Eliminar datos anteriores de citas
            |--------------------------------------------------------------------------
            */

            DB::table('pagos')->delete();

            DB::table('historial_citas')->delete();

            DB::table('citas')->delete();

            /*
            |--------------------------------------------------------------------------
            | 4. Eliminar Pediatria sin tilde
            |--------------------------------------------------------------------------
            */

            DB::table('medico_especialidad')
                ->where('especialidad_id', 2)
                ->delete();

            DB::table('especialidades')
                ->where('id', 2)
                ->where('nombre', 'Pediatria')
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | 5. Obtener usuario que registrará los historiales
            |--------------------------------------------------------------------------
            */

            $usuarioResponsable = DB::table('users')
                ->whereIn('rol', ['administrador', 'recepcionista'])
                ->orderBy('id')
                ->value('id');

            /*
            |--------------------------------------------------------------------------
            | 6. Preparar generación de citas
            |--------------------------------------------------------------------------
            */

            $hoy = Carbon::today();
            $ahora = Carbon::now();

            $fechaInicio = Carbon::parse('2026-08-01');
            $fechaFin = Carbon::parse('2026-11-30');

            $motivos = [
                'Consulta general',
                'Control médico',
                'Dolor o molestia',
                'Revisión médica',
                'Seguimiento de tratamiento',
                'Consulta preventiva',
                'Evaluación médica',
            ];

            $contador = 0;
            $indicePaciente = 0;

            // Controla que un paciente no tenga citas simultáneas.
            $reservasPacientes = [];

            /*
            |--------------------------------------------------------------------------
            | 7. Generar citas según los horarios existentes
            |--------------------------------------------------------------------------
            */

            for (
                $fecha = $fechaInicio->copy();
                $fecha->lte($fechaFin);
                $fecha->addDay()
            ) {
                $diaSemana = (int) $fecha->dayOfWeekIso;
                $fechaTexto = $fecha->toDateString();

                foreach ($medicos as $medico) {

                    $medicoId = $medico->id;

                    $bloques = $horariosPorMedico[$medicoId][$diaSemana]
                        ?? [];

                    $especialidades = $especialidadPorMedico[$medicoId]
                        ?? [];

                    if (empty($bloques) || empty($especialidades)) {
                        continue;
                    }

                    /*
                     * Si tiene varios bloques horarios, se alternan
                     * para distribuir las citas entre ellos.
                     */
                    $indiceBloque = (
                        (int) $fecha->dayOfYear
                    ) % count($bloques);

                    $bloque = $bloques[$indiceBloque];

                    $inicio = Carbon::parse(
                        $fechaTexto . ' ' . $bloque->hora_inicio
                    )->addMinutes(30);

                    $fin = $inicio->copy()->addMinutes(15);

                    $finHorario = Carbon::parse(
                        $fechaTexto . ' ' . $bloque->hora_fin
                    );

                    if ($fin->gt($finHorario)) {
                        continue;
                    }

                    // No generar citas en horas que ya pasaron hoy.
                    if ($inicio->lte(Carbon::now())) {
                        if ($fecha->isSameDay($hoy)) {
                            continue;
                        }
                    }

                    /*
                     * Buscar un paciente que no tenga otra cita
                     * en el mismo intervalo de tiempo.
                     */
                    $pacienteSeleccionado = null;

                    $cantidadPacientes = $pacientes->count();

                    for ($i = 0; $i < $cantidadPacientes; $i++) {

                        $indice = (
                            $indicePaciente + $i
                        ) % $cantidadPacientes;

                        $candidato = $pacientes[$indice];

                        $conflicto = false;

                        $reservas = $reservasPacientes[$candidato->id]
                            [$fechaTexto] ?? [];

                        foreach ($reservas as $reserva) {

                            if (
                                $inicio->lt($reserva['fin'])
                                && $fin->gt($reserva['inicio'])
                            ) {
                                $conflicto = true;
                                break;
                            }
                        }

                        if (!$conflicto) {
                            $pacienteSeleccionado = $candidato;
                            $indicePaciente = ($indice + 1)
                                % $cantidadPacientes;
                            break;
                        }
                    }

                    if (!$pacienteSeleccionado) {
                        continue;
                    }

                    $pacienteId = $pacienteSeleccionado->id;

                    $reservasPacientes[$pacienteId][$fechaTexto][] = [
                        'inicio' => $inicio->copy(),
                        'fin' => $fin->copy(),
                    ];

                    $contador++;

                    /*
                    |--------------------------------------------------------------------------
                    | 8. Determinar estado de la cita
                    |--------------------------------------------------------------------------
                    */

                    $esFutura = $inicio->gt(Carbon::now());

                    if ($esFutura) {
                        // 80 % programadas y 20 % canceladas.
                        $estado = ($contador % 10 < 8)
                            ? 'Programada'
                            : 'Cancelada';
                    } else {
                        // Históricas: atendidas, canceladas e inasistencias.
                        $posicion = $contador % 10;

                        if ($posicion < 6) {
                            $estado = 'Atendida';
                        } elseif ($posicion < 8) {
                            $estado = 'Cancelada';
                        } else {
                            $estado = 'Inasistencia';
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 9. Crear cita
                    |--------------------------------------------------------------------------
                    */

                    $especialidadId = $especialidades[
                        ($contador - 1) % count($especialidades)
                    ];

                    $codigo = sprintf(
                        'CVS-%s-%05d',
                        $fecha->format('Ymd'),
                        $contador
                    );

                    $citaId = DB::table('citas')->insertGetId([
                        'codigo' => $codigo,
                        'paciente_id' => $pacienteId,
                        'medico_id' => $medicoId,
                        'especialidad_id' => $especialidadId,
                        'fecha' => $fechaTexto,
                        'hora_inicio' => $inicio->format('H:i:s'),
                        'hora_fin' => $fin->format('H:i:s'),
                        'estado' => $estado,
                        'motivo' => $motivos[
                            ($contador - 1) % count($motivos)
                        ],
                        'observaciones' => null,
                        'pago_expira_en' => null,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | 10. Crear pago
                    |--------------------------------------------------------------------------
                    |
                    | No se generan pagos PayPal.
                    | Los pagos quedan aprobados.
                    | Una cancelación no altera el estado del pago.
                    */

                    $metodo = ($contador % 2 === 0)
                        ? 'qr'
                        : 'efectivo';

                    $fechaPago = $esFutura
                        ? $ahora->copy()->subDay()
                        : $inicio->copy()->subHour();

                    DB::table('pagos')->insert([
                        'cita_id' => $citaId,
                        'usuario_id' => $pacienteSeleccionado->user_id,
                        'monto' => 100.00,
                        'moneda' => 'BOB',
                        'metodo' => $metodo,
                        'estado' => 'Aprobado',
                        'referencia' => $metodo === 'qr'
                            ? 'DEMO-QR-' . str_pad(
                                (string) $contador,
                                6,
                                '0',
                                STR_PAD_LEFT
                            )
                            : null,
                        'pagado_en' => $fechaPago,
                        'created_at' => $fechaPago,
                        'updated_at' => $fechaPago,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | 11. Registrar historial de creación
                    |--------------------------------------------------------------------------
                    */

                    $fechaRegistro = $esFutura
                        ? $ahora->copy()->subDays(2)
                        : $inicio->copy()->subDays(3);

                    DB::table('historial_citas')->insert([
                        'cita_id' => $citaId,
                        'usuario_id' => $usuarioResponsable,
                        'accion' => 'Cita programada',
                        'fecha_anterior' => null,
                        'hora_anterior' => null,
                        'fecha_nueva' => $fechaTexto,
                        'hora_nueva' => $inicio->format('H:i:s'),
                        'detalle' => 'Cita registrada en el sistema.',
                        'created_at' => $fechaRegistro,
                        'updated_at' => $fechaRegistro,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | 12. Registrar el resultado de la cita
                    |--------------------------------------------------------------------------
                    */

                    if ($estado !== 'Programada') {

                        if ($estado === 'Atendida') {
                            $accion = 'Consulta atendida';
                            $fechaAccion = $inicio->copy()->addMinutes(30);
                            $detalle = 'El paciente asistió y recibió atención médica.';
                        } elseif ($estado === 'Inasistencia') {
                            $accion = 'Inasistencia registrada';
                            $fechaAccion = $inicio->copy()->addMinutes(30);
                            $detalle = 'El paciente no asistió a su cita.';
                        } else {
                            $accion = 'Cita cancelada';

                            $fechaAccion = $esFutura
                                ? $fechaRegistro->copy()->addHours(2)
                                : $inicio->copy()->subHours(2);

                            $detalle = 'La cita fue cancelada.';
                        }

                        DB::table('historial_citas')->insert([
                            'cita_id' => $citaId,
                            'usuario_id' => $usuarioResponsable,
                            'accion' => $accion,
                            'fecha_anterior' => $fechaTexto,
                            'hora_anterior' => $inicio->format('H:i:s'),
                            'fecha_nueva' => null,
                            'hora_nueva' => null,
                            'detalle' => $detalle,
                            'created_at' => $fechaAccion,
                            'updated_at' => $fechaAccion,
                        ]);
                    }
                }
            }

            $this->command->info(
                "Proceso completado. Se generaron {$contador} citas."
            );
        });
    }
}
