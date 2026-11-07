
@extends(auth()->user()->rol === 'recepcionista' ? 'layouts.recepcion' : 'layouts.admin')

@php
    $rutaPacientes = auth()->user()->rol === 'recepcionista'
        ? 'recepcion.pacientes'
        : 'admin.pacientes';
@endphp

@section('title', 'Gestión de pacientes')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Gestión de pacientes</h2>
            <p class="mt-1 text-sm text-gray-600">
                Registra, consulta y administra los pacientes de la clínica.
            </p>
        </div>

        <a href="{{ route($rutaPacientes . '.create') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 sm:w-auto">
            + Registrar paciente
        </a>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Búsqueda --}}
    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
        <form action="{{ route($rutaPacientes . '.index') }}"
              method="GET"
              class="flex flex-col gap-3 sm:flex-row sm:items-center">

            <input
                type="text"
                name="buscar"
                value="{{ $buscar }}"
                placeholder="Buscar por CI, nombres o apellidos..."
                class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            >

            <div class="grid grid-cols-2 gap-2 sm:flex">
                <button type="submit"
                        class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">
                    Buscar
                </button>

                <a href="{{ route($rutaPacientes . '.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    {{-- Listado --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-4 sm:px-5">
            <h3 class="font-semibold text-gray-900">Listado de pacientes</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $pacientes->total() }} pacientes registrados
            </p>
        </div>

        {{-- VISTA PARA COMPUTADORA --}}
        <div class="hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-5 py-4 font-semibold">CI</th>
                            <th class="px-5 py-4 font-semibold">Paciente</th>
                            <th class="px-5 py-4 font-semibold">Teléfono</th>
                            <th class="px-5 py-4 font-semibold">Correo</th>
                            <th class="px-5 py-4 font-semibold">Estado</th>
                            <th class="px-5 py-4 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 text-gray-800">
                        @forelse($pacientes as $paciente)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">{{ $paciente->ci }}</td>

                                <td class="px-5 py-4 font-semibold text-gray-900">
                                    {{ $paciente->nombres }} {{ $paciente->apellidos }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $paciente->telefono ?: '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $paciente->correo ?: '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if($paciente->estado)
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route($rutaPacientes . '.edit', $paciente) }}"
                                           class="rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Editar
                                        </a>

                                        <form action="{{ route($rutaPacientes . '.estado', $paciente) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Deseas cambiar el estado de este paciente?')">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="rounded-md px-3 py-2 text-xs font-semibold
                                                    {{ $paciente->estado
                                                        ? 'bg-red-50 text-red-700 hover:bg-red-100'
                                                        : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                {{ $paciente->estado ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                    No se encontraron pacientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- VISTA PARA CELULARES --}}
        <div class="space-y-3 p-3 md:hidden">
            @forelse($pacientes as $paciente)
                <article class="rounded-xl border border-gray-200 bg-white p-4">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="break-words font-bold text-gray-900">
                                {{ $paciente->nombres }} {{ $paciente->apellidos }}
                            </h4>

                            <p class="mt-1 text-sm text-gray-500">
                                CI: {{ $paciente->ci }}
                            </p>
                        </div>

                        @if($paciente->estado)
                            <span class="shrink-0 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                Activo
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                Inactivo
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2 border-t border-gray-100 pt-3 text-sm">
                        <p>
                            <span class="font-semibold text-gray-700">Teléfono:</span>
                            <span class="text-gray-600">{{ $paciente->telefono ?: '—' }}</span>
                        </p>

                        <p class="break-all">
                            <span class="font-semibold text-gray-700">Correo:</span>
                            <span class="text-gray-600">{{ $paciente->correo ?: '—' }}</span>
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route($rutaPacientes . '.edit', $paciente) }}"
                           class="rounded-lg bg-blue-50 px-3 py-2.5 text-center text-sm font-semibold text-blue-700 hover:bg-blue-100">
                            Editar
                        </a>

                        <form action="{{ route($rutaPacientes . '.estado', $paciente) }}"
                              method="POST"
                              onsubmit="return confirm('¿Deseas cambiar el estado de este paciente?')">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="w-full rounded-lg px-3 py-2.5 text-sm font-semibold
                                    {{ $paciente->estado
                                        ? 'bg-red-50 text-red-700 hover:bg-red-100'
                                        : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                {{ $paciente->estado ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>

                </article>
            @empty
                <div class="py-10 text-center text-sm text-gray-500">
                    No se encontraron pacientes.
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="border-t border-gray-200 p-4 sm:p-5">
            {{ $pacientes->links() }}
        </div>

    </section>
</div>
@endsection
