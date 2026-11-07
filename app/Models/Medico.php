<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medico extends Model
{
    protected $fillable = [
        'registro_profesional',
        'user_id',
        'nombres',
        'apellidos',
        'telefono',
        'correo',
        'direccion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function user()
{
    return $this->belongsTo(User::class);
}

    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Especialidad::class,
            'medico_especialidad'
        );
    }
    public function horarios()
{
    return $this->hasMany(HorarioMedico::class);
}
public function citas()
{
    return $this->hasMany(Cita::class);
}


}