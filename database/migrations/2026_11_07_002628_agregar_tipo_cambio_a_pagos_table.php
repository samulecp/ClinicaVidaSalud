
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('tipo_cambio', 12, 6)->nullable();
            $table->timestamp('fecha_tipo_cambio')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_cambio',
                'fecha_tipo_cambio',
            ]);
        });
    }
};
