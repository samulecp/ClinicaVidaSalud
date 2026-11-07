
@extends('layouts.recepcion')

@section('title', 'Reprogramación')
@section('header', 'Reprogramación de citas')

@section('content')

<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800">
        Reprogramación de citas
    </h2>

    <p class="mt-2 text-sm text-gray-500">
        Selecciona el día y el horario de atención afectado para buscar las citas que necesitan ser reprogramadas.
    </p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
        <p class="mb-2 font-semibold">No se pudo completar la operación:</p>
        <ul class="list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FILTROS --}}
<div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="border-b border-gray-200 px-6 py-5">
        <h3 class="text-lg font-bold text-gray-800">
            Criterios de búsqueda
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            El médico es opcional. Si no seleccionas uno, se incluirán todos.
        </p>
    </div>

    <form method="GET" action="{{ route('recepcion.reprogramacion.consultar') }}"
          class="p-6">

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <label for="fecha" class="mb-2 block text-sm font-semibold text-gray-700">
                    Fecha afectada
                </label>

                <input type="date"
                       id="fecha"
                       name="fecha"
                       value="{{ request('fecha') }}"
                       min="{{ now()->format('Y-m-d') }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label for="hora_inicio" class="mb-2 block text-sm font-semibold text-gray-700">
                    Desde
                </label>

                <input type="time"
                       id="hora_inicio"
                       name="hora_inicio"
                       value="{{ request('hora_inicio') }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label for="hora_fin" class="mb-2 block text-sm font-semibold text-gray-700">
                    Hasta
                </label>

                <input type="time"
                       id="hora_fin"
                       name="hora_fin"
                       value="{{ request('hora_fin') }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label for="medico_id" class="mb-2 block text-sm font-semibold text-gray-700">
                    Médico (opcional)
                </label>

                <select id="medico_id"
                        name="medico_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">

                    <option value="">Todos los médicos</option>

                    @foreach($medicos as $medico)
                        <option value="{{ $medico->id }}"
                            {{ request('medico_id') == $medico->id ? 'selected' : '' }}>
                            Dr. {{ $medico->nombres }} {{ $medico->apellidos }}
                        </option>
                    @endforeach

                </select>
            </div>

        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-3">

            <a href="{{ route('recepcion.reprogramacion.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                Limpiar
            </a>

            <button type="submit"
                    class="rounded-lg px-6 py-3 text-sm font-semibold"
                    style="background-color: #2563eb; color: white;">
                Buscar citas afectadas
            </button>

        </div>

    </form>

</div>

{{-- RESULTADOS --}}
@if(isset($citas))

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-5">

            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    Citas encontradas
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Citas programadas que coinciden con el día y rango horario seleccionado.
                </p>
            </div>

            <span class="rounded-full bg-blue-100 px-4 py-2 text-sm font-bold text-blue-700">
                {{ $citas->count() }} citas
            </span>

        </div>

        @if($citas->isNotEmpty())

            <div class="overflow-x-auto">

                <table class="w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Código</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Paciente</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Médico</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Especialidad</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Horario actual</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @foreach($citas as $cita)

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-blue-700">
                                    {{ $cita->codigo }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $cita->paciente->nombres }}
                                    {{ $cita->paciente->apellidos }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Dr. {{ $cita->medico->nombres }}
                                    {{ $cita->medico->apellidos }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $cita->especialidad->nombre }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                                    <br>
                                    {{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}
                                    -
                                    {{ \Carbon\Carbon::parse($cita->hora_fin)->format('H:i') }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            {{-- CONFIRMAR REPROGRAMACIÓN --}}
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-6">

                <div class="mb-5">
                    <h4 class="font-bold text-gray-800">
                        Confirmar reprogramación
                    </h4>

                    <p class="mt-1 text-sm text-gray-500">
                        Las {{ $citas->count() }} citas encontradas se moverán al siguiente horario disponible de cada médico, después de las {{ request('hora_fin') }}.
                    </p>
                </div>

                <form method="POST"
                      action="{{ route('recepcion.reprogramacion.ejecutar') }}"
                      onsubmit="return confirm('¿Estás seguro de reprogramar todas las citas encontradas? Esta operación modificará sus fechas y horarios.');">

                    @csrf

                    <input type="hidden" name="fecha" value="{{ request('fecha') }}">
                    <input type="hidden" name="hora_inicio" value="{{ request('hora_inicio') }}">
                    <input type="hidden" name="hora_fin" value="{{ request('hora_fin') }}">
                    <input type="hidden" name="medico_id" value="{{ request('medico_id') }}">

                    <div class="flex justify-end">
                        <button type="submit"
                                class="rounded-lg px-6 py-3 text-sm font-semibold"
                                style="background-color: #16a34a; color: white;">
                            Confirmar reprogramación
                        </button>
                    </div>

                </form>

            </div>

        @else

            <div class="px-6 py-12 text-center">
                <p class="font-semibold text-gray-700">
                    No se encontraron citas afectadas.
                </p>

                <p class="mt-2 text-sm text-gray-500">
                    Prueba con otra fecha, horario o médico.
                </p>
            </div>

        @endif

    </div>

@endif

@endsection
