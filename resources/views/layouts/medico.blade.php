
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Médico') - Clínica Vida Salud</title>
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
        <header class="sticky top-0 z-50 border-b border-blue-800 shadow-lg bg-blue-950 text-white">

            {{-- MARCA Y USUARIO --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 md:px-8">

                <div>
                    <h1 class="text-lg font-bold tracking-wide md:text-xl">
                        Clínica Vida Salud
                    </h1>
                    <p class="text-xs text-blue-200">
                        Panel médico
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-xs text-blue-200">
                            Médico
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
                    <a href="{{ route('medico.dashboard') }}"
                       class="rounded-lg px-4 py-2 text-sm font-semibold transition
                       {{ request()->routeIs('medico.dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                        Inicio
                    </a>

                    {{-- GESTIÓN MÉDICA --}}
                    <div class="relative">

                        <button type="button"
                            @click="menu = menu === 'gestion' ? null : 'gestion'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition
                            {{ request()->routeIs('medico.agenda*') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-900' }}">
                            Gestión médica
                            <span>⌄</span>
                        </button>

                        <div x-cloak x-show="menu === 'gestion'"
                             @click.outside="menu = null"
                             class="absolute left-0 top-full z-50 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-2 shadow-xl">

                            <a href="{{ route('medico.agenda') }}"
                               class="block rounded-md px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700">
                                Mi agenda
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
                    Clínica Vida Salud / Área médica
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-800 md:text-2xl">
                    @yield('header', 'Panel médico')
                </h2>
            </div>

            @if(session('success'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')

        </main>

    </div>

</body>
</html>
