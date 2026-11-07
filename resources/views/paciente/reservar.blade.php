
@extends('layouts.paciente')

@section('title', 'Reservar cita')

@section('header')
    <div>
        <h2 class="text-xl font-bold text-slate-800">
            Reservar una cita médica
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            Selecciona una especialidad, un médico y un horario disponible.
        </p>
    </div>
@endsection

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Nueva reserva
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Puedes reservar citas para hoy o mañana.
        </p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="font-semibold text-red-800">
                No se pudo completar la reserva:
            </p>

            <ul class="mt-2 list-inside list-disc text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-800">
                Datos de la consulta
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Completa los siguientes campos para encontrar un turno.
            </p>
        </div>

        <form method="POST" action="{{ route('paciente.reservar.store') }}"
              id="formReserva" class="space-y-6 p-6">

            @csrf

            {{-- ESPECIALIDAD --}}
            <div>
                <label for="especialidad_id"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Especialidad médica
                </label>

                <select name="especialidad_id" id="especialidad_id"
                        required
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                    <option value="">Selecciona una especialidad</option>

                    @foreach($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}"
                            {{ old('especialidad_id') == $especialidad->id ? 'selected' : '' }}>
                            {{ $especialidad->nombre }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- MÉDICO --}}
            <div>
                <label for="medico_id"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Médico
                </label>

                <select name="medico_id" id="medico_id"
                        required disabled
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                    <option value="">Primero selecciona una especialidad</option>

                </select>
            </div>

            {{-- FECHA --}}
            <div>
                <label for="fecha"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Fecha de la cita
                </label>

                <input type="date"
                       name="fecha"
                       id="fecha"
                       min="{{ today()->format('Y-m-d') }}"
                       max="{{ today()->addDay()->format('Y-m-d') }}"
                       value="{{ old('fecha') }}"
                       required
                       class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                <p class="mt-2 text-xs text-slate-500">
                    Solo se permiten reservas para hoy o mañana.
                    Los horarios anteriores a la hora actual no estarán disponibles.
                </p>
            </div>

            {{-- HORARIOS --}}
            <div>
                <label for="hora_inicio"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Horarios disponibles
                </label>

                <select name="hora_inicio" id="hora_inicio"
                        required disabled
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                    <option value="">Selecciona primero un médico y una fecha</option>

                </select>

                <p id="mensajeTurnos" class="mt-2 text-sm text-slate-500"></p>
            </div>

            {{-- MOTIVO --}}
            <div>
                <label for="motivo"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Motivo de la consulta
                    <span class="font-normal text-slate-400">(opcional)</span>
                </label>

                <textarea name="motivo" id="motivo" rows="3"
                          maxlength="500"
                          placeholder="Describe brevemente el motivo de tu consulta..."
                          class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('motivo') }}</textarea>
            </div>

            
{{-- INFORMACIÓN DEL PAGO --}}
<div class="rounded-xl border border-blue-200 bg-blue-50 p-5">

    <div class="flex items-center gap-2">
        <span class="text-lg">💳</span>
        <h3 class="font-bold text-slate-800">
            Pago de la consulta
        </h3>
    </div>

    <div class="mt-3 flex items-center justify-between">
        <span class="text-sm text-slate-600">
            Precio de la consulta
        </span>

        <span class="text-xl font-bold text-slate-900">
            Bs 100.00
        </span>
    </div>

    <div class="mt-3 rounded-lg bg-white p-3">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                <span class="font-bold text-blue-700">P</span>
            </div>

            <div>
                <p class="text-sm font-bold text-slate-800">
                    Pago mediante PayPal
                </p>
                <p class="text-xs text-slate-500">
                    Serás redirigido a PayPal para completar el pago.
                </p>
            </div>
        </div>
    </div>

    <p class="mt-3 text-xs text-slate-600">
        El importe se convertirá de bolivianos a dólares estadounidenses
        utilizando el tipo de cambio registrado al momento de la reserva.
        Dispondrás de 15 minutos para completar el pago.
    </p>

</div>


            {{-- BOTONES --}}
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row">

                <a href="{{ route('paciente.dashboard') }}"
                   class="rounded-lg px-5 py-3 text-center text-sm font-semibold"
                   style="background-color:#e2e8f0;color:#334155;">
                    Cancelar
                </a>

                <button type="submit"
                        id="btnReservar"
                        class="rounded-lg px-5 py-3 text-sm font-semibold"
                        style="background-color:#1d4ed8;color:white;">
                    Continuar al pago con PayPal
                </button>

            </div>

        </form>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const especialidad = document.getElementById('especialidad_id');
    const medico = document.getElementById('medico_id');
    const fecha = document.getElementById('fecha');
    const hora = document.getElementById('hora_inicio');
    const mensaje = document.getElementById('mensajeTurnos');
    const formulario = document.getElementById('formReserva');

    const urlMedicos = @json(route('disponibilidad.medicos'));
    const urlTurnos = @json(route('disponibilidad.turnos'));

    function limpiarTurnos(texto) {
        hora.innerHTML = '';
        hora.add(new Option(texto, ''));
        hora.disabled = true;
        mensaje.textContent = '';
    }

    function limpiarMedicos(texto) {
        medico.innerHTML = '';
        medico.add(new Option(texto, ''));
        medico.disabled = true;
        limpiarTurnos('Selecciona primero un médico y una fecha');
    }

    async function cargarMedicos() {

        limpiarMedicos('Cargando médicos...');

        if (!especialidad.value) {
            limpiarMedicos('Primero selecciona una especialidad');
            return;
        }

        try {
            const parametros = new URLSearchParams({
                especialidad_id: especialidad.value
            });

            const respuesta = await fetch(`${urlMedicos}?${parametros}`);
            const datos = await respuesta.json();

            medico.innerHTML = '';
            medico.add(new Option('Selecciona un médico', ''));

            if (!respuesta.ok) {
                throw new Error(datos.message || 'No se pudieron cargar los médicos.');
            }

            if (datos.length === 0) {
                limpiarMedicos('No hay médicos disponibles para esta especialidad');
                return;
            }

            datos.forEach(function (item) {
                medico.add(new Option(
                    `Dr. ${item.nombres} ${item.apellidos}`,
                    item.id
                ));
            });

            medico.disabled = false;

        } catch (error) {
            limpiarMedicos('Error al cargar médicos');
            mensaje.textContent = error.message;
        }
    }

    async function cargarTurnos() {

        limpiarTurnos('Cargando horarios...');

        if (!medico.value || !fecha.value) {
            limpiarTurnos('Selecciona un médico y una fecha');
            return;
        }

        try {
            const parametros = new URLSearchParams({
                medico_id: medico.value,
                fecha: fecha.value
            });

            const respuesta = await fetch(`${urlTurnos}?${parametros}`);
            const datos = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(datos.message || 'No se pudieron consultar los horarios.');
            }

            hora.innerHTML = '';
            hora.add(new Option('Selecciona un horario', ''));

            if (!datos.turnos || datos.turnos.length === 0) {
                limpiarTurnos('No hay horarios disponibles');
                mensaje.textContent = 'No se encontraron turnos libres para esta fecha.';
                return;
            }

            datos.turnos.forEach(function (turno) {
                hora.add(new Option(
                    `${turno.hora_inicio} - ${turno.hora_fin}`,
                    turno.hora_inicio
                ));
            });

            hora.disabled = false;
            mensaje.textContent = `${datos.turnos.length} horario(s) disponible(s).`;

        } catch (error) {
            limpiarTurnos('No se pudieron cargar los horarios');
            mensaje.textContent = error.message;
        }
    }

    especialidad.addEventListener('change', cargarMedicos);
    medico.addEventListener('change', cargarTurnos);
    fecha.addEventListener('change', cargarTurnos);

    // Evitar enviar una fecha fuera del rango permitido.
    formulario.addEventListener('submit', function (event) {

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        const manana = new Date(hoy);
        manana.setDate(manana.getDate() + 1);

        const seleccionada = new Date(fecha.value + 'T00:00:00');

        if (
            !fecha.value ||
            seleccionada < hoy ||
            seleccionada > manana
        ) {
            event.preventDefault();
            alert('Solo puedes reservar citas para hoy o mañana.');
            return;
        }

        if (!medico.value || !hora.value) {
            event.preventDefault();
            alert('Selecciona un médico y un horario disponible.');
        }
    });

    // Si el formulario vuelve con errores, recuperar los médicos.
    @if(old('especialidad_id'))
        cargarMedicos().then(function () {
            const medicoAnterior = @json(old('medico_id'));

            if (medicoAnterior) {
                medico.value = medicoAnterior;
                cargarTurnos().then(function () {
                    hora.value = @json(old('hora_inicio'));
                });
            }
        });
    @endif

});
</script>

@endsection
