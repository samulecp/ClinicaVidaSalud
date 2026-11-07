<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_medicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medico_id')
                ->constrained('medicos')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->boolean('estado')->default(true);

            $table->timestamps();

            $table->index(['medico_id', 'dia_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_medicos');
    }
};
