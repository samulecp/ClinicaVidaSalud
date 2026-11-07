
@extends('layouts.paciente')

@section('title', 'Mi panel')

@section('header')
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">
            Portal del paciente
        </p>

        <h2 class="mt-2 text-2xl font-bold text-gray-900">
            ¡Hola, {{ $paciente->nombres }}!
        </h2>

        <p class="mt-2 text-sm text-gray-600">
            Gestiona tus citas médicas, consulta tus próximas consultas
            y mantén actualizada tu información personal.
        </p>
    </div>
@endsection

@section('content')

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Bienvenida --}}
        

        {{-- Indicadores --}}
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Próximas citas</p>
                <p class="mt-2 text-3xl font-extrabold text-indigo-600">
                    {{ $totalProximas }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Citas atendidas</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-600">
                    {{ $totalAtendidas }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Número de paciente</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-800">
                    #{{ $paciente->id }}
                </p>
            </div>

        </div>

        {{-- Próximas citas --}}
        <div class="mb-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="p-6">

        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900">
                Próximas citas
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Tus siguientes consultas programadas.
            </p>
        </div>

        @forelse($proximasCitas as $cita)

            <div class="mb-4 rounded-xl border border-slate-200 p-5 last:mb-0">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

                    <div>
                        <h3 class="font-bold text-slate-800">
                            {{ $cita->especialidad->nombre }}
                        </h3>

                        <p class="mt-1 text-sm text-slate-600">
                            Dr. {{ $cita->medico->nombres }}
                            {{ $cita->medico->apellidos }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-500">
                            <span>
                                {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                            </span>

                            <span>
                                {{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}
                            </span>
                        </div>
                    </div>

                    <div class="sm:text-right">
                        <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                            {{ $cita->estado }}
                        </span>

                        <p class="mt-2 text-xs text-slate-500">
                            Código: {{ $cita->codigo }}
                        </p>
                    </div>

                </div>
            </div>

        @empty

            <div class="rounded-xl bg-slate-50 px-5 py-10 text-center">
                <h3 class="font-semibold text-slate-700">
                    Todavía no tienes citas programadas.
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Aquí aparecerán tus próximas consultas médicas.
                </p>
            </div>

        @endforelse

    </div>
</div>

        {{-- Datos personales --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-800">
                    Mis datos personales
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Información registrada en la clínica.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre completo</p>
                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $paciente->nombres }} {{ $paciente->apellidos }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Carnet de identidad</p>
                    <p class="mt-2 font-semibold text-slate-800">{{ $paciente->ci }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Correo electrónico</p>
                    <p class="mt-2 font-semibold text-slate-800">{{ $paciente->correo }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Teléfono</p>
                    <p class="mt-2 font-semibold text-slate-800">{{ $paciente->telefono }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Fecha de nacimiento</p>
                    <p class="mt-2 font-semibold text-slate-800">
                        {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dirección</p>
                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $paciente->direccion ?: 'No registrada' }}
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
