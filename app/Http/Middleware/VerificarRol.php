<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $usuario = auth()->user();

        if (!$usuario->estado) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Tu cuenta está deshabilitada.',
                ]);
        }

        if (!in_array($usuario->rol, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}