<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_citas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cita_id')
                ->constrained('citas')
                ->restrictOnDelete();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('accion', 30);
            $table->date('fecha_anterior')->nullable();
            $table->time('hora_anterior')->nullable();
            $table->date('fecha_nueva')->nullable();
            $table->time('hora_nueva')->nullable();

            $table->text('detalle')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_citas');
    }
};