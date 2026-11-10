<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\HorarioMedicoController;
use App\Http\Controllers\ConfiguracionSistemaController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\RegistroPacienteController;
use App\Http\Controllers\PacientePortalController;
use App\Http\Controllers\PacienteCitaController;
use App\Http\Controllers\ReprogramacionMasivaController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\MedicoPortalController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {

    if (auth()->check()) {
        return match (auth()->user()->rol) {
            'administrador' => redirect()->route('admin.dashboard'),
            'recepcionista' => redirect()->route('recepcion.dashboard'),
            'medico' => redirect()->route('medico.dashboard'),
            'paciente' => redirect()->route('paciente.dashboard'),
            default => abort(403, 'Usuario sin rol asignado.'),
        };
    }

    return view('welcome');

})->name('home');

Route::get('/registro-paciente', [RegistroPacienteController::class, 'create'])
    ->middleware('guest')
    ->name('registro.paciente');

Route::post('/registro-paciente', [RegistroPacienteController::class, 'store'])
    ->middleware('guest')
    ->name('registro.paciente.store');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::middleware(['auth', 'rol:administrador', 'bitacora'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    Route::get('/dashboard', function () {
    return view('admin.dashboard', [
        'totalPacientes' => \App\Models\Paciente::count(),
        'totalMedicos' => \App\Models\Medico::count(),
        'totalCitasProgramadas' => \App\Models\Cita::where('estado', 'Programada')->count(),
        'totalEspecialidades' => \App\Models\Especialidad::count(),
    ]);
})->name('dashboard');
    Route::resource('pacientes', PacienteController::class)
    ->except(['show', 'destroy']);
    Route::get('/bitacora', [\App\Http\Controllers\BitacoraController::class, 'index'])
    ->name('bitacora.index');

    Route::patch('/pacientes/{paciente}/estado', [PacienteController::class, 'cambiarEstado'])
    ->name('pacientes.estado');

    Route::get('/reportes', [ReporteController::class, 'index'])
    ->name('reportes.index');

    

Route::get('/reportes/pdf', [ReporteController::class, 'pdf'])
    ->name('reportes.pdf');

Route::get('/reportes/excel', [ReporteController::class, 'excel'])
    ->name('reportes.excel');

Route::get('/reportes/csv', [ReporteController::class, 'csv'])
    ->name('reportes.csv');

    Route::post('/reportes/interpretar-voz', [ReporteController::class, 'interpretarVoz'])
    ->name('reportes.interpretarVoz');

    Route::resource('citas', CitaController::class)
    ->only(['index', 'create', 'store']);

    Route::patch('/citas/{cita}/cancelar', [CitaController::class, 'cancelar'])
    ->name('citas.cancelar');

    // Gestión de médicos
    Route::resource('medicos', MedicoController::class)
    ->except(['show', 'destroy']);

    Route::patch('/medicos/{medico}/estado', [MedicoController::class, 'cambiarEstado'])
    ->name('medicos.estado');

    Route::resource('recepcionistas', RecepcionistaController::class)
    ->only(['index', 'create', 'store']);

    // Gestión de especialidades
    Route::resource('especialidades', EspecialidadController::class)
    ->except(['show', 'destroy']);

    Route::patch('/especialidades/{especialidad}/estado', [
    EspecialidadController::class,
    'cambiarEstado'
    ])->name('especialidades.estado');

    Route::resource('horarios', HorarioMedicoController::class)
    ->except(['show', 'destroy']);

    Route::patch('/horarios/{horario}/estado',
    [HorarioMedicoController::class, 'cambiarEstado']
    )->name('horarios.estado');

    Route::get('/configuracion', [ConfiguracionSistemaController::class, 'edit'])
    ->name('configuracion.edit');

    Route::put('/configuracion', [ConfiguracionSistemaController::class, 'update'])
    ->name('configuracion.update');
});

Route::middleware(['auth', 'rol:recepcionista', 'bitacora'])
    ->prefix('recepcion')
    ->name('recepcion.')
    ->group(function () {

        Route::get('/dashboard', [RecepcionController::class, 'dashboard'])
        ->name('dashboard'); 

        Route::resource('pacientes', PacienteController::class)
            ->except(['show', 'destroy']);

        Route::patch('/pacientes/{paciente}/estado', [
            PacienteController::class,
            'cambiarEstado'
        ])->name('pacientes.estado');

        // Reprogramación automática masiva
        Route::get('/reprogramacion', [
            ReprogramacionMasivaController::class,
            'index'
        ])->name('reprogramacion.index');

        Route::get('/reprogramacion/consultar', [
            ReprogramacionMasivaController::class,
            'consultar'
        ])->name('reprogramacion.consultar');

        Route::post('/reprogramacion/ejecutar', [
            ReprogramacionMasivaController::class,
            'ejecutar'
        ])->name('reprogramacion.ejecutar');

        Route::resource('citas', CitaController::class)
        ->only(['index', 'create', 'store']);
});



Route::middleware(['auth', 'rol:medico', 'bitacora'])
    ->prefix('medico')
    ->name('medico.')
    ->group(function () {

        Route::get('/dashboard', [
            MedicoPortalController::class,
            'dashboard'
        ])->name('dashboard');

        Route::get('/agenda', [
            MedicoPortalController::class,
            'agenda'
        ])->name('agenda');

        Route::get('/agenda/{cita}', [
    MedicoPortalController::class,
    'show'
])->name('citas.show');

Route::patch('/agenda/{cita}/atender', [
    MedicoPortalController::class,
    'atender'
])->name('citas.atender');

Route::patch('/agenda/{cita}/inasistencia', [
    MedicoPortalController::class,
    'inasistencia'
])->name('citas.inasistencia');
    });

Route::middleware(['auth', 'rol:paciente', 'bitacora'])
    ->prefix('paciente')
    ->name('paciente.')
    ->group(function () {

        Route::get('/dashboard', [PacientePortalController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/mis-citas', [PacientePortalController::class, 'citas'])
            ->name('citas');

        Route::get('/reservar', [PacienteCitaController::class, 'create'])
            ->name('reservar');

        Route::post('/reservar', [PacienteCitaController::class, 'store'])
            ->name('reservar.store');
            // Pagos PayPal Sandbox
Route::get('/paypal/retorno', [
    PacienteCitaController::class,
    'paypalRetorno'
])->name('paypal.retorno');

Route::get('/paypal/cancelado', [
    PacienteCitaController::class,
    'paypalCancelado'
])->name('paypal.cancelado');

        Route::patch('/mis-citas/{cita}/cancelar', [
        PacienteCitaController::class,
        'cancelar'
        ])->name('cancelar');
    });


Route::middleware('auth')->group(function () {

    Route::get('/disponibilidad/medicos', [
        DisponibilidadController::class,
        'medicosPorEspecialidad'
    ])->name('disponibilidad.medicos');

    Route::get('/disponibilidad/turnos', [
        DisponibilidadController::class,
        'turnos'
    ])->name('disponibilidad.turnos');

});

Route::get('/diagnostico-sesion', function () {
    header('X-PHP-Native-Probe: funciona');
    header('X-PHP-Native-Type: application/json');

    return response()->json([
        'prueba' => 'LARAVEL_CON_PHP_NATIVO',
        'entorno' => app()->environment(),
        'driver' => config('session.driver'),
    ]);
});

require __DIR__.'/auth.php';
