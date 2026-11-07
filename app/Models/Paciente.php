<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = [
        'user_id',
        'ci',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'correo',
        'direccion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'estado' => 'boolean',
        ];
    }
    public function citas()
{
    return $this->hasMany(Cita::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}
