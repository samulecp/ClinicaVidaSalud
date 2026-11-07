<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;
use App\Models\HorarioMedico;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\HistorialCita;
use App\Models\Bitacora;
use App\Models\ConfiguracionSistema;
use Carbon\Carbon;

class ClinicaDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $password = Hash::make('Demo2026!');

            // CONFIGURACIÓN
            DB::table('configuraciones_sistema')->updateOrInsert(
                ['id' => 1],
                [
                    'duracion_cita_minutos' => 15,
                    'precio_consulta' => 100,
                ]
            );

            // ADMINISTRADOR
            $admin = User::updateOrCreate(
                ['email' => 'admin@clinicavidasalud.test'],
                [
                    'name' => 'Administrador Demo',
                    'password' => $password,
                    'rol' => 'administrador',
                    'estado' => true,
                ]
            );

            // RECEPCIONISTAS
            $recepcionistas = [];

            for ($i = 1; $i <= 2; $i++) {
                $recepcionistas[] = User::updateOrCreate(
                    ['email' => "recepcion{$i}@clinicavidasalud.test"],
                    [
                        'name' => "Recepcionista Demo {$i}",
                        'password' => $password,
                        'rol' => 'recepcionista',
                        'estado' => true,
                    ]
                );
            }

            // ESPECIALIDADES
            $nombresEspecialidades = [
                'Medicina General',
                'Pediatría',
                'Cardiología',
                'Dermatología',
                'Ginecología',
                'Traumatología',
                'Oftalmología',
                'Odontología',
                'Neurología',
                'Endocrinología',
            ];

            $especialidades = [];

            foreach ($nombresEspecialidades as $nombre) {
                $especialidades[] = Especialidad::updateOrCreate(
                    ['nombre' => $nombre],
                    [
                        'descripcion' => "Servicio de {$nombre}",
                        'estado' => true,
                    ]
                );
            }

            // MÉDICOS
            $nombres = [
                'Carlos', 'María', 'José', 'Ana', 'Luis', 'Laura',
                'Pedro', 'Sofía', 'Miguel', 'Daniela', 'Andrés', 'Valeria'
            ];

            $apellidos = [
                'Méndez', 'Rojas', 'Flores', 'Gutiérrez',
                'Vargas', 'Castro', 'Suárez', 'Rivera',
                'Morales', 'Paz', 'Soria', 'Céspedes'
            ];

            $medicos = [];

            for ($i = 0; $i < 12; $i++) {
                $numero = $i + 1;
                $nombre = $nombres[$i];
                $apellido = $apellidos[$i];

                $user = User::updateOrCreate(
                    ['email' => sprintf('medico%02d@clinicavidasalud.test', $numero)],
                    [
                        'name' => "{$nombre} {$apellido}",
                        'password' => $password,
                        'rol' => 'medico',
                        'estado' => true,
                    ]
                );

                $medico = Medico::updateOrCreate(
    ['registro_profesional' => sprintf('MED-DEMO-%03d', $numero)],  
                    [
                        'user_id' => $user->id,
                        'nombres' => $nombre,
                        'apellidos' => $apellido,
                        'telefono' => sprintf('700%06d', $numero),
                        'correo' => $user->email,
                        'direccion' => 'Santa Cruz de la Sierra',
                        'estado' => true,
                    ]
                );

                $especialidad = $especialidades[$i % count($especialidades)];

                $medico->especialidades()->syncWithoutDetaching([
                    $especialidad->id
                ]);

                // HORARIOS: lunes a viernes
                for ($dia = 1; $dia <= 5; $dia++) {
                    HorarioMedico::firstOrCreate(
                        [
                            'medico_id' => $medico->id,
                            'dia_semana' => $dia,
                            'hora_inicio' => '08:00:00',
                            'hora_fin' => '12:00:00',
                        ],
                        ['estado' => true]
                    );

                    HorarioMedico::firstOrCreate(
                        [
                            'medico_id' => $medico->id,
                            'dia_semana' => $dia,
                            'hora_inicio' => '14:00:00',
                            'hora_fin' => '18:00:00',
                        ],
                        ['estado' => true]
                    );
                }

                $medicos[] = [
                    'medico' => $medico,
                    'especialidad' => $especialidad,
                ];
            }

            // PACIENTES
            $nombresPacientes = [
                'Gabriela', 'Fernando', 'Lucía', 'Roberto', 'Camila',
                'Javier', 'Paola', 'Diego', 'Natalia', 'Ricardo',
                'Andrea', 'Marco', 'Claudia', 'Sergio', 'Mónica',
                'Raúl', 'Elena', 'Pablo', 'Carolina', 'Hugo',
            ];

            $pacientes = [];

            for ($i = 1; $i <= 40; $i++) {
                $nombre = $nombresPacientes[($i - 1) % count($nombresPacientes)];
                $apellido = $apellidos[($i - 1) % count($apellidos)];
                $email = sprintf('paciente%02d@clinicavidasalud.test', $i);

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => "{$nombre} {$apellido}",
                        'password' => $password,
                        'rol' => 'paciente',
                        'estado' => true,
                    ]
                );

                $paciente = Paciente::updateOrCreate(
                    ['ci' => sprintf('DEMO-%07d', $i)],
                    [
                        'user_id' => $user->id,
                        'nombres' => $nombre,
                        'apellidos' => $apellido,
                        'fecha_nacimiento' => Carbon::create(
                            1970 + ($i % 30),
                            1 + ($i % 12),
                            1 + ($i % 27)
                        )->toDateString(),
                        'sexo' => $i % 2 === 0 ? 'F' : 'M',
                        'telefono' => sprintf('760%06d', $i),
                        'correo' => $email,
                        'direccion' => 'Santa Cruz de la Sierra',
                        'estado' => true,
                    ]
                );

                $pacientes[] = $paciente;
            }

            // CITAS Y PAGOS
            $estados = [
                'Programada',
                'Atendida',
                'Cancelada',
                'Pendiente de pago',
            ];

            $contador = 0;
            $fecha = Carbon::create(2026, 8, 7);
            $limite = Carbon::create(2026, 11, 7);

            while ($contador < 200 && $fecha->lte($limite)) {

                if ($fecha->isWeekday()) {
                    foreach ($medicos as $indice => $datosMedico) {
                        if ($contador >= 200) {
                            break;
                        }

                        $hora = $contador % 2 === 0 ? '09:00:00' : '15:00:00';

                        $paciente = $pacientes[$contador % count($pacientes)];
                        $medico = $datosMedico['medico'];
                        $especialidad = $datosMedico['especialidad'];

                        $estado = $estados[$contador % count($estados)];

                        $codigo = sprintf('DEMO-%06d', $contador + 1);

                        $cita = Cita::firstOrCreate(
                            ['codigo' => $codigo],
                            [
                                'paciente_id' => $paciente->id,
                                'medico_id' => $medico->id,
                                'especialidad_id' => $especialidad->id,
                                'fecha' => $fecha->toDateString(),
                                'hora_inicio' => $hora,
                                'hora_fin' => Carbon::parse($hora)
                                    ->addMinutes(15)->format('H:i:s'),
                                'estado' => $estado,
                                'motivo' => 'Consulta médica de demostración',
                                'observaciones' => 'Registro ficticio para pruebas.',
                            ]
                        );

                        $metodo = $contador % 2 === 0 ? 'qr' : 'efectivo';

                        $estadoPago = match ($estado) {
                            'Pendiente de pago' => 'Pendiente',
                            'Cancelada' => 'Cancelado',
                            default => 'Aprobado',
                        };

                        Pago::updateOrCreate(
                            ['cita_id' => $cita->id],
                            [
                                'usuario_id' => $paciente->user_id,
                                'monto' => 100,
                                'moneda' => 'BOB',
                                'metodo' => $metodo,
                                'estado' => $estadoPago,
                                'referencia' => "DEMO-PAGO-" . ($contador + 1),
                                'pagado_en' => $estadoPago === 'Aprobado'
                                    ? $fecha->copy()->setTime(8, 0)
                                    : null,
                            ]
                        );

                        HistorialCita::firstOrCreate(
                            [
                                'cita_id' => $cita->id,
                                'accion' => 'Creada',
                            ],
                            [
                                'usuario_id' => $admin->id,
                                'detalle' => 'Cita generada por el conjunto de datos de demostración.',
                            ]
                        );

                        $contador++;
                    }
                }

                $fecha->addDay();
            }

            // BITÁCORA
            foreach ($recepcionistas as $recepcionista) {
                Bitacora::firstOrCreate(
                    [
                        'user_id' => $recepcionista->id,
                        'modulo' => 'Sistema',
                        'accion' => 'Acceso demo',
                    ],
                    [
                        'descripcion' => 'Usuario de recepción creado para demostración.',
                        'metodo' => 'SEED',
                        'ruta' => 'database/seeders/ClinicaDemoSeeder.php',
                        'ip' => '127.0.0.1',
                    ]
                );
            }

            Bitacora::firstOrCreate(
                [
                    'user_id' => $admin->id,
                    'modulo' => 'Sistema',
                    'accion' => 'Carga demo',
                ],
                [
                    'descripcion' => 'Carga inicial de datos ficticios de demostración.',
                    'metodo' => 'SEED',
                    'ruta' => 'database/seeders/ClinicaDemoSeeder.php',
                    'ip' => '127.0.0.1',
                ]
            );
        });
    }
}
