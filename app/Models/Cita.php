<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cita extends Model
{
    protected $fillable = [
        'codigo',
        'paciente_id',
        'medico_id',
        'especialidad_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'motivo',
        'observaciones',
        'diagnostico',
        'tratamiento',
        'observaciones_medicas',
        'pago_expira_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'pago_expira_en' => 'datetime',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialCita::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function pagoAprobado(): HasMany
    {
        return $this->hasMany(Pago::class)
            ->where('estado', 'Aprobado');
    }
}