<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioMedico extends Model
{
    protected $table = 'horarios_medicos';
    protected $fillable = [
        'medico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }
}