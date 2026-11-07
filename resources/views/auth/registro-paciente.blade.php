<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">
            Registro de pacientes
        </h1>
        <p class="mt-2 text-sm text-gray-600">
            Crea tu cuenta para consultar horarios y reservar citas médicas.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('registro.paciente.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div>
                <label for="ci" class="block text-sm font-medium text-gray-700">
                    Carnet de identidad
                </label>
                <input id="ci" name="ci" type="text"
                    value="{{ old('ci') }}" required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">
                    Fecha de nacimiento
                </label>
                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                    value="{{ old('fecha_nacimiento') }}"
                    max="{{ date('Y-m-d') }}" required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="nombres" class="block text-sm font-medium text-gray-700">
                    Nombres
                </label>
                <input id="nombres" name="nombres" type="text"
                    value="{{ old('nombres') }}" required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="apellidos" class="block text-sm font-medium text-gray-700">
                    Apellidos
                </label>
                <input id="apellidos" name="apellidos" type="text"
                    value="{{ old('apellidos') }}" required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="sexo" class="block text-sm font-medium text-gray-700">
                    Sexo
                </label>
                <select id="sexo" name="sexo" required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecciona una opción</option>
                    <option value="Masculino" @selected(old('sexo') == 'Masculino')>Masculino</option>
                    <option value="Femenino" @selected(old('sexo') == 'Femenino')>Femenino</option>
                </select>
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700">
                    Teléfono
                </label>
                <input id="telefono" name="telefono" type="text"
                    value="{{ old('telefono') }}" required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label for="correo" class="block text-sm font-medium text-gray-700">
                    Correo electrónico
                </label>
                <input id="correo" name="correo" type="email"
                    value="{{ old('correo') }}" required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label for="direccion" class="block text-sm font-medium text-gray-700">
                    Dirección
                </label>
                <input id="direccion" name="direccion" type="text"
                    value="{{ old('direccion') }}"
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Contraseña
                </label>
                <input id="password" name="password" type="password"
                    required autocomplete="new-password"
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirmar contraseña
                </label>
                <input id="password_confirmation" name="password_confirmation"
                    type="password" required autocomplete="new-password"
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

        </div>

        <div class="mt-6">
            <button type="submit"
                style="background-color: #4f46e5; color: #ffffff;"
                class="w-full rounded-lg px-4 py-3 font-bold shadow-md transition hover:opacity-90">
                Crear mi cuenta
            </button>
        </div>

        <p class="mt-5 text-center text-sm text-gray-600">
            ¿Ya tienes una cuenta?
            <a href="{{ route('login') }}"
                class="font-semibold text-indigo-600 hover:underline">
                Inicia sesión
            </a>
        </p>
    </form>
</x-guest-layout>