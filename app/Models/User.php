<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Paciente;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => 'boolean',
        ];
    }

    public function medico()
{
    return $this->hasOne(\App\Models\Medico::class);
}

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function esRecepcionista(): bool
    {
        return $this->rol === 'recepcionista';
    }

    public function esMedico(): bool
    {
        return $this->rol === 'medico';
    }

    public function esPaciente(): bool
    {
        return $this->rol === 'paciente';
    }

    public function paciente()
{
    return $this->hasOne(Paciente::class);
}
}