<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\HistorialCita;
use App\Services\DisponibilidadCitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReprogramacionMasivaController extends Controller
{
    /**
     * Mostrar la pantalla de reprogramación masiva.
     */
    public function index()
    {
        $medicos = Medico::where('estado', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('recepcion.reprogramacion.index', [
            'medicos' => $medicos,
            'citas' => collect(),
            'filtros' => [],
            'consultado' => false,
        ]);
    }

    /**
     * Consultar las citas afectadas según los filtros.
     */
    public function consultar(Request $request)
    {
        $datos = $this->validarFiltros($request);

        $citas = $this->obtenerCitasAfectadas($datos);

        $medicos = Medico::where('estado', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('recepcion.reprogramacion.index', [
            'medicos' => $medicos,
            'citas' => $citas,
            'filtros' => $datos,
            'consultado' => true,
        ]);
    }

    /**
     * Ejecutar la reprogramación automática masiva.
     */
    public function ejecutar(
        Request $request,
        DisponibilidadCitas $disponibilidad
    ) {
        $datos = $this->validarFiltros($request);

        $cantidad = DB::transaction(function () use (
            $datos,
            $request,
            $disponibilidad
        ) {

            /*
             * Obtener los médicos involucrados.
             */
            $consultaInicial = $this->obtenerCitasAfectadas($datos);

            if ($consultaInicial->isEmpty()) {
                throw ValidationException::withMessages([
                    'reprogramacion' => 'No existen citas que coincidan con los filtros seleccionados.',
                ]);
            }

            $medicosIds = $consultaInicial
                ->pluck('medico_id')
                ->unique()
                ->sort()
                ->values();

            /*
             * Bloquear los médicos para evitar conflictos
             * con reservas o reprogramaciones simultáneas.
             */
            $medicosBloqueados = Medico::whereIn('id', $medicosIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /*
             * Volver a consultar y bloquear las citas afectadas.
             */
            $citas = Cita::whereDate('fecha', $datos['fecha'])
                ->where('estado', 'Programada')
                ->whereTime('hora_inicio', '<', $datos['hora_fin'])
                ->whereTime('hora_fin', '>', $datos['hora_inicio'])
                ->when(
                    !empty($datos['medico_id']),
                    fn ($query) => $query->where(
                        'medico_id',
                        $datos['medico_id']
                    )
                )
                ->orderBy('medico_id')
                ->orderBy('hora_inicio')
                ->lockForUpdate()
                ->get();

            if ($citas->isEmpty()) {
                throw ValidationException::withMessages([
                    'reprogramacion' => 'Las citas seleccionadas ya no están disponibles para reprogramación.',
                ]);
            }

            $fechaAfectada = Carbon::parse($datos['fecha']);
            $horaLimite = $datos['hora_fin'];

            foreach ($citas as $cita) {

                if (!isset($medicosBloqueados[$cita->medico_id])) {
                    throw new \RuntimeException(
                        'No se pudo bloquear el médico de una de las citas.'
                    );
                }

                /*
                 * Buscar el siguiente turno disponible
                 * para el mismo médico.
                 */
                $nuevoTurno = $this->buscarSiguienteTurno(
                    $cita,
                    $fechaAfectada,
                    $horaLimite,
                    $disponibilidad
                );

                if (!$nuevoTurno) {
                    throw new \RuntimeException(
                        'No se encontró disponibilidad futura para completar la reprogramación.'
                    );
                }

                $fechaAnterior = $cita->fecha->format('Y-m-d');
                $horaAnterior = $cita->hora_inicio;

                /*
                 * Actualizar la misma cita.
                 * Se conserva su código y sus relaciones.
                 */
                $cita->update([
                    'fecha' => $nuevoTurno['fecha'],
                    'hora_inicio' => $nuevoTurno['hora_inicio'],
                    'hora_fin' => $nuevoTurno['hora_fin'],
                ]);

                /*
                 * Registrar el cambio en el historial.
                 */
                HistorialCita::create([
                    'cita_id' => $cita->id,
                    'usuario_id' => $request->user()->id,
                    'accion' => 'Cita reprogramada automáticamente',
                    'fecha_anterior' => $fechaAnterior,
                    'hora_anterior' => $horaAnterior,
                    'fecha_nueva' => $nuevoTurno['fecha'],
                    'hora_nueva' => $nuevoTurno['hora_inicio'],
                    'detalle' => 'Reprogramación masiva realizada por recepción. Periodo afectado: '
                        . $datos['fecha']
                        . ' de '
                        . $datos['hora_inicio']
                        . ' a '
                        . $datos['hora_fin']
                        . '.',
                ]);
            }

            return $citas->count();

        }, 3);

        return redirect()
            ->route('recepcion.reprogramacion.index')
            ->with(
                'success',
                "Se reprogramaron correctamente {$cantidad} citas."
            );
    }

    /**
     * Validar los filtros de búsqueda.
     */
    private function validarFiltros(Request $request): array
    {
        $datos = $request->validate([
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'hora_inicio' => [
                'required',
                'date_format:H:i',
            ],
            'hora_fin' => [
                'required',
                'date_format:H:i',
                'after:hora_inicio',
            ],
            'medico_id' => [
                'nullable',
                'exists:medicos,id',
            ],
        ]);

        return $datos;
    }

    /**
     * Obtener las citas que se cruzan con el periodo seleccionado.
     */
    private function obtenerCitasAfectadas(array $datos)
    {
        return Cita::with([
                'paciente',
                'medico',
                'especialidad',
            ])
            ->whereDate('fecha', $datos['fecha'])
            ->where('estado', 'Programada')
            ->whereTime('hora_inicio', '<', $datos['hora_fin'])
            ->whereTime('hora_fin', '>', $datos['hora_inicio'])
            ->when(
                !empty($datos['medico_id']),
                fn ($query) => $query->where(
                    'medico_id',
                    $datos['medico_id']
                )
            )
            ->orderBy('medico_id')
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * Buscar el primer turno libre posterior al periodo afectado.
     */
    private function buscarSiguienteTurno(
        Cita $cita,
        Carbon $fechaAfectada,
        string $horaLimite,
        DisponibilidadCitas $disponibilidad
    ): ?array {

        /*
         * Buscar hacia adelante durante un máximo de un año.
         * Los turnos ocupados se descartan automáticamente.
         */
        for ($dia = 0; $dia < 366; $dia++) {

            $fechaBusqueda = $fechaAfectada
                ->copy()
                ->addDays($dia);

            $fechaTexto = $fechaBusqueda->format('Y-m-d');

            $turnos = $disponibilidad->obtenerTurnos(
                (int) $cita->medico_id,
                $fechaTexto,
                (int) $cita->id
            );

            foreach ($turnos as $turno) {

                /*
                 * El primer día solamente se aceptan
                 * turnos posteriores al final del periodo.
                 */
                if (
                    $dia === 0 &&
                    $turno['hora_inicio'] < $horaLimite
                ) {
                    continue;
                }

                return [
                    'fecha' => $fechaTexto,
                    'hora_inicio' => $turno['hora_inicio'],
                    'hora_fin' => $turno['hora_fin'],
                ];
            }
        }

        return null;
    }
}
