<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteCitasExport;
use Illuminate\Support\Facades\Http;

class ReporteController extends Controller
{
    private function consulta(Request $request)
    {
        return Cita::with([
            'paciente',
            'medico',
            'especialidad'
        ])
        ->when($request->filled('desde'), function ($query) use ($request) {
            $query->whereDate('fecha', '>=', $request->desde);
        })
        ->when($request->filled('hasta'), function ($query) use ($request) {
            $query->whereDate('fecha', '<=', $request->hasta);
        })
        ->when($request->filled('medico_id'), function ($query) use ($request) {
            $query->where('medico_id', $request->medico_id);
        })
        ->when($request->filled('especialidad_id'), function ($query) use ($request) {
            $query->where('especialidad_id', $request->especialidad_id);
        })
        ->when($request->filled('estado'), function ($query) use ($request) {
            $query->where('estado', $request->estado);
        })
        ->orderBy('fecha')
        ->orderBy('hora_inicio');
    }

    public function index(Request $request)
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'medico_id' => ['nullable', 'integer', 'exists:medicos,id'],
            'especialidad_id' => ['nullable', 'integer', 'exists:especialidades,id'],
            'estado' => ['nullable', 'in:Programada,Atendida,Cancelada,Inasistencia,Pendiente de pago'],
        ]);

        $citas = $this->consulta($request)->get();

        $medicos = Medico::orderBy('nombres')->get();
        $especialidades = Especialidad::orderBy('nombre')->get();

        $total = $citas->count();

        $porEstado = $citas->groupBy('estado')
            ->map(fn ($grupo) => $grupo->count());

        $porMedico = $citas->groupBy(
            fn ($cita) => $cita->medico->nombres . ' ' . $cita->medico->apellidos
        )->map(fn ($grupo) => $grupo->count());

        return view('admin.reportes.index', compact(
            'citas',
            'medicos',
            'especialidades',
            'total',
            'porEstado',
            'porMedico'
        ));
    }

    public function pdf(Request $request)
    {
        $citas = $this->consulta($request)->get();

        $pdf = Pdf::loadView('admin.reportes.pdf', compact('citas'));

        return $pdf->download('reporte-citas.pdf');
    }

    public function excel(Request $request)
    {
        $citas = $this->consulta($request)->get();

        return Excel::download(
            new ReporteCitasExport($citas),
            'reporte-citas.xlsx'
        );
    }

    public function csv(Request $request)
    {
        $citas = $this->consulta($request)->get();

        return Excel::download(
            new ReporteCitasExport($citas),
            'reporte-citas.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function interpretarVoz(Request $request)
{
    $request->validate([
        'texto' => ['required', 'string', 'max:500'],
    ]);

    $medicos = Medico::select('id', 'nombres', 'apellidos')
        ->get()
        ->map(fn($m) => [
            'id' => $m->id,
            'nombre' => $m->nombres . ' ' . $m->apellidos,
        ]);

    $especialidades = Especialidad::select('id', 'nombre')
        ->get()
        ->map(fn($e) => [
            'id' => $e->id,
            'nombre' => $e->nombre,
        ]);

    $prompt = "
Eres un asistente que interpreta bÃºsquedas de reportes de citas mÃ©dicas.

Fecha actual: " . now()->format('Y-m-d') . "

Interpreta esta instrucciÃ³n:
" . $request->texto . "

MÃ©dicos disponibles:
" . $medicos->toJson() . "

Especialidades disponibles:
" . $especialidades->toJson() . "

Devuelve exclusivamente un JSON con estas propiedades:
{
  \"desde\": null,
  \"hasta\": null,
  \"medico_id\": null,
  \"especialidad_id\": null,
  \"estado\": null
}

Reglas:
- Las fechas deben estar en formato YYYY-MM-DD.
- Si se menciona este mes, usa desde el primer dÃ­a del mes y hasta hoy.
- Si se menciona este aÃ±o, usa desde el primer dÃ­a del aÃ±o y hasta hoy.
- El mÃ©dico debe coincidir con uno de los disponibles. Devuelve su ID.
- La especialidad debe coincidir con una disponible. Devuelve su ID.
- El estado solo puede ser Programada, Atendida, Cancelada, Inasistencia o Pendiente de pago.
- Si un filtro no se menciona, devuelve null.
- No inventes IDs ni fechas.
";

    try {
        $respuesta = Http::timeout(30)
            ->withHeaders([
                'x-goog-api-key' => config('services.gemini.key'),
            ])
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent',
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.1,
                    ],
                ]
            );

        if (!$respuesta->successful()) {
            return response()->json([
                'error' => 'Gemini no pudo procesar la solicitud.',
                'detalle' => $respuesta->json('error.message'),
            ], 502);
        }

        $contenido = $respuesta->json('candidates.0.content.parts.0.text');

        $filtros = json_decode($contenido, true);

        if (!is_array($filtros)) {
            return response()->json([
                'error' => 'La IA devolviÃ³ una respuesta no vÃ¡lida.',
            ], 422);
        }

        $desde = $filtros['desde'] ?? null;
        $hasta = $filtros['hasta'] ?? null;
        $medicoId = $filtros['medico_id'] ?? null;
        $especialidadId = $filtros['especialidad_id'] ?? null;
        $estado = $filtros['estado'] ?? null;

        if ($desde && (!strtotime($desde) || date('Y-m-d', strtotime($desde)) !== $desde)) {
            $desde = null;
        }

        if ($hasta && (!strtotime($hasta) || date('Y-m-d', strtotime($hasta)) !== $hasta)) {
            $hasta = null;
        }

        if ($desde && $hasta && $hasta < $desde) {
            $desde = null;
            $hasta = null;
        }

        if (!$medicos->contains('id', $medicoId)) {
            $medicoId = null;
        }

        if (!$especialidades->contains('id', $especialidadId)) {
            $especialidadId = null;
        }

        if (!in_array($estado, [
            'Programada',
            'Atendida',
            'Cancelada',
            'Inasistencia',
            'Pendiente de pago',
        ], true)) {
            $estado = null;
        }

        return response()->json([
            'desde' => $desde,
            'hasta' => $hasta,
            'medico_id' => $medicoId,
            'especialidad_id' => $especialidadId,
            'estado' => $estado,
        ]);

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'error' => 'No se pudo conectar con el servicio de IA.',
        ], 500);
    }
}
}

