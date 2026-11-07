
@extends('layouts.recepcion')

@section('title', 'Dashboard')
@section('header', 'Panel de recepción')

@section('content')

    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-2xl font-bold text-gray-800 md:text-3xl">
                Panel de recepción
            </h2>

            <p class="mt-1 text-gray-500">
                Bienvenido al sistema de gestión clínica.
            </p>
        </div>

        <div class="sm:text-right">
            <p class="font-semibold text-gray-800">
                {{ auth()->user()->name }}
            </p>

            <p class="text-sm text-gray-500">
                Recepcionista
            </p>
        </div>

    </header>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl bg-white p-6 shadow">
            <p class="text-gray-500">Citas de hoy</p>
            <h3 class="mt-3 text-3xl font-bold text-blue-700">
                {{ $citasHoy }}
            </h3>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <p class="text-gray-500">Citas programadas</p>
            <h3 class="mt-3 text-3xl font-bold text-green-600">
                {{ $citasProgramadas }}
            </h3>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <p class="text-gray-500">Citas canceladas</p>
            <h3 class="mt-3 text-3xl font-bold text-purple-600">
                {{ $citasCanceladas }}
            </h3>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <p class="text-gray-500">Pacientes registrados</p>
            <h3 class="mt-3 text-3xl font-bold text-orange-500">
                {{ $pacientesActivos }}
            </h3>
        </div>

    </div>

    {{-- BIENVENIDA --}}
    <section class="mt-8 rounded-xl bg-white p-8 shadow">

        <h3 class="mb-3 text-xl font-bold text-gray-800">
            Gestión de atención médica
        </h3>

        <p class="text-gray-600">
            Desde este panel podrás consultar y registrar pacientes,
            gestionar la información de atención y reprogramar citas
            médicas cuando sea necesario.
        </p>

    </section>

    {{-- ACCESOS RÁPIDOS --}}
    <section class="mt-8">

        <h3 class="mb-5 text-xl font-bold text-gray-800">
            Accesos rápidos
        </h3>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <a href="{{ route('recepcion.pacientes.index') }}"
               class="rounded-xl bg-white p-6 shadow transition hover:shadow-md">

                <h4 class="text-lg font-bold text-gray-800">
                    Gestión de pacientes
                </h4>

                <p class="mt-2 text-sm text-gray-500">
                    Registrar, consultar y actualizar información de pacientes.
                </p>

                <p class="mt-4 font-semibold text-blue-700">
                    Ir a pacientes →
                </p>

            </a>

            <a href="{{ route('recepcion.reprogramacion.index') }}"
               class="rounded-xl bg-white p-6 shadow transition hover:shadow-md">

                <h4 class="text-lg font-bold text-gray-800">
                    Reprogramación de citas
                </h4>

                <p class="mt-2 text-sm text-gray-500">
                    Reprogramar citas afectadas por inconvenientes de atención.
                </p>

                <p class="mt-4 font-semibold text-blue-700">
                    Ir a reprogramación →
                </p>

            </a>

        </div>

    </section>

    {{-- PRÓXIMAS CITAS --}}
    <section class="mt-8 overflow-hidden rounded-xl bg-white shadow">

        <div class="border-b border-gray-200 px-6 py-5">
            <h3 class="text-xl font-bold text-gray-800">
                Próximas citas
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Las siguientes citas programadas en la clínica.
            </p>
        </div>

        @if($proximasCitas->isNotEmpty())

            <div class="overflow-x-auto">

                <table class="w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Hora</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Paciente</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Médico</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Especialidad</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @foreach($proximasCitas as $cita)

                            <tr class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}
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

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="px-6 py-12 text-center">
                <p class="font-semibold text-gray-700">
                    No hay próximas citas programadas.
                </p>

                <p class="mt-2 text-sm text-gray-500">
                    Cuando se registren nuevas citas, aparecerán aquí.
                </p>
            </div>

        @endif

    </section>

@endsection
