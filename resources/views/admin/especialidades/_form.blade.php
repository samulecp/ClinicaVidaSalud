
<div class="space-y-5">
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Nombre de la especialidad *
        </label>

        <input type="text" name="nombre"
            value="{{ old('nombre', $especialidad->nombre ?? '') }}"
            maxlength="100" required
            placeholder="Ej. Medicina General"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">

        @error('nombre')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-800">
            Descripción
        </label>

        <textarea name="descripcion" rows="4" maxlength="255"
            placeholder="Descripción de la especialidad..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900">{{ old('descripcion', $especialidad->descripcion ?? '') }}</textarea>

        @error('descripcion')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex flex-wrap gap-3 border-t border-gray-200 pt-6">
    <button type="submit"
        class="rounded-lg bg-blue-700 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-800">
        {{ $boton }}
    </button>

    <a href="{{ route('admin.especialidades.index') }}"
        class="rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">
        Cancelar
    </a>
</div>
