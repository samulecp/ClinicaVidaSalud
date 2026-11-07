
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Administración') - Clínica Vida Salud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100">

@php
    $seguridadActivo = request()->routeIs(
        'admin.medicos.*',
        'admin.pacientes.*',
        'admin.recepcionistas.*',
        'admin.bitacora.*'
    );

    $horariosActivo = request()->routeIs(
        'admin.horarios.*',
        'admin.especialidades.*',
        'admin.configuracion.*'
    );

    $citasActivo = request()->routeIs('admin.citas.*');
@endphp

<div x-data="{ menu: null }" class="min-h-screen">

    {{-- BARRA SUPERIOR --}}
    <header class="sticky top-0 z-50 border-b border-blue-800 bg-blue-950 text-white shadow-lg">

        {{-- MARCA Y USUARIO --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 md:px-8">

            <div>
                <h1 class="text-lg font-bold tracking-wide md:text-xl">
                    Clínica Vida Salud
                </h1>
                <p class="text-xs text-blue-200">
                    Panel administrativo
                </p>
            </div>

            <div class="flex items-center gap-3">

                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="text-xs text-blue-200">
                        Administrador
                    </p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700 md:px-4 md:text-sm">
                        Cerrar sesión
                    </button>
                </form>

            </div>
        </div>

        {{-- NAVEGACIÓN POR GRUPOS --}}
        <nav class="border-t border-blue-800 px-3 py-2 md:px-8">

            <div class="flex flex-wrap items-center gap-2">

                {{-- INICIO --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-lg px-4 py-2 text-sm font-semibold transition
                   {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                    Inicio
                </a>

                {{-- SEGURIDAD --}}
                <div class="relative">

                    <button type="button"
                        @click="menu = menu === 'seguridad' ? null : 'seguridad'"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                        {{ $seguridadActivo ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                        Seguridad
                        <span :class="menu === 'seguridad' ? 'rotate-180' : ''"
                              class="transition-transform">⌄</span>
                    </button>

                    <div x-cloak x-show="menu === 'seguridad'"
                         @click.outside="menu = null"
                         class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-2 shadow-xl">

                        <a href="{{ route('admin.medicos.index') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.medicos.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Médicos
                        </a>

                        <a href="{{ route('admin.pacientes.index') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.pacientes.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Pacientes
                        </a>

                        <a href="{{ route('admin.recepcionistas.index') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.recepcionistas.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Recepcionistas
                        </a>

                        <a href="{{ route('admin.bitacora.index') }}"
   class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
   {{ request()->routeIs('admin.bitacora.*') ? 'bg-blue-50 text-blue-700' : '' }}">
    Bitácora
</a>

                    </div>
                </div>

                {{-- HORARIOS --}}
                <div class="relative">

                    <button type="button"
                        @click="menu = menu === 'horarios' ? null : 'horarios'"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                        {{ $horariosActivo ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                        Horarios
                        <span :class="menu === 'horarios' ? 'rotate-180' : ''"
                              class="transition-transform">⌄</span>
                    </button>

                    <div x-cloak x-show="menu === 'horarios'"
                         @click.outside="menu = null"
                         class="absolute left-0 top-full z-50 mt-2 w-60 rounded-lg border border-gray-200 bg-white p-2 shadow-xl">

                        <a href="{{ route('admin.horarios.index') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.horarios.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Horarios médicos
                        </a>

                        <a href="{{ route('admin.especialidades.index') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.especialidades.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Especialidades
                        </a>

                        <a href="{{ route('admin.configuracion.edit') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.configuracion.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Configuración del sistema
                        </a>

                    </div>
                </div>

                {{-- CITAS --}}
                <div class="relative">

                    <button type="button"
                        @click="menu = menu === 'citas' ? null : 'citas'"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                        {{ $citasActivo ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                        Citas
                        <span :class="menu === 'citas' ? 'rotate-180' : ''"
                              class="transition-transform">⌄</span>
                    </button>

                    <div x-cloak x-show="menu === 'citas'"
                         @click.outside="menu = null"
                         class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-2 shadow-xl">

                        <a href="{{ route('admin.citas.index') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.citas.index') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Citas registradas
                        </a>

                        <a href="{{ route('admin.citas.create') }}"
                           class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700
                           {{ request()->routeIs('admin.citas.create') ? 'bg-blue-50 text-blue-700' : '' }}">
                            Programar cita
                        </a>

                    </div>
                </div>

                {{-- REPORTES --}}
                <a href="{{ route('admin.reportes.index') }}"
                   class="rounded-lg px-4 py-2 text-sm font-semibold transition
                   {{ request()->routeIs('admin.reportes.index') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                    Reportes
                </a>

            </div>
        </nav>

    </header>

    {{-- CONTENIDO --}}
    <main class="mx-auto max-w-screen-2xl min-w-0 p-4 sm:p-6">

        @yield('content')

    </main>

</div>

</body>
</html>
