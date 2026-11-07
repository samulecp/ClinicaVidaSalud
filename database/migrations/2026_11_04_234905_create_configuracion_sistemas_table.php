<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_sistema', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('duracion_cita_minutos')->default(30);
            $table->timestamps();
        });

        DB::table('configuraciones_sistema')->insert([
            'duracion_cita_minutos' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_sistema');
    }
};