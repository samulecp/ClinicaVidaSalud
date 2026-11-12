
@extends('layouts.paciente')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">
        Mis citas médicas
    </h1>
    <p class="mt-1 text-sm text-slate-500">
        Consulta tus citas y verifica el estado de tus pagos.
    </p>
</div>

@if(session('success'))
    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

@if($errors->has('cancelacion'))
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first('cancelacion') }}
    </div>
@endif

<div class="space-y-4">

    @forelse($citas as $cita)

        @php
            $pago = $cita->pagos->sortByDesc('created_at')->first();

            $coloresEstado = [
                'Programada' => 'bg-blue-100 text-blue-700',
                'Confirmada' => 'bg-blue-100 text-blue-700',
                'Pendiente de pago' => 'bg-amber-100 text-amber-700',
                'Cancelada' => 'bg-red-100 text-red-700',
                'Atendida' => 'bg-green-100 text-green-700',
                'Inasistencia' => 'bg-slate-100 text-slate-700',
            ];

            $estadoPago = $pago?->estado ?? 'Sin registro';
        @endphp

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="p-5">

                {{-- Información principal --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">

                    <h2 class="text-lg font-bold text-slate-800">
                        {{ $cita->especialidad->nombre }}
                    </h2>

                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $coloresEstado[$cita->estado] ?? 'bg-slate-100 text-slate-700' }}">
                        {{ $cita->estado }}
                    </span>

                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                    <div>
                        <p class="text-xs font-medium text-slate-400">Médico</p>
                        <p class="text-sm font-semibold text-slate-700">
                            Dr. {{ $cita->medico->nombres }}
                            {{ $cita->medico->apellidos }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-400">Fecha</p>
                        <p class="text-sm font-semibold text-slate-700">
                            {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-400">Horario</p>
                        <p class="text-sm font-semibold text-slate-700">
                            {{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}
                            -
                            {{ \Carbon\Carbon::parse($cita->hora_fin)->format('H:i') }}
                        </p>
                    </div>

                </div>

                @if($cita->motivo)
                    <p class="mt-3 text-sm text-slate-500">
                        <span class="font-medium">Motivo:</span>
                        {{ $cita->motivo }}
                    </p>
                @endif

                {{-- Información del pago --}}
                <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">

                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-semibold text-slate-800">
                            Información del pago
                        </h3>

                        @if($estadoPago === 'Aprobado')
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                <icon name="check-circle" /> Pagado
                            </span>
                        @elseif($estadoPago === 'Pendiente')
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                Pendiente de pago
                            </span>
                        @elseif($estadoPago === 'Cancelado')
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                Pago cancelado
                            </span>
                        @else
                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                Sin registro de pago
                            </span>
                        @endif
                    </div>

                    @if($pago)

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">

                            <div>
                                <p class="text-xs text-slate-400">Importe</p>
                                <p class="text-sm font-bold text-slate-800">
                                    Bs {{ number_format((float) $pago->monto, 2) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">Método</p>
                                <p class="text-sm font-semibold text-slate-700">
                                    {{ ucfirst($pago->metodo) }}
                                </p>
                            </div>

                            @if($pago->tipo_cambio)
                                <div>
                                    <p class="text-xs text-slate-400">Tipo de cambio registrado</p>
                                    <p class="text-sm font-semibold text-slate-700">
                                        1 USD = Bs {{ number_format((float) $pago->tipo_cambio, 4) }}
                                    </p>
                                </div>
                            @endif

                            @if($pago->pagado_en)
                                <div>
                                    <p class="text-xs text-slate-400">Fecha de pago</p>
                                    <p class="text-sm font-semibold text-slate-700">
                                        {{ $pago->pagado_en->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            @endif

                            @if($pago->referencia)
                                <div>
                                    <p class="text-xs text-slate-400">Referencia</p>
                                    <p class="break-all text-sm font-semibold text-slate-700">
                                        {{ $pago->referencia }}
                                    </p>
                                </div>
                            @endif

                        </div>

                    @else

                        <p class="text-sm text-slate-500">
                            Esta cita todavía no tiene un registro de pago.
                        </p>

                    @endif

                </div>

                {{-- Código y acciones --}}
                <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <p class="text-xs font-medium text-slate-400">
                            Código de cita
                        </p>
                        <p class="font-bold text-slate-800">
                            {{ $cita->codigo }}
                        </p>
                    </div>

                    @if(in_array($cita->estado, ['Pendiente de pago', 'Programada', 'Confirmada'], true))
                        <form
                            action="{{ route('paciente.cancelar', $cita->id) }}"
                            method="POST"
                            onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta cita? El estado del pago no cambiará.');"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="rounded-lg bg-red-100 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-200"
                            >
                                Cancelar cita
                            </button>
                        </form>
                    @endif

                </div>

            </div>
        </div>

    @empty

        <div class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
            <h2 class="text-lg font-bold text-slate-700">
                No tienes citas registradas
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Cuando reserves una cita, aparecerá en esta sección.
            </p>

            <a
                href="{{ route('paciente.reservar') }}"
                class="mt-5 inline-block rounded-lg px-5 py-2.5 text-sm font-semibold text-white"
                style="background-color: #2563eb;"
            >
                Reservar una cita
            </a>
        </div>

    @endforelse

</div>

@if($citas->hasPages())
    <div class="mt-6">
        {{ $citas->links() }}
    </div>
@endif

@endsection
