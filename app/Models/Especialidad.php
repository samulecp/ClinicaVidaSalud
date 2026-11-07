<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function medicos()
    {
        return $this->belongsToMany(
            Medico::class,
            'medico_especialidad'
        );
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}