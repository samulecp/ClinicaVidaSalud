
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de citas</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #1e40af;
        }

        p {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #1e40af;
            color: white;
        }
    </style>
</head>
<body>

    <h1>Clínica Vida Salud</h1>
    <p>Reporte de citas médicas</p>

    <p>Total de citas: {{ $citas->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Especialidad</th>
                <th>Horario</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($citas as $cita)
                <tr>
                    <td>{{ $cita->codigo }}</td>
                    <td>{{ $cita->fecha?->format('d/m/Y') }}</td>
                    <td>{{ $cita->paciente->nombres }} {{ $cita->paciente->apellidos }}</td>
                    <td>{{ $cita->medico->nombres }} {{ $cita->medico->apellidos }}</td>
                    <td>{{ $cita->especialidad->nombre }}</td>
                    <td>{{ $cita->hora_inicio }} - {{ $cita->hora_fin }}</td>
                    <td>{{ $cita->estado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No se encontraron citas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
