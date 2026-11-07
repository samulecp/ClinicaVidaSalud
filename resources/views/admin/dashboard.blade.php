
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">

    {{-- ENCABEZADO --}}
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 md:text-3xl">
                Panel de administración
            </h2>

            <p class="mt-1 text-gray-500">
                Bienvenido al sistema de gestión clínica.
            </p>
        </div>

        <div class="rounded-lg bg-white px-4 py-3 shadow-sm">
            <p class="font-semibold text-gray-800">
                {{ auth()->user()->name }}
            </p>
            <p class="text-sm text-gray-500">Administrador</p>
        </div>
    </header>

    {{-- INDICADORES --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Pacientes registrados</p>
                <span class="rounded-lg bg-blue-50 p-2 text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-blue-700">
                {{ $totalPacientes }}
            </p>
            <a href="{{ route('admin.pacientes.index') }}"
               class="mt-3 inline-block text-sm font-medium text-blue-700 hover:underline">
                Ver pacientes →
            </a>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Médicos registrados</p>
                <span class="rounded-lg bg-green-50 p-2 text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-3.314 0-6 2.239-6 5v2h12v-2c0-2.761-2.686-5-6-5z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-green-600">
                {{ $totalMedicos }}
            </p>
            <a href="{{ route('admin.medicos.index') }}"
               class="mt-3 inline-block text-sm font-medium text-green-700 hover:underline">
                Ver médicos →
            </a>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Citas programadas</p>
                <span class="rounded-lg bg-purple-50 p-2 text-purple-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14H4V6a1 1 0 011-1z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-purple-600">
                {{ $totalCitasProgramadas }}
            </p>
            <a href="{{ route('admin.citas.index') }}"
               class="mt-3 inline-block text-sm font-medium text-purple-700 hover:underline">
                Ver citas →
            </a>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Especialidades</p>
                <span class="rounded-lg bg-orange-50 p-2 text-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-orange-500">
                {{ $totalEspecialidades }}
            </p>
            <a href="{{ route('admin.especialidades.index') }}"
               class="mt-3 inline-block text-sm font-medium text-orange-600 hover:underline">
                Ver especialidades →
            </a>
        </div>

    </div>

    {{-- BIENVENIDA --}}
    <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <h3 class="text-xl font-bold text-gray-800">
            Sistema de gestión de citas médicas
        </h3>

        <p class="mt-3 leading-relaxed text-gray-600">
            Desde este panel podrás administrar pacientes, médicos,
            especialidades, horarios y citas médicas de Clínica Vida Salud.
            También podrás gestionar las cuentas de recepción y configurar
            el funcionamiento general del sistema.
        </p>
    </section>

</div>
@endsection
