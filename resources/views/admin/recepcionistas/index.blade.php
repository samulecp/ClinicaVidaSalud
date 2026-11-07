
@extends('layouts.admin')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Gestión de recepcionistas
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Administra las cuentas de acceso del personal de recepción.
        </p>
    </div>

    <a href="{{ route('admin.recepcionistas.create') }}"
       class="rounded-lg px-5 py-2.5 text-sm font-semibold"
       style="background-color: #2563eb; color: white;">
        Crear recepcionista
    </a>
</div>

@if(session('success'))
    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif

<div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="text-lg font-bold text-slate-800">
            Cuentas registradas
        </h2>
    </div>

    <div class="w-full overflow-x-auto">
        <table class="w-full table-fixed divide-y divide-slate-200">

            <colgroup>
                <col style="width: 25%;">
                <col style="width: 30%;">
                <col style="width: 20%;">
                <col style="width: 25%;">
            </colgroup>

            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                        Nombre
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                        Correo electrónico
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                        Estado
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                        Fecha de registro
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($recepcionistas as $recepcionista)
                    <tr class="hover:bg-slate-50">

                        <td class="break-words px-6 py-4 text-sm font-semibold text-slate-800">
                            {{ $recepcionista->name }}
                        </td>

                        <td class="break-words px-6 py-4 text-sm text-slate-600">
                            {{ $recepcionista->email }}
                        </td>

                        <td class="px-6 py-4">
                            @if($recepcionista->estado)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    Activo
                                </span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                            {{ $recepcionista->created_at->format('d/m/Y') }}
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <p class="font-semibold text-slate-700">
                                No hay recepcionistas registrados.
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Crea una cuenta para permitir el acceso al panel de recepción.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if($recepcionistas->hasPages())
        <div class="border-t border-slate-200 px-6 py-4">
            {{ $recepcionistas->links() }}
        </div>
    @endif

</div>

@endsection
