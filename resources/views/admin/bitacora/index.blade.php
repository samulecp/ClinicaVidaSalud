
@extends('layouts.admin')

@section('title', 'Bitácora del sistema')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Bitácora del sistema
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Consulta las acciones realizadas por los usuarios del sistema.
        </p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">
            Filtros de búsqueda
        </h2>

        <form method="GET" action="{{ route('admin.bitacora.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Usuario
                </label>
                <select name="usuario_id"
                    class="w-full rounded-lg border-gray-300">
                    <option value="">Todos los usuarios</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}"
                            @selected(request('usuario_id') == $usuario->id)>
                            {{ $usuario->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Módulo
                </label>
                <select name="modulo"
                    class="w-full rounded-lg border-gray-300">
                    <option value="">Todos los módulos</option>
                    @foreach($modulos as $modulo)
                        <option value="{{ $modulo }}"
                            @selected(request('modulo') == $modulo)>
                            {{ $modulo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Acción
                </label>
                <select name="accion"
                    class="w-full rounded-lg border-gray-300">
                    <option value="">Todas las acciones</option>
                    @foreach($acciones as $accion)
                        <option value="{{ $accion }}"
                            @selected(request('accion') == $accion)>
                            {{ $accion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Desde
                </label>
                <input type="date" name="desde" value="{{ request('desde') }}"
                    class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Hasta
                </label>
                <input type="date" name="hasta" value="{{ request('hasta') }}"
                    class="w-full rounded-lg border-gray-300">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white hover:bg-blue-700">
                    Filtrar
                </button>

                <a href="{{ route('admin.bitacora.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-gray-700 hover:bg-gray-50">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="border-b border-gray-200 p-5">
            <h2 class="text-lg font-semibold text-gray-800">
                Registro de actividades
            </h2>
            <p class="text-sm text-gray-500">
                {{ $registros->total() }} registros encontrados.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Fecha y hora</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Usuario</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Módulo</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Acción</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Descripción</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">IP</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($registros as $registro)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                {{ $registro->created_at->format('d/m/Y H:i:s') }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-800">
                                {{ $registro->usuario?->name ?? 'Usuario eliminado' }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $registro->modulo }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $registro->accion }}
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $registro->descripcion }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500">
                                {{ $registro->ip ?? 'No disponible' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                                No se encontraron registros de actividad.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 p-4">
            {{ $registros->links() }}
        </div>
    </div>

</div>
@endsection
