<?php

namespace App\Http\Middleware;

use App\Models\Bitacora;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegistrarBitacora
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            Auth::check() &&
            in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) &&
            $response->getStatusCode() >= 200 &&
            $response->getStatusCode() < 400 &&
            ! $request->routeIs('logout') &&
            ! $request->routeIs('reportes.interpretarVoz')
        ) {
            $ruta = $request->route();

            $segmento = $request->segment(1);

            $modulos = [
                'admin' => 'Administración',
                'recepcion' => 'Recepción',
                'medico' => 'Médico',
                'paciente' => 'Paciente',
            ];

            $modulo = $modulos[$segmento] ?? 'General';

            $acciones = [
                'POST' => 'Crear o registrar',
                'PUT' => 'Actualizar',
                'PATCH' => 'Modificar',
                'DELETE' => 'Eliminar',
            ];

            Bitacora::create([
                'user_id' => Auth::id(),
                'modulo' => $modulo,
                'accion' => $acciones[$request->method()],
                'descripcion' => $ruta?->getName() ?? $request->path(),
                'metodo' => $request->method(),
                'ruta' => $request->path(),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
