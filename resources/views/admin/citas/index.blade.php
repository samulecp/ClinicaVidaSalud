
@extends(auth()->user()->rol === 'recepcionista' ? 'layouts.recepcion' : 'layouts.admin')

@php
    $rutaCitas = auth()->user()->rol === 'recepcionista'
        ? 'recepcion.citas'
        : 'admin.citas';
@endphp

@section('content')

<div class="w-full space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Citas médicas
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Administración y seguimiento de las citas médicas registradas.
            </p>
        </div>

        <a href="{{ route($rutaCitas . '.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 font-bold text-white shadow-sm transition hover:bg-indigo-700">
            <span class="text-lg">＋</span>
            Programar cita
        </a>

    </div>

    {{-- MENSAJES --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TARJETA PRINCIPAL --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-gray-800">
                    Citas registradas
                </h2>
                <p class="mt-1 text-xs text-gray-500">
                    Consulta, administra y cancela citas médicas.
                </p>
            </div>

            <span class="text-sm text-gray-500">
                Página {{ $citas->currentPage() }} de {{ $citas->lastPage() }}
            </span>
        </div>

        {{-- VISTA PARA COMPUTADORAS --}}
        <div class="hidden w-full overflow-x-auto md:block">

            <table class="w-full min-w-[950px] divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        @foreach(['Código', 'Paciente', 'Médico', 'Especialidad', 'Fecha y hora', 'Estado', 'Acciones'] as $columna)
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                {{ $columna }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($citas as $cita)

                        <tr class="transition hover:bg-gray-50">

                            <td class="px-4 py-4 text-sm font-semibold text-indigo-600">
                                {{ $cita->codigo }}
                            </td>

                            <td class="px-4 py-4">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $cita->paciente->apellidos }}, {{ $cita->paciente->nombres }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    CI: {{ $cita->paciente->ci }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                <p class="text-sm font-medium text-gray-800">
                                    Dr. {{ $cita->medico->apellidos }}, {{ $cita->medico->nombres }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $cita->medico->registro_profesional }}
                                </p>
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{ $cita->especialidad->nombre }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ substr($cita->hora_inicio, 0, 5) }}
                                    - {{ substr($cita->hora_fin, 0, 5) }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                @php
                                    $colores = [
                                        'Programada' => 'background-color: #dbeafe; color: #1d4ed8;',
                                        'Pendiente de pago' => 'background-color: #fef3c7; color: #92400e;',
                                        'Confirmada' => 'background-color: #dbeafe; color: #1d4ed8;',
                                        'Atendida' => 'background-color: #f3f4f6; color: #374151;',
                                        'Cancelada' => 'background-color: #fee2e2; color: #b91c1c;',
                                        'Inasistencia' => 'background-color: #fef3c7; color: #92400e;',
                                    ];

                                    $color = $colores[$cita->estado] ?? 'background-color: #f3f4f6; color: #374151;';
                                @endphp

                                <span style="{{ $color }}"
                                      class="inline-flex whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold">
                                    {{ $cita->estado }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                @php
                                    $pagoPendiente = $cita->pagos
                                        ->where('estado', 'Pendiente')
                                        ->sortByDesc('id')
                                        ->first();
                                @endphp

                                <div class="min-w-40 space-y-2">
                                    @if($cita->estado === 'Pendiente de pago' && $pagoPendiente && $pagoPendiente->metodo === 'qr')
                                        <form action="{{ route($rutaCitas . '.aprobar-pago', $cita) }}"
                                              method="POST" class="space-y-2"
                                              onsubmit="return confirm('¿Verificaste que el dinero llegó a la cuenta y confirmas la aprobación?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="referencia_pago" maxlength="255"
                                                   placeholder="N.º de referencia (opcional)"
                                                   class="w-full rounded-md border-gray-300 text-xs">
                                            <button type="submit"
                                                class="w-full whitespace-nowrap rounded-lg bg-green-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-green-700">
                                                Aprobar pago QR
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($cita->estado, ['Pendiente de pago', 'Programada', 'Confirmada'], true))
                                        <form action="{{ route($rutaCitas . '.cancelar', $cita) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Cancelar esta cita? El pago conservará su estado actual.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="w-full whitespace-nowrap rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-red-700">
                                                Cancelar cita
                                            </button>
                                        </form>
                                    @endif

                                    @if(!($cita->estado === 'Pendiente de pago' && $pagoPendiente && $pagoPendiente->metodo === 'qr') && !in_array($cita->estado, ['Pendiente de pago', 'Programada', 'Confirmada'], true))
                                        <span class="text-xs text-gray-400">Sin acciones</span>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <span class="text-4xl text-gray-300">📅</span>
                                <p class="mt-3 font-semibold text-gray-700">
                                    No hay citas registradas
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    Cuando programes una cita aparecerá en este listado.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- VISTA PARA CELULARES --}}
        <div class="space-y-4 bg-gray-50 p-4 md:hidden">

            @forelse($citas as $cita)

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                    <div class="flex items-center justify-between gap-2 border-b border-gray-100 p-4">

                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">
                                Código de cita
                            </p>
                            <p class="break-all font-bold text-indigo-600">
                                {{ $cita->codigo }}
                            </p>
                        </div>

                        @php
                            $colores = [
                                'Programada' => 'background-color: #dbeafe; color: #1d4ed8;',
                                'Pendiente de pago' => 'background-color: #fef3c7; color: #92400e;',
                                'Confirmada' => 'background-color: #dbeafe; color: #1d4ed8;',
                                'Atendida' => 'background-color: #f3f4f6; color: #374151;',
                                'Cancelada' => 'background-color: #fee2e2; color: #b91c1c;',
                                'Inasistencia' => 'background-color: #fef3c7; color: #92400e;',
                            ];

                            $color = $colores[$cita->estado] ?? 'background-color: #f3f4f6; color: #374151;';
                        @endphp

                        <span style="{{ $color }}"
                              class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold">
                            {{ $cita->estado }}
                        </span>

                    </div>

                    <div class="space-y-4 p-4">

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Paciente
                            </p>
                            <p class="mt-1 font-semibold text-gray-800">
                                {{ $cita->paciente->apellidos }}, {{ $cita->paciente->nombres }}
                            </p>
                            <p class="text-sm text-gray-500">
                                CI: {{ $cita->paciente->ci }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Médico
                            </p>
                            <p class="mt-1 font-semibold text-gray-800">
                                Dr. {{ $cita->medico->apellidos }}, {{ $cita->medico->nombres }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $cita->especialidad->nombre }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-3">

                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Fecha
                                </p>
                                <p class="mt-1 text-sm font-medium text-gray-800">
                                    {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Horario
                                </p>
                                <p class="mt-1 text-sm font-medium text-gray-800">
                                    {{ substr($cita->hora_inicio, 0, 5) }}
                                    - {{ substr($cita->hora_fin, 0, 5) }}
                                </p>
                            </div>

                        </div>

                        @php
                            $pagoPendiente = $cita->pagos
                                ->where('estado', 'Pendiente')
                                ->sortByDesc('id')
                                ->first();
                        @endphp

                        @if($cita->estado === 'Pendiente de pago' && $pagoPendiente && $pagoPendiente->metodo === 'qr')
                            <form action="{{ route($rutaCitas . '.aprobar-pago', $cita) }}"
                                  method="POST" class="space-y-2"
                                  onsubmit="return confirm('¿Verificaste que el dinero llegó a la cuenta y confirmas la aprobación?');">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="referencia_pago" maxlength="255"
                                       placeholder="N.º de referencia (opcional)"
                                       class="w-full rounded-md border-gray-300 text-sm">
                                <button type="submit"
                                    class="w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-green-700">
                                    Aprobar pago QR
                                </button>
                            </form>
                        @endif

                        @if(in_array($cita->estado, ['Pendiente de pago', 'Programada', 'Confirmada'], true))
                            <form action="{{ route($rutaCitas . '.cancelar', $cita) }}"
                                  method="POST"
                                  onsubmit="return confirm('¿Cancelar esta cita? El pago conservará su estado actual.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                                    Cancelar cita
                                </button>
                            </form>
                        @endif

                    </div>
                </div>

            @empty

                <div class="rounded-xl bg-white p-8 text-center">
                    <span class="text-4xl text-gray-300">📅</span>
                    <p class="mt-3 font-semibold text-gray-700">
                        No hay citas registradas
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Cuando programes una cita aparecerá en este listado.
                    </p>
                </div>

            @endforelse

        </div>

        {{-- PAGINACIÓN --}}
        @if($citas->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-4">
                {{ $citas->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
