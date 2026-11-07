
@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Médico *</label>
        <select name="medico_id" required
                class="w-full bg-white text-gray-900 border-gray-300 rounded-lg">
            <option value="">Seleccione un médico</option>
            @foreach($medicos as $medico)
                <option value="{{ $medico->id }}"
                    @selected(old('medico_id', $horario->medico_id ?? '') == $medico->id)>
                    {{ $medico->nombres }} {{ $medico->apellidos }}
                    — {{ $medico->registro_profesional }}
                </option>
            @endforeach
        </select>
        @error('medico_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Día de la semana *
    </label>

    <select name="dia_semana" required
            class="w-full bg-white text-gray-900 border-gray-300 rounded-lg">

        <option value="">Seleccione un día</option>

        @foreach([
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ] as $numero => $dia)

            <option value="{{ $numero }}"
                @selected(old('dia_semana', $horario->dia_semana ?? '') == $numero)>
                {{ $dia }}
            </option>

        @endforeach

    </select>

    @error('dia_semana')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>


    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Hora de inicio *</label>
        <input type="time" name="hora_inicio" required
               value="{{ old('hora_inicio', isset($horario) ? substr($horario->hora_inicio, 0, 5) : '') }}"
               class="w-full bg-white text-gray-900 border-gray-300 rounded-lg">
        @error('hora_inicio') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Hora de finalización *</label>
        <input type="time" name="hora_fin" required
               value="{{ old('hora_fin', isset($horario) ? substr($horario->hora_fin, 0, 5) : '') }}"
               class="w-full bg-white text-gray-900 border-gray-300 rounded-lg">
        @error('hora_fin') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

</div>

@if($errors->any())
    <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg">
        <p class="font-semibold">Revisa los siguientes errores:</p>
        <ul class="list-disc ml-5 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex gap-3 mt-6">
    <button type="submit"
            class="inline-flex w-auto px-5 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800">
        {{ isset($horario) ? 'Actualizar horario' : 'Guardar horario' }}
    </button>

    <a href="{{ route('admin.horarios.index') }}"
       class="inline-flex w-auto px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100">
        Cancelar
    </a>
</div>
