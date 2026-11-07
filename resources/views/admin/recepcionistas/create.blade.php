
@extends('layouts.admin')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.recepcionistas.index') }}"
       class="text-sm font-semibold text-blue-600 hover:underline">
        ← Volver a recepcionistas
    </a>

    <h1 class="mt-3 text-2xl font-bold text-slate-800">
        Registrar recepcionista
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        Crea una cuenta para que el personal pueda acceder al sistema.
    </p>
</div>

<div class="max-w-3xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="text-lg font-bold text-slate-800">
            Datos de acceso
        </h2>
    </div>

    <form action="{{ route('admin.recepcionistas.store') }}"
          method="POST"
          class="space-y-5 p-6">

        @csrf

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">
                Nombre completo
            </label>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                maxlength="255"
                placeholder="Nombre del recepcionista"
                class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
            >

            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">
                Correo electrónico
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                maxlength="255"
                placeholder="recepcion@clinica.com"
                class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
            >

            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Contraseña
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >

                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Confirmar contraseña
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
            <p class="text-sm font-semibold text-blue-800">
                Rol asignado: Recepcionista
            </p>
            <p class="mt-1 text-xs text-blue-700">
                La cuenta tendrá acceso al panel de recepción y sus funciones autorizadas.
            </p>
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">

            <a href="{{ route('admin.recepcionistas.index') }}"
               class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Cancelar
            </a>

            <button type="submit"
                    class="rounded-lg px-5 py-2.5 text-sm font-semibold"
                    style="background-color: #2563eb; color: white;">
                Crear cuenta
            </button>

        </div>

    </form>

</div>

@endsection
