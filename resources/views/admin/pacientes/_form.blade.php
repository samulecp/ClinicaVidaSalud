
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">

    {{-- Carnet de identidad --}}
    <div>
        <label for="ci" class="mb-2 block text-sm font-semibold text-gray-800">
            Carnet de identidad <span class="text-red-600">*</span>
        </label>

        <input
            type="text"
            id="ci"
            name="ci"
            value="{{ old('ci', $paciente->ci ?? '') }}"
            maxlength="20"
            required
            placeholder="Ej. 1234567"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        @error('ci')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Nombres --}}
    <div>
        <label for="nombres" class="mb-2 block text-sm font-semibold text-gray-800">
            Nombres <span class="text-red-600">*</span>
        </label>

        <input
            type="text"
            id="nombres"
            name="nombres"
            value="{{ old('nombres', $paciente->nombres ?? '') }}"
            maxlength="100"
            required
            placeholder="Ingrese los nombres"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        @error('nombres')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Apellidos --}}
    <div>
        <label for="apellidos" class="mb-2 block text-sm font-semibold text-gray-800">
            Apellidos <span class="text-red-600">*</span>
        </label>

        <input
            type="text"
            id="apellidos"
            name="apellidos"
            value="{{ old('apellidos', $paciente->apellidos ?? '') }}"
            maxlength="100"
            required
            placeholder="Ingrese los apellidos"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        @error('apellidos')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Fecha de nacimiento --}}
    <div>
        <label for="fecha_nacimiento" class="mb-2 block text-sm font-semibold text-gray-800">
            Fecha de nacimiento
        </label>

        <input
            type="date"
            id="fecha_nacimiento"
            name="fecha_nacimiento"
            value="{{ old('fecha_nacimiento', isset($paciente) && $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('Y-m-d') : '') }}"
            max="{{ date('Y-m-d') }}"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        @error('fecha_nacimiento')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Sexo --}}
    <div>
        <label for="sexo" class="mb-2 block text-sm font-semibold text-gray-800">
            Sexo
        </label>

        <select
            id="sexo"
            name="sexo"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >
            <option value="">Seleccione una opción</option>

            <option value="Masculino"
                @selected(old('sexo', $paciente->sexo ?? '') == 'Masculino')>
                Masculino
            </option>

            <option value="Femenino"
                @selected(old('sexo', $paciente->sexo ?? '') == 'Femenino')>
                Femenino
            </option>

            <option value="Otro"
                @selected(old('sexo', $paciente->sexo ?? '') == 'Otro')>
                Otro
            </option>
        </select>

        @error('sexo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Teléfono --}}
    <div>
        <label for="telefono" class="mb-2 block text-sm font-semibold text-gray-800">
            Teléfono
        </label>

        <input
            type="text"
            id="telefono"
            name="telefono"
            value="{{ old('telefono', $paciente->telefono ?? '') }}"
            maxlength="20"
            placeholder="Ej. 70000000"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        @error('telefono')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Correo --}}
    <div>
        <label for="correo" class="mb-2 block text-sm font-semibold text-gray-800">
            Correo electrónico
        </label>

        <input
            type="email"
            id="correo"
            name="correo"
            value="{{ old('correo', $paciente->correo ?? '') }}"
            maxlength="150"
            placeholder="ejemplo@correo.com"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        @error('correo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Dirección --}}
    <div>
        <label for="direccion" class="mb-2 block text-sm font-semibold text-gray-800">
            Dirección
        </label>

        <input
            type="text"
            id="direccion"
            name="direccion"
            value="{{ old('direccion', $paciente->direccion ?? '') }}"
            maxlength="255"
            placeholder="Barrio, calle y número"
            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        @error('direccion')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>

{{-- Botones --}}
<div class="mt-8 flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6">

    <button
        type="submit"
        class="inline-flex w-auto items-center justify-center rounded-lg bg-blue-700 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
    >
        {{ $boton }}
    </button>

    <a
        href="{{ route($rutaPacientes . '.index') }}"
        class="inline-flex w-auto items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100"
    >
        Cancelar
    </a>

</div>
