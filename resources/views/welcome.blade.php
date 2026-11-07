
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Vida Salud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100">

    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-lg rounded-2xl bg-white p-8 text-center shadow-lg">

            <div class="mb-6">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-3xl">
                    🏥
                </div>

                <h1 class="text-3xl font-bold text-blue-950">
                    Clínica Vida Salud
                </h1>

                <p class="mt-3 text-slate-500">
                    Sistema de gestión de citas médicas y atención al paciente.
                </p>
            </div>

            
                <a href="{{ route('login') }}"
                   class="mb-3 block w-full rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">
                    Iniciar sesión
                </a>

                <a href="{{ route('registro.paciente') }}"
                   class="block w-full rounded-lg border border-blue-200 px-5 py-3 font-semibold text-blue-700 hover:bg-blue-50">
                    Registrarse como paciente
                </a>
            

        </div>
    </div>

</body>
</html>
