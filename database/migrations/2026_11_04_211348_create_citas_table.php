<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique();

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->restrictOnDelete();

            $table->foreignId('medico_id')
                ->constrained('medicos')
                ->restrictOnDelete();

            $table->foreignId('especialidad_id')
                ->constrained('especialidades')
                ->restrictOnDelete();

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->string('estado', 30)->default('Programada');

            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['medico_id', 'fecha']);
            $table->index(['paciente_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};