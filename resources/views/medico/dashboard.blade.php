
@extends('layouts.medico')

@section('title', 'Dashboard')
@section('header', 'Bienvenido, Dr. ' . $medico->nombres)

@section('content')

<div class="mb-6">
    <p class="text-slate-500">
        Aquí puedes consultar tus citas y organizar tu jornada médica.
    </p>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Citas de hoy</p>
        <p class="mt-3 text-3xl font-bold text-blue-600">
            {{ $citasHoy->count() }}
        </p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Programadas para hoy</p>
        <p class="mt-3 text-3xl font-bold text-emerald-600">
            {{ $citasHoy->where('estado', 'Programada')->count() }}
        </p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Próximas citas</p>
        <p class="mt-3 text-3xl font-bold text-purple-600">
            {{ $proximasCitas->count() }}
        </p>
    </div>

</div>

<div class="mt-8 rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-5">
        <div>
            <h3 class="font-bold text-slate-800">Agenda de hoy</h3>
            <p class="text-sm text-slate-500">
                {{ now()->format('d/m/Y') }}
            </p>
        </div>

        <a href="{{ route('medico.agenda') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Ver agenda completa
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-6 py-4">Hora</th>
                    <th class="px-6 py-4">Paciente</th>
                    <th class="px-6 py-4">Especialidad</th>
                    <th class="px-6 py-4">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($citasHoy as $cita)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4 font-semibold">
                            {{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $cita->paciente->nombres ?? 'Sin paciente' }}
                            {{ $cita->paciente->apellidos ?? '' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $cita->especialidad->nombre ?? 'Sin especialidad' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $cita->estado }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                            No tienes citas programadas para hoy.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
