<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Models\HistorialCita;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirarReservasPendientes extends Command
{
    protected $signature = 'citas:expirar-pendientes';

    protected $description = 'Cancela las reservas PayPal cuyo tiempo de pago ha vencido';

    public function handle(): int
    {
        $expiradas = 0;

        Cita::query()
            ->where('estado', 'Pendiente de pago')
            ->whereNotNull('pago_expira_en')
            ->where('pago_expira_en', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($citas) use (&$expiradas) {

                foreach ($citas as $cita) {

                    DB::transaction(function () use ($cita, &$expiradas) {

                        $citaBloqueada = Cita::whereKey($cita->id)
                            ->lockForUpdate()
                            ->first();

                        if (
                            !$citaBloqueada ||
                            $citaBloqueada->estado !== 'Pendiente de pago' ||
                            !$citaBloqueada->pago_expira_en ||
                            $citaBloqueada->pago_expira_en->isFuture()
                        ) {
                            return;
                        }

                        $citaBloqueada->update([
                            'estado' => 'Cancelada',
                            'pago_expira_en' => null,
                        ]);

                        $citaBloqueada->pagos()
                            ->where('estado', 'Pendiente')
                            ->update([
                                'estado' => 'Cancelado',
                            ]);

                        HistorialCita::create([
                            'cita_id' => $citaBloqueada->id,
                            'usuario_id' => null,
                            'accion' => 'Reserva PayPal expirada',
                            'detalle' => 'La reserva fue cancelada automáticamente porque vencieron los 15 minutos disponibles para realizar el pago.',
                        ]);

                        $expiradas++;
                    });
                }
            });

        $this->info("Reservas expiradas: {$expiradas}");

        return self::SUCCESS;
    }
}

