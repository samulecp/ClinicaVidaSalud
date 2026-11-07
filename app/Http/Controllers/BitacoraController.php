<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'usuario_id' => ['nullable', 'integer', 'exists:users,id'],
            'modulo' => ['nullable', 'string', 'max:100'],
            'accion' => ['nullable', 'string', 'max:100'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $consulta = Bitacora::with('usuario')
            ->when($request->filled('usuario_id'), function ($query) use ($request) {
                $query->where('user_id', $request->usuario_id);
            })
            ->when($request->filled('modulo'), function ($query) use ($request) {
                $query->where('modulo', $request->modulo);
            })
            ->when($request->filled('accion'), function ($query) use ($request) {
                $query->where('accion', $request->accion);
            })
            ->when($request->filled('desde'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->desde);
            })
            ->when($request->filled('hasta'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->hasta);
            });

        $registros = $consulta
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.bitacora.index', [
            'registros' => $registros,
            'usuarios' => User::orderBy('name')->get(),
            'modulos' => Bitacora::select('modulo')
                ->distinct()
                ->orderBy('modulo')
                ->pluck('modulo'),
            'acciones' => Bitacora::select('accion')
                ->distinct()
                ->orderBy('accion')
                ->pluck('accion'),
        ]);
    }
}
