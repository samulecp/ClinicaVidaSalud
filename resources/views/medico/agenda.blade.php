
@extends('layouts.medico')

@section('title', 'Mi agenda')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Mi agenda de citas</h1>
    <p class="mt-1 text-sm text-slate-500">
        Consulta tus citas médicas y gestiona la atención de tus pacientes.
    </p>
</div>

{{-- FILTRO DE FECHA --}}
<div class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <form method="GET" action="{{ route('medico.agenda') }}"
          class="flex flex-wrap items-end gap-4">

        <div>
            <label for="fecha" class="mb-2 block text-sm font-semibold text-slate-700">
                Seleccionar fecha
            </label>

            <input
                type="date"
                id="fecha"
                name="fecha"
                value="{{ $fecha }}"
                class="rounded-lg border border-slate-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500"
            >
        </div>

        <button type="submit"
            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
            Consultar
        </button>

        <a href="{{ route('medico.agenda') }}"
           class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            Hoy
        </a>
    </form>
</div>

{{-- TABLA DE CITAS --}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="font-bold text-slate-800">Citas del día</h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">

            <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                <tr>
                    <th class="whitespace-nowrap px-6 py-4">Horario</th>
                    <th class="px-6 py-4">Paciente</th>
                    <th class="px-6 py-4">Especialidad</th>
                    <th class="px-6 py-4">Motivo</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">

                @forelse($citas as $cita)

                    <tr class="hover:bg-slate-50">

                        <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-700">
                            {{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}
                            -
                            {{ \Carbon\Carbon::parse($cita->hora_fin)->format('H:i') }}
                        </td>

                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">
                                {{ $cita->paciente->nombres ?? '' }}
                                {{ $cita->paciente->apellidos ?? '' }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $cita->paciente->ci ?? '' }}
                            </p>
                        </td>

                        <td class="px-6 py-4 text-slate-600">
                            {{ $cita->especialidad->nombre ?? '—' }}
                        </td>

                        <td class="px-6 py-4 text-slate-600">
                            {{ $cita->motivo ?? '—' }}
                        </td>

                        <td class="px-6 py-4">
                            @if($cita->estado === 'Programada')
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                    Programada
                                </span>
                            @elseif($cita->estado === 'Atendida')
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    Atendida
                                </span>
                            @elseif($cita->estado === 'Inasistencia')
                                <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                                    Inasistencia
                                </span>
                            @elseif($cita->estado === 'Cancelada')
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    Cancelada
                                </span>
                            @else
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    {{ $cita->estado }}
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('medico.citas.show', $cita) }}"
                               class="inline-block whitespace-nowrap rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                Ver consulta
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="font-semibold text-slate-600">
                                No hay citas para esta fecha.
                            </p>
                            <p class="mt-1 text-sm text-slate-400">
                                Selecciona otra fecha para consultar tu agenda.
                            </p>
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 px-6 py-4">
        <p class="text-sm text-slate-500">
            Total de citas: <strong class="text-slate-800">{{ $citas->count() }}</strong>
        </p>
    </div>

</div>

@endsection
