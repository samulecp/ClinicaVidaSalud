
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

{{-- INFORMACIÓN DEL PAGO QR --}}
<div class="rounded-xl border border-green-200 bg-green-50 p-5">

    <div class="flex items-center gap-2">
        <span class="text-xl">💳</span>
        <h3 class="font-bold text-slate-800">
            Pago de la consulta mediante QR
        </h3>
    </div>

    <div class="mt-4 flex items-center justify-between rounded-lg bg-white p-4">
        <span class="text-sm font-medium text-slate-600">
            Precio de la consulta
        </span>

        <span class="text-2xl font-bold text-green-700">
            Bs {{ number_format((float) $precioConsulta, 2) }}
        </span>
    </div>

    <div class="mt-4 rounded-xl bg-white p-4 text-center">

        <p class="mb-3 font-semibold text-slate-800">
            Escanea el siguiente código QR
        </p>

        <img
            src="{{ asset('images/pago-qr.png') }}"
            alt="Código QR de Banco Ganadero"
            class="mx-auto w-full max-w-xs rounded-lg"
        >

        <p class="mt-3 text-sm font-bold text-slate-800">
            Banco Ganadero
        </p>

        <p class="text-sm text-slate-600">
            Cuellar Panoso Wilmar Samuel
        </p>

        <p class="text-sm text-slate-600">
            Cuenta: 1310526789
        </p>
    </div>

    <div class="mt-4 rounded-lg border border-green-200 bg-white p-4">
        <p class="text-sm font-semibold text-slate-800">
            Instrucciones de pago
        </p>

        <ol class="mt-2 list-inside list-decimal space-y-2 text-sm text-slate-600">
            <li>Primero registra tu reserva.</li>
            <li>Escanea el código QR desde tu aplicación bancaria.</li>
            <li>Introduce manualmente el importe de la consulta: Bs {{ number_format((float) $precioConsulta, 2) }}.</li>
            <li>Realiza la transferencia y guarda el comprobante.</li>
            <li>Envía el comprobante por WhatsApp a la clínica.</li>
        </ol>

        <a
            href="https://wa.me/59176316165?text={{ urlencode('Hola, acabo de realizar el pago de mi cita médica. Adjunto mi comprobante de transferencia.') }}"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-4 flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-3 text-sm font-bold text-white hover:bg-green-700"
        >
            Enviar comprobante por WhatsApp
        </a>
    </div>

    <p class="mt-3 text-xs text-slate-500">
        Tu cita permanecerá pendiente de pago hasta que recepción
        verifique la transferencia y apruebe el pago.
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
                    Continuar
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
