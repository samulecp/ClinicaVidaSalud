
@extends('layouts.admin')

@section('title', 'Gestión de especialidades')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Especialidades médicas</h2>
            <p class="mt-1 text-sm text-gray-600">
                Administra las áreas de atención de la clínica.
            </p>
        </div>

        <a href="{{ route('admin.especialidades.create') }}"
            class="inline-flex w-fit rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">
            + Registrar especialidad
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.especialidades.index') }}"
            class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="buscar" value="{{ $buscar }}"
                placeholder="Buscar especialidad..."
                class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">

            <div class="flex gap-2">
                <button class="rounded-lg bg-blue-700 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    Buscar
                </button>
                <a href="{{ route('admin.especialidades.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900">Listado de especialidades</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $especialidades->total() }} especialidades registradas
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-5 py-4">Especialidad</th>
                        <th class="px-5 py-4">Descripción</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-gray-800">
                    @forelse($especialidades as $especialidad)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">
                                {{ $especialidad->nombre }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $especialidad->descripcion ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                @if($especialidad->estado)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Activa</span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Inactiva</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('admin.especialidades.edit', $especialidad) }}"
                                        class="rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        Editar
                                    </a>

                                    <form method="POST"
                                        action="{{ route('admin.especialidades.estado', $especialidad) }}"
                                        onsubmit="return confirm('¿Deseas cambiar el estado de esta especialidad?')">
                                        @csrf
                                        @method('PATCH')

                                        <button class="rounded-md px-3 py-2 text-xs font-semibold {{ $especialidad->estado ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $especialidad->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-500">
                                No hay especialidades registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 p-5">
            {{ $especialidades->links() }}
        </div>
    </div>
</div>
@endsection
