
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Recepción') - Clínica Vida Salud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100">

    <div x-data="{ menu: null }" class="min-h-screen">

        {{-- BARRA SUPERIOR --}}
        <header class="sticky top-0 z-50 border-b border-slate-700 shadow-lg"
                style="background-color: #0f172a; color: #ffffff;">

            {{-- MARCA Y USUARIO --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 md:px-8">

                <div>
                    <h1 class="text-lg font-bold tracking-wide md:text-xl">
                        Clínica Vida Salud
                    </h1>
                    <p class="text-xs text-slate-300">
                        Panel de recepción
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-xs text-slate-300">
                            Recepcionista
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
            <nav class="border-t border-slate-700 px-3 py-2 md:px-8">

                <div class="flex flex-wrap items-center gap-2">

                    {{-- INICIO --}}
                    <a href="{{ route('recepcion.dashboard') }}"
                       class="rounded-lg px-4 py-2 text-sm font-semibold transition
                       {{ request()->routeIs('recepcion.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                        Inicio
                    </a>

                    {{-- PACIENTES --}}
                    <div class="relative">
                        <button type="button"
                            @click="menu = menu === 'pacientes' ? null : 'pacientes'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('recepcion.pacientes.*') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                            Pacientes
                            <span>⌄</span>
                        </button>

                        <div x-cloak x-show="menu === 'pacientes'"
                             @click.outside="menu = null"
                             class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-slate-200 bg-white p-2 shadow-xl">

                            <a href="{{ route('recepcion.pacientes.index') }}"
                               class="block rounded-md px-4 py-3 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                                Lista de pacientes
                            </a>

                        </div>
                    </div>

                    {{-- CITAS --}}
                    <div class="relative">
                        <button type="button"
                            @click="menu = menu === 'citas' ? null : 'citas'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('recepcion.citas.*') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                            Citas
                            <span>⌄</span>
                        </button>

                        <div x-cloak x-show="menu === 'citas'"
                             @click.outside="menu = null"
                             class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-slate-200 bg-white p-2 shadow-xl">

                            <a href="{{ route('recepcion.citas.index') }}"
                               class="block rounded-md px-4 py-3 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                                Citas registradas
                            </a>

                            <a href="{{ route('recepcion.citas.create') }}"
                               class="block rounded-md px-4 py-3 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                                Programar cita
                            </a>

                        </div>
                    </div>

                    {{-- REPROGRAMACIÓN --}}
                    <div class="relative">
                        <button type="button"
                            @click="menu = menu === 'reprogramacion' ? null : 'reprogramacion'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('recepcion.reprogramacion.*') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-700' }}">
                            Reprogramación
                            <span>⌄</span>
                        </button>

                        <div x-cloak x-show="menu === 'reprogramacion'"
                             @click.outside="menu = null"
                             class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-slate-200 bg-white p-2 shadow-xl">

                            <a href="{{ route('recepcion.reprogramacion.index') }}"
                               class="block rounded-md px-4 py-3 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                                Reprogramación masiva
                            </a>

                        </div>
                    </div>

                </div>
            </nav>
        </header>

        {{-- CONTENIDO --}}
        <main class="mx-auto w-full min-w-0 p-4 md:p-8">

            <div class="mb-6">
                <p class="text-sm text-gray-500">
                    Clínica Vida Salud / Recepción
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-800 md:text-2xl">
                    @yield('header', 'Panel de recepción')
                </h2>
            </div>

            @yield('content')

        </main>

    </div>

</body>
</html>
