
@extends('layouts.medico')

@section('title', 'Consulta médica')

@section('content')
<div class="mb-6">
    <a href="{{ route('medico.agenda') }}"
       class="text-sm font-semibold text-blue-600 hover:text-blue-800">
        ← Volver a mi agenda
    </a>

    <h1 class="mt-3 text-2xl font-bold text-slate-800">
        Consulta médica
    </h1>
    <p class="mt-1 text-sm text-slate-500">
        Revisa la información del paciente y registra la atención médica.
    </p>
</div>

{{-- Datos de la cita --}}
<div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-slate-800">Información de la cita</h2>

        <span class="rounded-full px-3 py-1 text-xs font-semibold
            {{ $cita->estado === 'Programada' ? 'bg-blue-100 text-blue-700' : '' }}
            {{ $cita->estado === 'Atendida' ? 'bg-green-100 text-green-700' : '' }}
            {{ $cita->estado === 'Inasistencia' ? 'bg-red-100 text-red-700' : '' }}
            {{ $cita->estado === 'Cancelada' ? 'bg-slate-100 text-slate-600' : '' }}">
            {{ $cita->estado }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Código</p>
            <p class="mt-1 font-semibold text-slate-800">{{ $cita->codigo }}</p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Fecha</p>
            <p class="mt-1 font-semibold text-slate-800">
                {{ $cita->fecha->format('d/m/Y') }}
            </p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Horario</p>
            <p class="mt-1 font-semibold text-slate-800">
                {{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($cita->hora_fin)->format('H:i') }}
            </p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Especialidad</p>
            <p class="mt-1 font-semibold text-slate-800">
                {{ $cita->especialidad->nombre ?? '—' }}
            </p>
        </div>

        <div class="sm:col-span-2">
            <p class="text-xs font-semibold uppercase text-slate-400">Motivo de consulta</p>
            <p class="mt-1 text-slate-700">{{ $cita->motivo ?? 'No especificado' }}</p>
        </div>
    </div>
</div>

{{-- Datos del paciente --}}
<div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="mb-5 text-lg font-bold text-slate-800">Datos del paciente</h2>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Nombre completo</p>
            <p class="mt-1 font-semibold text-slate-800">
                {{ $cita->paciente->nombres ?? '' }}
                {{ $cita->paciente->apellidos ?? '' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Carnet de identidad</p>
            <p class="mt-1 text-slate-700">{{ $cita->paciente->ci ?? '—' }}</p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Fecha de nacimiento</p>
            <p class="mt-1 text-slate-700">
                {{ $cita->paciente->fecha_nacimiento
                    ? \Carbon\Carbon::parse($cita->paciente->fecha_nacimiento)->format('d/m/Y')
                    : '—' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Sexo</p>
            <p class="mt-1 text-slate-700">{{ $cita->paciente->sexo ?? '—' }}</p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Teléfono</p>
            <p class="mt-1 text-slate-700">{{ $cita->paciente->telefono ?? '—' }}</p>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase text-slate-400">Correo</p>
            <p class="mt-1 text-slate-700">{{ $cita->paciente->correo ?? '—' }}</p>
        </div>
    </div>
</div>

{{-- Historial de consultas anteriores --}}
<div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="mb-4 text-lg font-bold text-slate-800">Historial de consultas anteriores</h2>

    @forelse($historial as $consulta)
        <div class="border-b border-slate-100 py-4 last:border-0">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <p class="font-semibold text-slate-800">
                    {{ $consulta->fecha->format('d/m/Y') }}
                </p>
                <span class="text-xs font-semibold text-green-700">Atendida</span>
            </div>

            <p class="text-sm text-slate-600">
                <strong>Especialidad:</strong>
                {{ $consulta->especialidad->nombre ?? '—' }}
            </p>

            <p class="mt-2 text-sm text-slate-600">
                <strong>Diagnóstico:</strong>
                {{ $consulta->diagnostico ?? 'No registrado' }}
            </p>

            <p class="mt-1 text-sm text-slate-600">
                <strong>Tratamiento:</strong>
                {{ $consulta->tratamiento ?? 'No registrado' }}
            </p>

            @if($consulta->observaciones_medicas)
                <p class="mt-1 text-sm text-slate-600">
                    <strong>Observaciones:</strong>
                    {{ $consulta->observaciones_medicas }}
                </p>
            @endif
        </div>
    @empty
        <p class="py-4 text-sm text-slate-500">
            Este paciente todavía no tiene consultas médicas anteriores registradas.
        </p>
    @endforelse
</div>

{{-- Registrar atención --}}
@if($cita->estado === 'Programada')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-2 text-lg font-bold text-slate-800">Registrar atención médica</h2>
        <p class="mb-6 text-sm text-slate-500">
            Completa los datos de la consulta para registrar la atención del paciente.
        </p>

        <form method="POST" action="{{ route('medico.citas.atender', $cita) }}">
            @csrf
            @method('PATCH')

            <div class="mb-5">
                <label for="diagnostico" class="mb-2 block text-sm font-semibold text-slate-700">
                    Diagnóstico *
                </label>
                <textarea id="diagnostico" name="diagnostico" rows="3" required
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Describe el diagnóstico del paciente...">{{ old('diagnostico') }}</textarea>
                @error('diagnostico')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="tratamiento" class="mb-2 block text-sm font-semibold text-slate-700">
                    Tratamiento *
                </label>
                <textarea id="tratamiento" name="tratamiento" rows="3" required
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Describe el tratamiento indicado...">{{ old('tratamiento') }}</textarea>
                @error('tratamiento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="observaciones_medicas" class="mb-2 block text-sm font-semibold text-slate-700">
                    Observaciones médicas
                </label>
                <textarea id="observaciones_medicas" name="observaciones_medicas" rows="3"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Observaciones adicionales (opcional)...">{{ old('observaciones_medicas') }}</textarea>
                @error('observaciones_medicas')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="submit"
                    class="rounded-lg bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700">
                    Guardar atención médica
                </button>
            </div>
        </form>

        <div class="mt-5 border-t border-slate-100 pt-5">
            <form method="POST"
                  action="{{ route('medico.citas.inasistencia', $cita) }}"
                  onsubmit="return confirm('¿Confirmas que el paciente no asistió a la cita?')">
                @csrf
                @method('PATCH')

                <button type="submit"
                    class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                    Registrar inasistencia
                </button>
            </form>
        </div>
    </div>

@elseif($cita->estado === 'Atendida')
    <div class="rounded-xl border border-green-200 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-lg font-bold text-slate-800">Resultado de la consulta</h2>

        <div class="mb-5">
            <p class="text-sm font-semibold text-slate-500">Diagnóstico</p>
            <p class="mt-1 whitespace-pre-line text-slate-800">
                {{ $cita->diagnostico ?? 'No registrado' }}
            </p>
        </div>

        <div class="mb-5">
            <p class="text-sm font-semibold text-slate-500">Tratamiento</p>
            <p class="mt-1 whitespace-pre-line text-slate-800">
                {{ $cita->tratamiento ?? 'No registrado' }}
            </p>
        </div>

        <div>
            <p class="text-sm font-semibold text-slate-500">Observaciones médicas</p>
            <p class="mt-1 whitespace-pre-line text-slate-800">
                {{ $cita->observaciones_medicas ?? 'Sin observaciones' }}
            </p>
        </div>
    </div>

@elseif($cita->estado === 'Inasistencia')
    <div class="rounded-xl border border-red-200 bg-red-50 p-6">
        <p class="font-semibold text-red-700">
            Esta cita fue registrada como inasistencia.
        </p>
    </div>

@elseif($cita->estado === 'Cancelada')
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6">
        <p class="font-semibold text-slate-700">
            Esta cita fue cancelada y no puede recibir atención médica.
        </p>
    </div>
@endif

@endsection
