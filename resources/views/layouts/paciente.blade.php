
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal del paciente') - Clínica Vida Salud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 text-gray-900">

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
                        Portal del paciente
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-blue-200">
                            Paciente
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
                    <a href="{{ route('paciente.dashboard') }}"
                       class="rounded-lg px-4 py-2 text-sm font-semibold transition
                       {{ request()->routeIs('paciente.dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                        Inicio
                    </a>

                    {{-- MIS CITAS --}}
                    <div class="relative">

                        <button type="button"
                            @click="menu = menu === 'citas' ? null : 'citas'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('paciente.citas') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                            Mis citas
                            <span>⌄</span>
                        </button>

                        <div x-cloak x-show="menu === 'citas'"
                             @click.outside="menu = null"
                             class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-2 shadow-xl">

                            <a href="{{ route('paciente.citas') }}"
                               class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700">
                                Consultar mis citas
                            </a>

                        </div>
                    </div>

                    {{-- RESERVAR --}}
                    <div class="relative">

                        <button type="button"
                            @click="menu = menu === 'reservar' ? null : 'reservar'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('paciente.reservar') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                            Reservar cita
                            <span>⌄</span>
                        </button>

                        <div x-cloak x-show="menu === 'reservar'"
                             @click.outside="menu = null"
                             class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-2 shadow-xl">

                            <a href="{{ route('paciente.reservar') }}"
                               class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700">
                                Nueva reserva
                            </a>

                        </div>
                    </div>

                </div>

            </nav>
        </header>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="mx-auto w-full min-w-0 p-4 md:p-8">

            {{-- ENCABEZADO DE LA PÁGINA --}}
            @hasSection('header')
                <div class="mb-6 rounded-xl bg-white p-5 shadow-sm">
                    @yield('header')
                </div>
            @endif

            {{-- CONTENIDO --}}
            <div class="min-w-0">
                @yield('content')
            </div>

        </main>

    </div>

</body>
</html>
