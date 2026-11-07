
@extends('layouts.admin')

@section('title', 'Reportes')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reportes de citas</h1>
        <p class="text-sm text-gray-500">Consulta y exporta información de las citas médicas.</p>
    </div>

    {{-- FILTROS --}}
    <div class="rounded-xl bg-white p-5 shadow">

        <h2 class="mb-4 text-lg font-semibold text-gray-800">Filtros del reporte</h2>
        {{-- BÚSQUEDA POR VOZ --}}
<div class="mb-6 rounded-xl bg-white p-5 shadow">
    <h2 class="mb-2 text-lg font-semibold text-gray-800">
        Búsqueda por voz con IA
    </h2>

    <p class="mb-4 text-sm text-gray-600">
        Habla y describe qué reporte necesitas. Por ejemplo:
        "Quiero ver las citas atendidas de este mes".
    </p>

    <div class="flex flex-col gap-3 sm:flex-row">
        <textarea
            id="textoVoz"
            rows="2"
            placeholder="Aquí aparecerá lo que digas..."
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
        ></textarea>

        <button
            type="button"
            id="btnMicrofono"
            class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700"
        >
            🎙️ Hablar
        </button>

        <button
    type="button"
    id="btnInterpretar"
    class="rounded-lg bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700"
>
    Aplicar con IA
</button>
    </div>

    <p id="estadoVoz" class="mt-2 text-sm text-gray-500"></p>
</div>
        <form method="GET" action="{{ route('admin.reportes.index') }}"
              class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">

            <div>
                <label class="mb-1 block text-sm font-medium">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}"
                       class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}"
                       class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Médico</label>
                <select name="medico_id" class="w-full rounded-lg border-gray-300">
                    <option value="">Todos</option>
                    @foreach($medicos as $medico)
                        <option value="{{ $medico->id }}"
                            @selected(request('medico_id') == $medico->id)>
                            {{ $medico->nombres }} {{ $medico->apellidos }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Especialidad</label>
                <select name="especialidad_id" class="w-full rounded-lg border-gray-300">
                    <option value="">Todas</option>
                    @foreach($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}"
                            @selected(request('especialidad_id') == $especialidad->id)>
                            {{ $especialidad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Estado</label>
                <select name="estado" class="w-full rounded-lg border-gray-300">
                    <option value="">Todos</option>
                    @foreach(['Programada', 'Atendida', 'Cancelada', 'Inasistencia', 'Pendiente de pago'] as $estado)
                        <option value="{{ $estado }}"
                            @selected(request('estado') == $estado)>
                            {{ $estado }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-2 lg:col-span-5">
                <button type="submit"
                    class="rounded-lg bg-blue-700 px-5 py-2.5 text-white hover:bg-blue-800">
                    Aplicar filtros
                </button>

                <a href="{{ route('admin.reportes.index') }}"
                   class="rounded-lg bg-gray-200 px-5 py-2.5 text-gray-700">
                    Limpiar
                </a>
            </div>

        </form>
    </div>

    {{-- EXPORTACIONES --}}
    <div class="flex flex-wrap gap-3">

        <a href="{{ route('admin.reportes.pdf', request()->query()) }}"
           class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">
            Exportar PDF
        </a>

        <a href="{{ route('admin.reportes.excel', request()->query()) }}"
           class="rounded-lg bg-green-700 px-4 py-2 text-white hover:bg-green-800">
            Exportar Excel
        </a>

        <a href="{{ route('admin.reportes.csv', request()->query()) }}"
           class="rounded-lg bg-gray-700 px-4 py-2 text-white hover:bg-gray-800">
            Exportar CSV
        </a>

    </div>

    {{-- INDICADORES --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">

        <div class="rounded-xl bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Total de citas</p>
            <p class="mt-2 text-3xl font-bold text-blue-700">{{ $total }}</p>
        </div>

        @foreach(['Programada', 'Atendida', 'Cancelada', 'Inasistencia', 'Pendiente de pago'] as $estado)
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">{{ $estado }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-800">
                    {{ $porEstado->get($estado, 0) }}
                </p>
            </div>
        @endforeach

    </div>
    {{-- GRÁFICOS --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    <div class="rounded-xl bg-white p-5 shadow">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">
            Citas por estado
        </h2>

        <div class="relative h-72">
            <canvas id="graficoEstados"></canvas>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">
            Citas por médico
        </h2>

        <div class="relative h-72">
            <canvas id="graficoMedicos"></canvas>
        </div>
    </div>

</div>
    {{-- TABLA --}}
    <div class="overflow-hidden rounded-xl bg-white shadow">

        <div class="border-b p-5">
            <h2 class="text-lg font-semibold text-gray-800">Resultados del reporte</h2>
            <p class="text-sm text-gray-500">{{ $total }} registros encontrados</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Paciente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Médico</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Especialidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Horario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Estado</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($citas as $cita)
                        <tr>
                            <td class="px-4 py-3">{{ $cita->codigo }}</td>
                            <td class="px-4 py-3">{{ $cita->fecha?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                {{ $cita->paciente->nombres }} {{ $cita->paciente->apellidos }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $cita->medico->nombres }} {{ $cita->medico->apellidos }}
                            </td>
                            <td class="px-4 py-3">{{ $cita->especialidad->nombre }}</td>
                            <td class="px-4 py-3">{{ $cita->hora_inicio }} - {{ $cita->hora_fin }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                    {{ $cita->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No se encontraron citas con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const estadosLabels = @json($porEstado->keys()->values());
    const estadosDatos = @json($porEstado->values()->values());

    const medicosLabels = @json($porMedico->keys()->values());
    const medicosDatos = @json($porMedico->values()->values());

    new Chart(document.getElementById('graficoEstados'), {
        type: 'doughnut',
        data: {
            labels: estadosLabels,
            datasets: [{
                data: estadosDatos,
                backgroundColor: [
                    '#2563eb',
                    '#16a34a',
                    '#dc2626',
                    '#f59e0b'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    new Chart(document.getElementById('graficoMedicos'), {
        type: 'bar',
        data: {
            labels: medicosLabels,
            datasets: [{
                label: 'Cantidad de citas',
                data: medicosDatos,
                backgroundColor: '#2563eb',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const boton = document.getElementById('btnMicrofono');
    const texto = document.getElementById('textoVoz');
    const estado = document.getElementById('estadoVoz');

    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        boton.disabled = true;
        estado.textContent = 'Tu navegador no admite reconocimiento de voz.';
        return;
    }

    const reconocimiento = new SpeechRecognition();

    reconocimiento.lang = 'es-BO';
    reconocimiento.continuous = false;
    reconocimiento.interimResults = true;
    reconocimiento.maxAlternatives = 1;

    let escuchando = false;

    boton.addEventListener('click', function () {
        if (escuchando) return;

        texto.value = '';
        estado.textContent = 'Te estoy escuchando. Habla ahora...';
        boton.textContent = 'Escuchando...';
        escuchando = true;

        try {
            reconocimiento.start();
        } catch (error) {
            escuchando = false;
            boton.textContent = '🎙️ Hablar';
            estado.textContent = 'No se pudo iniciar el micrófono. Intenta nuevamente.';
        }
    });

    reconocimiento.onresult = function (evento) {
        let transcripcion = '';

        for (let i = 0; i < evento.results.length; i++) {
            transcripcion += evento.results[i][0].transcript;
        }

        texto.value = transcripcion;
        estado.textContent = 'Voz reconocida correctamente.';
    };

    reconocimiento.onerror = function (evento) {
        if (evento.error === 'no-speech') {
            estado.textContent = 'No detecté tu voz. Acércate al micrófono y vuelve a intentar.';
        } else if (evento.error === 'audio-capture') {
            estado.textContent = 'No se encontró un micrófono disponible.';
        } else if (evento.error === 'not-allowed') {
            estado.textContent = 'El navegador no tiene permiso para usar el micrófono.';
        } else {
            estado.textContent = 'Error de reconocimiento: ' + evento.error;
        }
    };

    reconocimiento.onend = function () {
        escuchando = false;
        boton.textContent = '🎙️ Hablar';
    };

    const interpretar = document.getElementById('btnInterpretar');

interpretar.addEventListener('click', async function () {
    const instruccion = texto.value.trim();

    if (!instruccion) {
        estado.textContent = 'Primero debes dictar una búsqueda.';
        return;
    }

    interpretar.disabled = true;
    interpretar.textContent = 'Interpretando...';
    estado.textContent = 'La IA está analizando tu solicitud...';

    try {
        const respuesta = await fetch(
            @json(route('admin.reportes.interpretarVoz')),
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token())
                },
                body: JSON.stringify({
                    texto: instruccion
                })
            }
        );

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.error || 'No se pudo interpretar la búsqueda.');
        }

        const formulario = document.querySelector(
            'form[action="{{ route('admin.reportes.index') }}"]'
        );

        ['desde', 'hasta', 'medico_id', 'especialidad_id', 'estado'].forEach(campo => {
            const elemento = formulario.querySelector(`[name="${campo}"]`);

            if (elemento) {
                elemento.value = datos[campo] ?? '';
            }
        });

        estado.textContent = 'Búsqueda interpretada. Aplicando filtros...';

        formulario.submit();

    } catch (error) {
        estado.textContent = error.message;
    } finally {
        interpretar.disabled = false;
        interpretar.textContent = 'Aplicar con IA';
    }
});
});
</script>
@endsection
