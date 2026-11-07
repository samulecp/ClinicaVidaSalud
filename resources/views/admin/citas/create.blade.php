@extends(auth()->user()->rol === 'recepcionista' ? 'layouts.recepcion' : 'layouts.admin')
@php
    $rutaCitas = auth()->user()->rol === 'recepcionista'
        ? 'recepcion.citas'
        : 'admin.citas';

    $precioConsulta = \App\Models\ConfiguracionSistema::actual()->precio_consulta;
@endphp
@section('content')

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Programar cita médica
        </h1>

        <p class="text-gray-500 mt-1">
            Selecciona al paciente, especialidad, médico y horario.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4">
            <p class="font-semibold text-red-700 mb-2">
                Revisa los siguientes errores:
            </p>

            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route(
    auth()->user()->rol === 'recepcionista'
        ? 'recepcion.citas.store'
        : 'admin.citas.store'
) }}"
          method="POST"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">

        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Paciente
            </label>

            <select name="paciente_id"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                <option value="">Seleccionar paciente</option>

                @foreach ($pacientes as $paciente)
                    <option value="{{ $paciente->id }}"
                        {{ old('paciente_id') == $paciente->id ? 'selected' : '' }}>
                        {{ $paciente->apellidos }}, {{ $paciente->nombres }}
                        — CI: {{ $paciente->ci }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Especialidad
                </label>

                <select id="especialidad_id"
                        name="especialidad_id"
                        required
                        class="w-full rounded-lg border-gray-300">

                    <option value="">Seleccionar especialidad</option>

                    @foreach ($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}"
                            {{ old('especialidad_id') == $especialidad->id ? 'selected' : '' }}>
                            {{ $especialidad->nombre }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Médico
                </label>

                <select id="medico_id"
                        name="medico_id"
                        required
                        disabled
                        class="w-full rounded-lg border-gray-300">

                    <option value="">Primero selecciona una especialidad</option>

                </select>
            </div>

        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Fecha de la cita
            </label>

            <input type="date"
                   id="fecha"
                   name="fecha"
                   min="{{ now()->format('Y-m-d') }}"
                   value="{{ old('fecha', now()->format('Y-m-d')) }}"
                   required
                   class="w-full rounded-lg border-gray-300">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Horario disponible
            </label>

            <select id="hora_inicio"
                    name="hora_inicio"
                    required
                    disabled
                    class="w-full rounded-lg border-gray-300">

                <option value="">Selecciona médico y fecha primero</option>

            </select>

            <p class="text-xs text-gray-500 mt-2">
                La duración de cada cita se calcula automáticamente
                según la configuración general del sistema.
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Motivo de la consulta
            </label>

            <textarea name="motivo"
                      rows="3"
                      maxlength="500"
                      class="w-full rounded-lg border-gray-300">{{ old('motivo') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Observaciones
            </label>

            <textarea name="observaciones"
                      rows="3"
                      maxlength="1000"
                      class="w-full rounded-lg border-gray-300">{{ old('observaciones') }}</textarea>
        </div>

        {{-- PAGO DE LA CONSULTA --}}
<div class="border border-indigo-200 bg-indigo-50 rounded-xl p-5 space-y-4">

    <div>
        <h3 class="text-lg font-bold text-gray-800">
            Pago de la consulta
        </h3>

        <p class="text-sm text-gray-600 mt-1">
            El personal debe registrar el pago recibido antes de programar la cita.
        </p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
            Importe
        </label>

        <input type="text"
               value="Bs {{ number_format((float) $precioConsulta, 2, ',', '.') }}"
               readonly
               class="w-full rounded-lg border-gray-300 bg-gray-100 font-bold text-gray-800">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
            Método de pago
        </label>

        <select name="metodo_pago"
                id="metodo_pago"
                required
                class="w-full rounded-lg border-gray-300">

            <option value="">Seleccionar método</option>

            <option value="efectivo"
                {{ old('metodo_pago') === 'efectivo' ? 'selected' : '' }}>
                Efectivo
            </option>

            <option value="qr"
                {{ old('metodo_pago') === 'qr' ? 'selected' : '' }}>
                QR
            </option>

        </select>
    </div>

    <div id="referencia_pago_contenedor" class="hidden">
        <label class="block text-sm font-semibold text-gray-700 mb-1">
            Referencia de operación QR
        </label>

        <input type="text"
               name="referencia_pago"
               value="{{ old('referencia_pago') }}"
               maxlength="255"
               placeholder="Número de operación (opcional)"
               class="w-full rounded-lg border-gray-300">
    </div>

    <p class="text-xs text-gray-600">
        Al guardar, el pago se registrará como aprobado.
    </p>

</div>

        <div class="flex justify-end items-center gap-3 pt-6 border-t border-gray-200">

    <a href="{{ route($rutaCitas . '.index') }}"
       class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
        Cancelar
    </a>

    <button type="submit"
        style="background-color: #4f46e5; color: #ffffff;"
        class="px-6 py-3 rounded-lg font-bold shadow-md hover:opacity-90 transition">
    Guardar
</button>

</div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const especialidadSelect = document.getElementById('especialidad_id');
    const medicoSelect = document.getElementById('medico_id');
    const fechaInput = document.getElementById('fecha');
    const horaSelect = document.getElementById('hora_inicio');

    const urlMedicos = @json(route('disponibilidad.medicos'));
    const urlTurnos = @json(route('disponibilidad.turnos'));

    const metodoPago = document.getElementById('metodo_pago');
const referenciaContenedor = document.getElementById('referencia_pago_contenedor');

function actualizarReferenciaPago() {
    referenciaContenedor.classList.toggle(
        'hidden',
        metodoPago.value !== 'qr'
    );
}

metodoPago.addEventListener('change', actualizarReferenciaPago);
actualizarReferenciaPago();

    let medicoAnterior = @json(old('medico_id'));
    const horaAnterior = @json(old('hora_inicio'));

    function limpiarMedicos(mensaje) {
        medicoSelect.innerHTML = '';
        medicoSelect.add(new Option(mensaje, ''));
        medicoSelect.disabled = true;
    }

    function limpiarTurnos(mensaje) {
        horaSelect.innerHTML = '';
        horaSelect.add(new Option(mensaje, ''));
        horaSelect.disabled = true;
    }

    async function cargarMedicos() {

        const especialidadId = especialidadSelect.value;

        limpiarMedicos('Cargando médicos...');
        limpiarTurnos('Selecciona un médico primero');

        if (!especialidadId) {
            limpiarMedicos('Primero selecciona una especialidad');
            return;
        }

        try {
            const respuesta = await fetch(
                `${urlMedicos}?especialidad_id=${encodeURIComponent(especialidadId)}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const medicos = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(medicos.message || 'No se pudieron cargar los médicos.');
            }

            limpiarMedicos('Seleccionar médico');

            medicos.forEach(medico => {
                const nombre = `${medico.apellidos}, ${medico.nombres}`;
                const opcion = new Option(nombre, medico.id);

                medicoSelect.add(opcion);
            });

            medicoSelect.disabled = false;

            if (medicoAnterior) {
                medicoSelect.value = medicoAnterior;
                medicoAnterior = null;
                await cargarTurnos();
            }

            if (medicos.length === 0) {
                limpiarMedicos('No hay médicos activos para esta especialidad');
            }

        } catch (error) {
            limpiarMedicos('Error al cargar médicos');
            alert(error.message);
        }
    }

    async function cargarTurnos() {

        const medicoId = medicoSelect.value;
        const fecha = fechaInput.value;

        limpiarTurnos('Cargando horarios...');

        if (!medicoId || !fecha) {
            limpiarTurnos('Selecciona médico y fecha');
            return;
        }

        try {
            const parametros = new URLSearchParams({
                medico_id: medicoId,
                fecha: fecha
            });

            const respuesta = await fetch(
                `${urlTurnos}?${parametros.toString()}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const datos = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(datos.message || 'No se pudieron cargar los horarios.');
            }

            limpiarTurnos('Seleccionar horario');

            datos.turnos.forEach(turno => {

                const texto = `${turno.hora_inicio} - ${turno.hora_fin}`;

                const opcion = new Option(
                    texto,
                    turno.hora_inicio
                );

                if (horaAnterior && horaAnterior === turno.hora_inicio) {
                    opcion.selected = true;
                }

                horaSelect.add(opcion);
            });

            horaSelect.disabled = false;

            if (datos.turnos.length === 0) {
                limpiarTurnos('No hay horarios disponibles para esta fecha');
            }

        } catch (error) {
            limpiarTurnos('Error al cargar horarios');
            alert(error.message);
        }
    }

    especialidadSelect.addEventListener('change', cargarMedicos);
    medicoSelect.addEventListener('change', cargarTurnos);
    fechaInput.addEventListener('change', cargarTurnos);

    if (especialidadSelect.value) {
        cargarMedicos();
    }

});
</script>

@endsection
