<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'cita_id',
        'usuario_id',
        'monto',
        'moneda',
        'metodo',
        'estado',
        'referencia',
        'tipo_cambio',
'fecha_tipo_cambio',
        'paypal_order_id',
        'paypal_capture_id',
        'monto_pasarela',
        'moneda_pasarela',
        'pagado_en',
        'datos_pasarela',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'monto_pasarela' => 'decimal:2',
            'pagado_en' => 'datetime',
            'datos_pasarela' => 'array',
            'tipo_cambio' => 'decimal:6',
'fecha_tipo_cambio' => 'datetime',
        ];
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}