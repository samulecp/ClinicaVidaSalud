<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EspecialidadController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $especialidades = Especialidad::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where('nombre', 'ilike', "%{$buscar}%");
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.especialidades.index', compact(
            'especialidades',
            'buscar'
        ));
    }

    public function create()
    {
        return view('admin.especialidades.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                'unique:especialidades,nombre'
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $datos['estado'] = true;

        Especialidad::create($datos);

        return redirect()
            ->route('admin.especialidades.index')
            ->with('success', 'Especialidad registrada correctamente.');
    }

    public function edit(Especialidad $especialidad)
    {
        return view('admin.especialidades.edit', compact('especialidad'));
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $datos = $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('especialidades', 'nombre')
                    ->ignore($especialidad->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $especialidad->update($datos);

        return redirect()
            ->route('admin.especialidades.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function cambiarEstado(Especialidad $especialidad)
    {
        $especialidad->estado = !$especialidad->estado;
        $especialidad->save();

        return redirect()
            ->route('admin.especialidades.index')
            ->with('success', $especialidad->estado
                ? 'Especialidad activada correctamente.'
                : 'Especialidad desactivada correctamente.');
    }
}
