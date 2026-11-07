<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteCitasExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $citas;

    public function __construct(Collection $citas)
    {
        $this->citas = $citas;
    }

    public function collection()
    {
        return $this->citas;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Fecha',
            'Hora de inicio',
            'Hora de fin',
            'Paciente',
            'Médico',
            'Especialidad',
            'Estado',
            'Motivo',
        ];
    }

    public function map($cita): array
    {
        return [
            $cita->codigo,
            $cita->fecha?->format('d/m/Y'),
            $cita->hora_inicio,
            $cita->hora_fin,
            $cita->paciente->nombres . ' ' . $cita->paciente->apellidos,
            $cita->medico->nombres . ' ' . $cita->medico->apellidos,
            $cita->especialidad->nombre,
            $cita->estado,
            $cita->motivo,
        ];
    }
}
