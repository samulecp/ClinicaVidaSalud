<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TipoCambioBcb
{
    public function obtener(): array
    {
        $claveUltimo = 'tipo_cambio_bcb_ultimo';

        try {
            $respuesta = Http::timeout(10)
                ->retry(2, 500)
                ->get('https://api.factura.bo/ExchangeRate')
                ->throw()
                ->json();

            $tasa = data_get($respuesta, 'datos.usd_bob');

            if (!is_numeric($tasa) || (float) $tasa <= 0) {
                throw new RuntimeException('El BCB no devolvió una cotización válida.');
            }

            $dato = [
                'tasa' => (float) $tasa,
                'fecha' => data_get(
                    $respuesta,
                    'datos.fecha_actualizacion',
                    now()->toDateString()
                ),
                'fuente' => 'Banco Central de Bolivia (BCB)',
            ];

            Cache::put($claveUltimo, $dato, now()->addDays(7));

            return $dato;

        } catch (\Throwable $e) {
            $ultimo = Cache::get($claveUltimo);

            if ($ultimo) {
                return $ultimo;
            }

            throw new RuntimeException(
                'No se pudo consultar el tipo de cambio del BCB y no existe una cotización guardada.'
            );
        }
    }

    public function convertirABolivianos(float $montoBs): array
    {
        $cotizacion = $this->obtener();

        return [
            'monto_bs' => round($montoBs, 2),
            'tipo_cambio' => $cotizacion['tasa'],
            'monto_usd' => round($montoBs / $cotizacion['tasa'], 2),
            'fecha_tipo_cambio' => $cotizacion['fecha'],
            'fuente' => $cotizacion['fuente'],
        ];
    }
}
