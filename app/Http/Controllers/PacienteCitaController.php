<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ConfiguracionSistema;
use App\Models\HistorialCita;
use App\Models\Medico;
use App\Models\Pago;
use App\Models\Especialidad;
use App\Services\DisponibilidadCitas;
use App\Services\PayPalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

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
     * Retorno desde PayPal: capturar y verificar el pago.
     */
    public function paypalRetorno(
        Request $request,
        PayPalService $paypal
    ) {
        $ordenId = $request->query('token');

        if (!$ordenId) {
            return redirect()
                ->route('paciente.citas')
                ->with('error', 'No se recibió la referencia del pago.');
        }

        $pago = Pago::where('paypal_order_id', $ordenId)
            ->where('usuario_id', $request->user()->id)
            ->with('cita')
            ->firstOrFail();

        if ($pago->estado === 'Aprobado') {
            return redirect()
                ->route('paciente.citas')
                ->with('success', 'Tu pago ya fue confirmado. Código: ' . $pago->cita->codigo);
        }

        $cita = $pago->cita;

        if (
            $cita->estado !== 'Pendiente de pago' ||
            !$cita->pago_expira_en ||
            $cita->pago_expira_en->isPast()
        ) {
            $this->cancelarReservaPendiente(
                $cita,
                $pago,
                'El tiempo para completar el pago expiró.'
            );

            return redirect()
                ->route('paciente.citas')
                ->with('error', 'El tiempo para pagar expiró. Debes realizar una nueva reserva.');
        }

        
try {
    // Consultar la orden directamente en PayPal.
    $orden = $paypal->consultarOrden($ordenId);

    // La orden debe pertenecer a esta cita y estar aprobada.
    $codigoOrden = data_get(
        $orden,
        'purchase_units.0.custom_id'
    );

    $referenciaOrden = data_get(
        $orden,
        'purchase_units.0.reference_id'
    );

    $montoOrden = data_get(
        $orden,
        'purchase_units.0.amount.value'
    );

    $monedaOrden = data_get(
        $orden,
        'purchase_units.0.amount.currency_code'
    );

    if (
        ($orden['status'] ?? null) !== 'APPROVED' ||
        $codigoOrden !== $cita->codigo ||
        $referenciaOrden !== $cita->codigo ||
        $monedaOrden !== 'USD' ||
        number_format((float) $montoOrden, 2, '.', '') !==
            number_format((float) $pago->monto_pasarela, 2, '.', '')
    ) {
        throw new \RuntimeException(
            'La orden de PayPal no coincide con la reserva.'
        );
    }

    // Capturar el pago después de verificar la orden.
    $captura = $paypal->capturarOrden($ordenId);

    $capturaId = data_get(
        $captura,
        'purchase_units.0.payments.captures.0.id'
    );

    $montoCapturado = data_get(
        $captura,
        'purchase_units.0.payments.captures.0.amount.value'
    );

    $monedaCapturada = data_get(
        $captura,
        'purchase_units.0.payments.captures.0.amount.currency_code'
    );

    $codigoCapturado = data_get(
        $captura,
        'purchase_units.0.custom_id'
    );

    if (
        !$capturaId ||
        $monedaCapturada !== 'USD' ||
        !$montoCapturado ||
        number_format((float) $montoCapturado, 2, '.', '') !==
            number_format((float) $pago->monto_pasarela, 2, '.', '') ||
        $codigoCapturado !== $cita->codigo
    ) {
        throw new \RuntimeException(
            'Los datos de la captura no coinciden con el pago registrado.'
        );
    }


            DB::transaction(function () use (
                $pago,
                $cita,
                $captura,
                $capturaId,
                $montoCapturado
            ) {
                $pagoBloqueado = Pago::whereKey($pago->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $citaBloqueada = Cita::whereKey($cita->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($pagoBloqueado->estado === 'Aprobado') {
                    return;
                }

                if (
                    $citaBloqueada->estado !== 'Pendiente de pago' ||
                    !$citaBloqueada->pago_expira_en ||
                    $citaBloqueada->pago_expira_en->isPast()
                ) {
                    throw new \RuntimeException('La reserva ya no está vigente.');
                }

                $pagoBloqueado->update([
                    'estado' => 'Aprobado',
                    'paypal_capture_id' => $capturaId,
                    'pagado_en' => now(),
                    'monto_pasarela' => $montoCapturado,
                    'datos_pasarela' => $captura,
                ]);

                $citaBloqueada->update([
                    'estado' => 'Programada',
                    'pago_expira_en' => null,
                ]);

                HistorialCita::create([
                    'cita_id' => $citaBloqueada->id,
                    'usuario_id' => $pagoBloqueado->usuario_id,
                    'accion' => 'Pago PayPal aprobado',
                    'fecha_anterior' => $citaBloqueada->fecha->format('Y-m-d'),
                    'hora_anterior' => $citaBloqueada->hora_inicio,
                    'fecha_nueva' => $citaBloqueada->fecha->format('Y-m-d'),
                    'hora_nueva' => $citaBloqueada->hora_inicio,
                    'detalle' => 'Pago de ' . $montoCapturado . ' USD verificado por PayPal. Cita confirmada.',
                ]);
            });

            return redirect()
                ->route('paciente.citas')
                ->with('success', '¡Pago aprobado! Tu cita quedó confirmada. Código: ' . $cita->codigo);

        } catch (Throwable $e) {
            return redirect()
                ->route('paciente.citas')
                ->with('error', 'No se pudo verificar el pago. Si PayPal ya realizó un cobro, contacta con la clínica antes de volver a pagar.');
        }
    }

    /**
     * El paciente vuelve desde PayPal sin completar el pago.
     */
    public function paypalCancelado(Request $request)
    {
        $ordenId = $request->query('token');

        if ($ordenId) {
            $pago = Pago::where('paypal_order_id', $ordenId)
                ->where('usuario_id', $request->user()->id)
                ->with('cita')
                ->first();

            if ($pago && $pago->estado === 'Pendiente') {
                $this->cancelarReservaPendiente(
                    $pago->cita,
                    $pago,
                    'El paciente canceló el proceso de pago en PayPal.'
                );
            }
        }

        return redirect()
            ->route('paciente.citas')
            ->with('error', 'El pago fue cancelado. Puedes realizar una nueva reserva.');
    }

    /**
     * Cancelar una reserva pendiente y liberar el horario.
     */
    private function cancelarReservaPendiente(
        Cita $cita,
        Pago $pago,
        string $detalle
    ): void {
        DB::transaction(function () use ($cita, $pago, $detalle) {
            $citaBloqueada = Cita::whereKey($cita->id)
                ->lockForUpdate()
                ->first();

            $pagoBloqueado = Pago::whereKey($pago->id)
                ->lockForUpdate()
                ->first();

            if (!$citaBloqueada || !$pagoBloqueado) {
                return;
            }

            if (
                $citaBloqueada->estado !== 'Pendiente de pago' ||
                $pagoBloqueado->estado !== 'Pendiente'
            ) {
                return;
            }

            $citaBloqueada->update([
                'estado' => 'Cancelada',
                'pago_expira_en' => null,
            ]);

            $pagoBloqueado->update([
                'estado' => 'Cancelado',
            ]);

            HistorialCita::create([
                'cita_id' => $citaBloqueada->id,
                'usuario_id' => $pagoBloqueado->usuario_id,
                'accion' => 'Reserva cancelada por falta de pago',
                'fecha_anterior' => $citaBloqueada->fecha->format('Y-m-d'),
                'hora_anterior' => $citaBloqueada->hora_inicio,
                'fecha_nueva' => null,
                'hora_nueva' => null,
                'detalle' => $detalle,
            ]);
        });
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

            Pago::where('cita_id', $citaBloqueada->id)
                ->where('estado', 'Pendiente')
                ->update(['estado' => 'Cancelado']);

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
