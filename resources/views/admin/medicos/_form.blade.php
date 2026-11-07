
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">

    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Registro profesional *
        </label>
        <input type="text" name="registro_profesional"
            value="{{ old('registro_profesional', $medico->registro_profesional ?? '') }}"
            maxlength="50" required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
        @error('registro_profesional')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Nombres *
        </label>
        <input type="text" name="nombres"
            value="{{ old('nombres', $medico->nombres ?? '') }}"
            maxlength="100" required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
        @error('nombres')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Apellidos *
        </label>
        <input type="text" name="apellidos"
            value="{{ old('apellidos', $medico->apellidos ?? '') }}"
            maxlength="100" required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
        @error('apellidos')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Teléfono
        </label>
        <input type="text" name="telefono"
            value="{{ old('telefono', $medico->telefono ?? '') }}"
            maxlength="20"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
        @error('telefono')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Correo electrónico *
        </label>
        <input type="email" name="correo"
    value="{{ old('correo', $medico->correo ?? '') }}"
    maxlength="150"
    {{ !isset($medico) ? 'required' : '' }}
    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
        @error('correo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Dirección
        </label>
        <input type="text" name="direccion"
            value="{{ old('direccion', $medico->direccion ?? '') }}"
            maxlength="255"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
        @error('direccion')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>
@if(!isset($medico))
    <div class="mt-7 rounded-xl border border-blue-200 bg-blue-50 p-5">
        <h3 class="text-base font-bold text-gray-900">
            Datos de acceso al sistema
        </h3>

        <p class="mt-1 text-sm text-gray-600">
            Estas credenciales permitirán al médico ingresar a su portal.
        </p>

        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-800">
                    Contraseña inicial *
                </label>
                <input type="password" name="password"
                    minlength="8" required
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-800">
                    Confirmar contraseña *
                </label>
                <input type="password" name="password_confirmation"
                    minlength="8" required
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">
            </div>
        </div>
    </div>
@endif

<div class="mt-7">
    <label class="mb-3 block text-sm font-semibold text-gray-800">
        Especialidades <span class="text-red-600">*</span>
    </label>

    <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-3">
        @php
            $seleccionadas = old(
                'especialidades',
                isset($medico)
                    ? $medico->especialidades->pluck('id')->map(fn($id) => (string) $id)->toArray()
                    : []
            );
        @endphp

        @forelse($especialidades as $especialidad)
            <label class="flex cursor-pointer items-center gap-2 rounded-md bg-white p-3 text-sm text-gray-800">
                <input
                    type="checkbox"
                    name="especialidades[]"
                    value="{{ $especialidad->id }}"
                    @checked(in_array((string) $especialidad->id, $seleccionadas))
                    class="rounded border-gray-300 text-blue-700 focus:ring-blue-500"
                >
                {{ $especialidad->nombre }}
            </label>
        @empty
            <p class="col-span-full text-sm text-gray-500">
                No hay especialidades activas. Registra una especialidad primero.
            </p>
        @endforelse
    </div>

    @error('especialidades')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    @error('especialidades.*')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="mt-8 flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6">
    <button type="submit"
        class="inline-flex w-auto items-center justify-center rounded-lg bg-blue-700 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-800">
        {{ $boton }}
    </button>

    <a href="{{ route('admin.medicos.index') }}"
        class="inline-flex w-auto items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">
        Cancelar
    </a>
</div>
