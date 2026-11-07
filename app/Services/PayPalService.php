<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PayPalService
{
    private function baseUrl(): string
    {
        return rtrim(config('services.paypal.base_url'), '/');
    }

    /**
     * Obtener token de acceso de PayPal.
     */
    private function obtenerToken(): string
    {
        return Cache::remember(
            'paypal_access_token',
            now()->addMinutes(8),
            function () {

                $clientId = config('services.paypal.client_id');
                $secret = config('services.paypal.secret');

                if (!$clientId || !$secret) {
                    throw new RuntimeException(
                        'Faltan las credenciales de PayPal.'
                    );
                }

                $respuesta = Http::timeout(20)
                    ->asForm()
                    ->withBasicAuth($clientId, $secret)
                    ->acceptJson()
                    ->post(
                        $this->baseUrl() . '/v1/oauth2/token',
                        ['grant_type' => 'client_credentials']
                    );

                if (!$respuesta->successful()) {
                    throw new RuntimeException(
                        'No se pudo autenticar con PayPal.'
                    );
                }

                $token = $respuesta->json('access_token');

                if (!$token) {
                    throw new RuntimeException(
                        'PayPal no devolvió un token válido.'
                    );
                }

                return $token;
            }
        );
    }

    /**
     * Crear una orden de pago.
     */
    public function crearOrden(
    string $montoUsd,
    string $codigoCita,
    string $returnUrl,
    string $cancelUrl
): array {
    $respuesta = Http::timeout(20)
        ->withToken($this->obtenerToken())
        ->acceptJson()
        ->withHeaders([
            'PayPal-Request-Id' => (string) Str::uuid(),
        ])
        ->post($this->baseUrl() . '/v2/checkout/orders', [
            'intent' => 'CAPTURE',

            'purchase_units' => [[
                'reference_id' => $codigoCita,
                'custom_id' => $codigoCita,
                'description' => 'Consulta médica - Clínica Vida Salud',
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $montoUsd,
                ],
            ]],

            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'brand_name' => 'Clínica Vida Salud',
                        'user_action' => 'PAY_NOW',
                        'landing_page' => 'LOGIN',
                        'shipping_preference' => 'NO_SHIPPING',
                        'return_url' => $returnUrl,
                        'cancel_url' => $cancelUrl,
                    ],
                ],
            ],
        ]);

    if (!$respuesta->successful()) {
        throw new RuntimeException(
            'No se pudo crear la orden de PayPal: ' .
            $respuesta->body()
        );
    }

    $datos = $respuesta->json();
    $enlaces = collect($datos['links'] ?? []);

    $urlAprobacion = $enlaces->firstWhere('rel', 'approve')['href']
        ?? $enlaces->firstWhere('rel', 'payer-action')['href']
        ?? null;

    if (!$urlAprobacion || empty($datos['id'])) {
        throw new RuntimeException(
            'PayPal no devolvió una URL de aprobación válida.'
        );
    }

    return [
        'id' => $datos['id'],
        'url' => $urlAprobacion,
        'datos' => $datos,
    ];
}

    /**
     * Consultar una orden directamente en PayPal.
     */
    public function consultarOrden(string $ordenId): array
    {
        $respuesta = Http::timeout(20)
            ->withToken($this->obtenerToken())
            ->acceptJson()
            ->get(
                $this->baseUrl() .
                '/v2/checkout/orders/' . urlencode($ordenId)
            );

        if (!$respuesta->successful()) {
            throw new RuntimeException(
                'No se pudo consultar la orden en PayPal.'
            );
        }

        $datos = $respuesta->json();

        if (($datos['id'] ?? null) !== $ordenId) {
            throw new RuntimeException(
                'El identificador de la orden no coincide.'
            );
        }

        return $datos;
    }

    /**
     * Capturar el pago de una orden aprobada.
     */
    public function capturarOrden(string $ordenId): array
    {
        // Identificador estable para evitar capturas duplicadas
        // al reintentar la misma operación.
        $requestId = 'cap-' . substr(
            hash('sha256', $ordenId),
            0,
            32
        );

        $respuesta = Http::timeout(30)
            ->withToken($this->obtenerToken())
            ->acceptJson()
            ->withHeaders([
                'PayPal-Request-Id' => $requestId,
            ])
            ->withBody('{}', 'application/json')
            ->post(
                $this->baseUrl() .
                '/v2/checkout/orders/' .
                urlencode($ordenId) .
                '/capture'
            );

        if (!$respuesta->successful()) {
            throw new RuntimeException(
                'PayPal no pudo capturar el pago.'
            );
        }

        $datos = $respuesta->json();

        $estadoCaptura = data_get(
            $datos,
            'purchase_units.0.payments.captures.0.status'
        );

        if (
            ($datos['status'] ?? null) !== 'COMPLETED' ||
            $estadoCaptura !== 'COMPLETED'
        ) {
            throw new RuntimeException(
                'PayPal no confirmó la captura del pago.'
            );
        }

        return $datos;
    }
}
