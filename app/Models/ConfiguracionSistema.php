<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuraciones_sistema';

    protected $fillable = [
        'duracion_cita_minutos',
        'precio_consulta',
    ];

    protected function casts(): array
    {
        return [
            'duracion_cita_minutos' => 'integer',
            'precio_consulta' => 'decimal:2',
        ];
    }

    public static function actual(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'duracion_cita_minutos' => 30,
                'precio_consulta' => 100.00,
            ]
        );
    }
}