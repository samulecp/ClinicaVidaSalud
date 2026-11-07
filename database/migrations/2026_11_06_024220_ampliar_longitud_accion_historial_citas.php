<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_citas', function (Blueprint $table) {
            $table->string('accion', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('historial_citas', function (Blueprint $table) {
            $table->string('accion', 30)->change();
        });
    }
};