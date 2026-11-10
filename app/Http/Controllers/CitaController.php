<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ConfiguracionSistema;
use App\Models\Especialidad;
use App\Models\HistorialCita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Pago;
use App\Services\DisponibilidadCitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CitaController extends Controller
{
    /**
     * Listado de citas.
     */
    public function index()
    {
        $citas = Cita::with([
            'paciente',
            'medico',
            'especialidad',
            'pagos',
        ])
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->paginate(10);

        return view('admin.citas.index', compact('citas'));
    }

    /**
     * Formulario para programar una cita.
     */
    public function create()
    {
        $pacientes = Paciente::where('estado', true)
            ->orderBy('apellidos')
            ->get();

        $especialidades = Especialidad::where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.citas.create', compact(
            'pacientes',
            'especialidades'
        ));
    }

    /**
     * Guardar una cita.
     */
    public function store(
        Request $request,
        DisponibilidadCitas $disponibilidad
    ) {
        $datos = $request->validate([
            'paciente_id' => [
                'required',
                'exists:pacientes,id',
            ],
            'medico_id' => [
                'required',
                'exists:medicos,id',
            ],
            'especialidad_id' => [
                'required',
                'exists:especialidades,id',
            ],
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'hora_inicio' => [
                'required',
                'date_format:H:i',
            ],
            'motivo' => [
                'nullable',
                'string',
                'max:500',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'metodo_pago' => [
                'required',
                'in:efectivo,qr',
            ],
            'referencia_pago' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        // Evitar reservar una hora que ya pasó.
        if (
            $datos['fecha'] === now()->format('Y-m-d')
            && $datos['hora_inicio'] <= now()->format('H:i')
        ) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'No puedes reservar una hora que ya pasó.',
            ]);
        }

        DB::transaction(function () use ($datos, $disponibilidad) {
            DB::connection()->beforeExecuting(
    function ($query, $bindings, $connection) {
        Log::info('CITA SQL ANTES DE EJECUTAR', [
            'sql' => $query,
            'bindings' => $bindings,
            'transaction_level' => $connection->transactionLevel(),
        ]);
    }
);
            Log::info('CITA DEBUG 1: inicio de transacción', [
                'paciente_id' => $datos['paciente_id'],
                'medico_id' => $datos['medico_id'],
                'especialidad_id' => $datos['especialidad_id'],
                'transaction_level' => DB::transactionLevel(),
            ]);
            try {
    DB::select('SELECT 1 AS prueba_inicio');

    Log::info('CITA PRUEBA: SELECT inicial correcto');
} catch (\Throwable $e) {
    Log::error('CITA PRUEBA: SELECT inicial falló', [
        'mensaje' => $e->getMessage(),
        'clase' => get_class($e),
    ]);

    throw $e;
}
            /*
             * Bloquear al médico durante la operación.
             * Las reservas concurrentes deben usar este mismo bloqueo.
             */
            try {
    $medico = Medico::whereKey($datos['medico_id'])
        ->lockForUpdate()
        ->firstOrFail();
} catch (\Throwable $e) {
    Log::error('CITA ERROR ORIGINAL MEDICO', [
        'mensaje' => $e->getMessage(),
        'clase' => get_class($e),
        'sql' => $e instanceof \Illuminate\Database\QueryException
            ? $e->getSql()
            : null,
        'bindings' => $e instanceof \Illuminate\Database\QueryException
            ? $e->getBindings()
            : null,
        'trace' => $e->getTraceAsString(),
    ]);

    throw $e;
}
            Log::info('CITA DEBUG 2: médico consultado', [
                'medico_id' => $medico->id,
            ]);
            Log::info('CITA DEBUG 3: antes de consultar paciente');
            try {
                DB::select('SELECT 1 AS prueba');

                Log::info('CITA DEBUG 3A: conexión y transacción operativas');
            } catch (\Throwable $e) {
                Log::error('CITA DEBUG 3A: fallo antes de consultar paciente', [
                    'mensaje' => $e->getMessage(),
                    'clase' => get_class($e),
                    'transaction_level' => DB::transactionLevel(),
                ]);

                throw $e;
            }

            $paciente = Paciente::findOrFail(
                $datos['paciente_id']
            );
            Log::info('CITA DEBUG 4: paciente consultado', [
                'paciente_id' => $paciente->id,
            ]);
            $especialidad = Especialidad::findOrFail(
                $datos['especialidad_id']
            );

            if (! $medico->estado) {
                throw ValidationException::withMessages([
                    'medico_id' => 'El médico no está activo.',
                ]);
            }

            if (! $paciente->estado) {
                throw ValidationException::withMessages([
                    'paciente_id' => 'El paciente no está activo.',
                ]);
            }

            if (! $especialidad->estado) {
                throw ValidationException::withMessages([
                    'especialidad_id' => 'La especialidad no está activa.',
                ]);
            }

            // Comprobar que el médico atiende esa especialidad.
            $atiendeEspecialidad = $medico->especialidades()
                ->where('especialidades.id', $especialidad->id)
                ->where('especialidades.estado', true)
                ->exists();

            if (! $atiendeEspecialidad) {
                throw ValidationException::withMessages([
                    'especialidad_id' => 'El médico no atiende esa especialidad.',
                ]);
            }

            /*
             * Verificar nuevamente la disponibilidad en el servidor.
             * No confiamos únicamente en lo que muestra el formulario.
             */
            $turno = $disponibilidad->buscarTurno(
                (int) $medico->id,
                $datos['fecha'],
                $datos['hora_inicio']
            );

            if (! $turno) {
                throw ValidationException::withMessages([
                    'hora_inicio' => 'Ese horario ya no está disponible. Selecciona otro.',
                ]);
            }

            // Generar código único de la cita.
            do {
                $codigo = 'CIT-'.
                    now()->format('Ymd').
                    '-'.
                    Str::upper(Str::random(6));
            } while (Cita::where('codigo', $codigo)->exists());

            $cita = Cita::create([
                'codigo' => $codigo,
                'paciente_id' => $paciente->id,
                'medico_id' => $medico->id,
                'especialidad_id' => $especialidad->id,
                'fecha' => $datos['fecha'],
                'hora_inicio' => $turno['hora_inicio'],
                'hora_fin' => $turno['hora_fin'],
                'estado' => 'Programada',
                'motivo' => $datos['motivo'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
            // Registrar el pago recibido por administración o recepción.
            Pago::create([
                'cita_id' => $cita->id,
                'usuario_id' => auth()->id(),
                'monto' => ConfiguracionSistema::actual()->precio_consulta,
                'moneda' => 'BOB',
                'metodo' => $datos['metodo_pago'],
                'estado' => 'Aprobado',
                'referencia' => $datos['referencia_pago'] ?? null,
                'pagado_en' => now(),
            ]);

            // Registrar la acción en el historial.
            HistorialCita::create([
                'cita_id' => $cita->id,
                'usuario_id' => auth()->id(),
                'accion' => 'Cita programada',
                'detalle' => 'La cita fue programada desde el módulo de '.
    (auth()->user()->rol === 'recepcionista' ? 'recepción.' : 'administración.'),
            ]);
        });

        return redirect()
            ->route($this->rutaCitas().'.index')
            ->with('success', 'Cita programada correctamente.');
    }

    /**
     * Aprobar un pago QR verificado por administración o recepción.
     */
    public function aprobarPago(Request $request, Cita $cita)
    {
        $datos = $request->validate([
            'referencia_pago' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($cita, $datos) {
            $citaBloqueada = Cita::whereKey($cita->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($citaBloqueada->estado !== 'Pendiente de pago') {
                throw ValidationException::withMessages([
                    'pago' => 'Esta cita ya no está pendiente de pago.',
                ]);
            }

            $pago = Pago::where('cita_id', $citaBloqueada->id)
                ->where('estado', 'Pendiente')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (! $pago) {
                throw ValidationException::withMessages([
                    'pago' => 'No se encontró un pago pendiente para esta cita.',
                ]);
            }

            $pago->update([
                'usuario_id' => auth()->id(),
                'metodo' => 'qr',
                'estado' => 'Aprobado',
                'referencia' => $datos['referencia_pago'] ?? $pago->referencia,
                'pagado_en' => now(),
            ]);

            $citaBloqueada->update([
                'estado' => 'Programada',
                'pago_expira_en' => null,
            ]);

            HistorialCita::create([
                'cita_id' => $citaBloqueada->id,
                'usuario_id' => auth()->id(),
                'accion' => 'Pago QR aprobado',
                'detalle' => 'Recepción/administración verificó el comprobante de transferencia QR y aprobó el pago. La cita pasó a estado Programada.',
            ]);
        });

        return redirect()
            ->route($this->rutaCitas().'.index')
            ->with('success', 'Pago QR aprobado. La cita quedó programada.');
    }

    /**
     * Cancelar una cita sin eliminarla de la base de datos.
     */
    public function cancelar(Cita $cita)
    {
        DB::transaction(function () use ($cita) {

            // Bloquear la cita mientras se realiza la operación.
            $cita = Cita::whereKey($cita->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($cita->estado, ['Programada', 'Confirmada'])) {
                throw ValidationException::withMessages([
                    'cita' => 'Esta cita no se puede cancelar porque ya fue atendida, cancelada o está marcada como inasistencia.',
                ]);
            }

            $cita->update([
                'estado' => 'Cancelada',
            ]);

            HistorialCita::create([
                'cita_id' => $cita->id,
                'usuario_id' => auth()->id(),
                'accion' => 'Cita cancelada',
                'detalle' => 'La cita fue cancelada desde el módulo de administración.',
            ]);
        });

        return redirect()
            ->route('admin.citas.index')
            ->with('success', 'La cita fue cancelada correctamente. El horario quedó disponible nuevamente.');
    }

    private function esRecepcion(): bool
    {
        return auth()->user()->rol === 'recepcionista';
    }

    private function rutaCitas(): string
    {
        return $this->esRecepcion()
            ? 'recepcion.citas'
            : 'admin.citas';
    }
}
