
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('modulo', 100);
            $table->string('accion', 100);
            $table->text('descripcion')->nullable();
            $table->string('metodo', 10)->nullable();
            $table->string('ruta')->nullable();
            $table->string('ip', 45)->nullable();

            $table->timestamps();

            $table->index(['modulo', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
