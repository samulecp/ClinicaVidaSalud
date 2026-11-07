<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialCita extends Model
{
    protected $table = 'historial_citas';
    protected $fillable = [
        'cita_id',
        'usuario_id',
        'accion',
        'fecha_anterior',
        'hora_anterior',
        'fecha_nueva',
        'hora_nueva',
        'detalle',
    ];

    protected function casts(): array
    {
        return [
            'fecha_anterior' => 'date',
            'fecha_nueva' => 'date',
        ];
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}