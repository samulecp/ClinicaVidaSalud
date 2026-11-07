
@extends('layouts.admin')

@section('title', 'Gestión de médicos')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Gestión de médicos</h2>
            <p class="mt-1 text-sm text-gray-600">
                Administra los médicos y sus especialidades.
            </p>
        </div>

        <a href="{{ route('admin.medicos.create') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 sm:w-auto">
            + Registrar médico
        </a>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Búsqueda --}}
    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('admin.medicos.index') }}"
              class="flex flex-col gap-3 sm:flex-row">

            <input
                type="text"
                name="buscar"
                value="{{ $buscar }}"
                placeholder="Buscar por registro profesional o nombre..."
                class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            >

            <div class="grid grid-cols-2 gap-2 sm:flex">
                <button type="submit"
                        class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">
                    Buscar
                </button>

                <a href="{{ route('admin.medicos.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    {{-- Listado --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-4 sm:p-5">
            <h3 class="font-semibold text-gray-900">Listado de médicos</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $medicos->total() }} médicos registrados
            </p>
        </div>

        {{-- Computadora --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-5 py-4">Registro profesional</th>
                        <th class="px-5 py-4">Médico</th>
                        <th class="px-5 py-4">Especialidades</th>
                        <th class="px-5 py-4">Teléfono</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-gray-800">
                    @forelse($medicos as $medico)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                {{ $medico->registro_profesional }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-gray-900">
                                {{ $medico->nombres }} {{ $medico->apellidos }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($medico->especialidades as $especialidad)
                                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs text-blue-800">
                                            {{ $especialidad->nombre }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400">Sin especialidad</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                {{ $medico->telefono ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                @if($medico->estado)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.medicos.edit', $medico) }}"
                                       class="rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        Editar
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.medicos.estado', $medico) }}"
                                          onsubmit="return confirm('¿Deseas cambiar el estado de este médico?')">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="rounded-md px-3 py-2 text-xs font-semibold {{ $medico->estado ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $medico->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                                No se encontraron médicos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Celulares --}}
        <div class="space-y-3 p-3 md:hidden">
            @forelse($medicos as $medico)
                <article class="rounded-xl border border-gray-200 p-4">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="break-words font-bold text-gray-900">
                                {{ $medico->nombres }} {{ $medico->apellidos }}
                            </h4>

                            <p class="mt-1 text-sm text-gray-500">
                                Registro: {{ $medico->registro_profesional }}
                            </p>
                        </div>

                        @if($medico->estado)
                            <span class="shrink-0 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                Activo
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                Inactivo
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-3 border-t border-gray-100 pt-3">
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase text-gray-500">
                                Especialidades
                            </p>

                            <div class="flex flex-wrap gap-1">
                                @forelse($medico->especialidades as $especialidad)
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs text-blue-800">
                                        {{ $especialidad->nombre }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-400">Sin especialidad</span>
                                @endforelse
                            </div>
                        </div>

                        <p class="text-sm">
                            <span class="font-semibold text-gray-700">Teléfono:</span>
                            <span class="text-gray-600">{{ $medico->telefono ?: '—' }}</span>
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.medicos.edit', $medico) }}"
                           class="rounded-lg bg-blue-50 px-3 py-2.5 text-center text-sm font-semibold text-blue-700 hover:bg-blue-100">
                            Editar
                        </a>

                        <form method="POST"
                              action="{{ route('admin.medicos.estado', $medico) }}"
                              onsubmit="return confirm('¿Deseas cambiar el estado de este médico?')">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="w-full rounded-lg px-3 py-2.5 text-sm font-semibold {{ $medico->estado ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                {{ $medico->estado ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>

                </article>
            @empty
                <div class="py-10 text-center text-sm text-gray-500">
                    No se encontraron médicos.
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="border-t border-gray-200 p-4 sm:p-5">
            {{ $medicos->links() }}
        </div>

    </section>
</div>
@endsection
