
@extends('layouts.admin')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Configuración del sistema
        </h1>
        <p class="mt-1 text-gray-600">
            Administra los parámetros generales de las citas médicas.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-100 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow-sm">
        <form action="{{ route('admin.configuracion.update') }}"
              method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="duracion_cita_minutos"
                       class="mb-2 block font-medium text-gray-700">
                    Duración global de cada cita (minutos)
                </label>

                <input
                    type="number"
                    id="duracion_cita_minutos"
                    name="duracion_cita_minutos"
                    min="5"
                    max="180"
                    step="1"
                    value="{{ old('duracion_cita_minutos', $configuracion->duracion_cita_minutos) }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                >

                @error('duracion_cita_minutos')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <p class="mt-2 text-sm text-gray-500">
                    Se permiten valores enteros entre 5 y 180 minutos.
                    Esta duración determinará los turnos disponibles
                    dentro de los horarios de atención de los médicos.
                </p>
            </div>

            <div class="mb-6 rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
                <strong>Ejemplo:</strong>
                con una duración de 30 minutos, un horario de 08:00 a 10:00
                genera cuatro turnos:
                08:00, 08:30, 09:00 y 09:30.
            </div>

            <button type="submit"
                    class="inline-flex w-auto items-center rounded-lg bg-blue-700 px-5 py-2.5 font-medium text-white hover:bg-blue-800">
                Guardar configuración
            </button>
        </form>
    </div>
</div>
@endsection
