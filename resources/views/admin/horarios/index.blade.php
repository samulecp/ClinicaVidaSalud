
@extends('layouts.admin')

@section('title', 'Horarios médicos')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Horarios médicos</h1>
            <p class="mt-1 text-gray-600">
                Administra la disponibilidad de los médicos.
            </p>
        </div>

        <a href="{{ route('admin.horarios.create') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 sm:w-auto">
            + Registrar horario
        </a>
    </div>

    {{-- MENSAJES --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- BÚSQUEDA --}}
    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET"
              action="{{ route('admin.horarios.index') }}"
              class="flex flex-col gap-3 sm:flex-row">

            <input type="text"
                   name="buscar"
                   value="{{ $buscar }}"
                   placeholder="Buscar médico..."
                   class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:max-w-sm">

            <div class="grid grid-cols-2 gap-2 sm:flex">
                <button type="submit"
                        class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">
                    Buscar
                </button>

                <a href="{{ route('admin.horarios.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    {{-- LISTADO --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-4 sm:p-5">
            <h2 class="font-semibold text-gray-900">Horarios registrados</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $horarios->total() }} horarios registrados
            </p>
        </div>

        {{-- TABLA PARA COMPUTADORA --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-5 py-4">Médico</th>
                        <th class="px-5 py-4">Día de atención</th>
                        <th class="px-5 py-4">Hora de inicio</th>
                        <th class="px-5 py-4">Hora de finalización</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-gray-800">
                    @forelse($horarios as $horario)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $horario->medico->nombres }}
                                    {{ $horario->medico->apellidos }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Registro: {{ $horario->medico->registro_profesional }}
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ [
                                    1 => 'Lunes',
                                    2 => 'Martes',
                                    3 => 'Miércoles',
                                    4 => 'Jueves',
                                    5 => 'Viernes',
                                    6 => 'Sábado',
                                    7 => 'Domingo'
                                ][$horario->dia_semana] ?? 'No definido' }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ substr($horario->hora_inicio, 0, 5) }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ substr($horario->hora_fin, 0, 5) }}
                            </td>

                            <td class="px-5 py-4">
                                @if($horario->estado)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('admin.horarios.edit', $horario) }}"
                                       class="rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        Editar
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.horarios.estado', $horario) }}"
                                          onsubmit="return confirm('¿Deseas cambiar el estado de este horario?')">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="rounded-md px-3 py-2 text-xs font-semibold {{ $horario->estado ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $horario->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                                No hay horarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TARJETAS PARA CELULARES --}}
        <div class="space-y-3 p-3 md:hidden">
            @forelse($horarios as $horario)
                <article class="rounded-xl border border-gray-200 p-4">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="break-words font-bold text-gray-900">
                                {{ $horario->medico->nombres }}
                                {{ $horario->medico->apellidos }}
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Registro: {{ $horario->medico->registro_profesional }}
                            </p>
                        </div>

                        @if($horario->estado)
                            <span class="shrink-0 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                Activo
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">
                                Inactivo
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Día de atención</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800">
                                {{ [
                                    1 => 'Lunes',
                                    2 => 'Martes',
                                    3 => 'Miércoles',
                                    4 => 'Jueves',
                                    5 => 'Viernes',
                                    6 => 'Sábado',
                                    7 => 'Domingo'
                                ][$horario->dia_semana] ?? 'No definido' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500">Horario</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800">
                                {{ substr($horario->hora_inicio, 0, 5) }}
                                -
                                {{ substr($horario->hora_fin, 0, 5) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.horarios.edit', $horario) }}"
                           class="rounded-lg bg-blue-50 px-3 py-2.5 text-center text-sm font-semibold text-blue-700 hover:bg-blue-100">
                            Editar
                        </a>

                        <form method="POST"
                              action="{{ route('admin.horarios.estado', $horario) }}"
                              onsubmit="return confirm('¿Deseas cambiar el estado de este horario?')">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="w-full rounded-lg px-3 py-2.5 text-sm font-semibold {{ $horario->estado ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                {{ $horario->estado ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>

                </article>
            @empty
                <div class="py-10 text-center text-sm text-gray-500">
                    No hay horarios registrados.
                </div>
            @endforelse
        </div>

        {{-- PAGINACIÓN --}}
        <div class="border-t border-gray-200 p-4 sm:p-5">
            {{ $horarios->links() }}
        </div>

    </section>
</div>
@endsection
