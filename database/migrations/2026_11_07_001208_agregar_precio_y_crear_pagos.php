<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Precio de consulta configurable.
        Schema::table('configuraciones_sistema', function (Blueprint $table) {
            $table->decimal('precio_consulta', 10, 2)
                ->default(100.00);
        });

        // Tiempo límite para completar el pago de una reserva.
        Schema::table('citas', function (Blueprint $table) {
            $table->timestamp('pago_expira_en')->nullable();
        });

        // Registro de pagos.
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cita_id')
                ->constrained('citas')
                ->restrictOnDelete();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Importe de la consulta en bolivianos.
            $table->decimal('monto', 10, 2);

            $table->string('moneda', 3)->default('BOB');

            // efectivo, qr o paypal.
            $table->string('metodo', 20);

            // Pendiente, Aprobado, Rechazado o Reembolsado.
            $table->string('estado', 20)->default('Pendiente');

            // Referencia del pago QR o comprobante interno.
            $table->string('referencia')->nullable();

            // Identificadores devueltos por PayPal.
            $table->string('paypal_order_id')->nullable()->unique();
            $table->string('paypal_capture_id')->nullable()->unique();

            // Importe y moneda enviados a PayPal.
            $table->decimal('monto_pasarela', 10, 2)->nullable();
            $table->string('moneda_pasarela', 3)->nullable();

            $table->timestamp('pagado_en')->nullable();

            $table->json('datos_pasarela')->nullable();

            $table->timestamps();

            $table->index(['cita_id', 'estado']);
            $table->index(['metodo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');

        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn('pago_expira_en');
        });

        Schema::table('configuraciones_sistema', function (Blueprint $table) {
            $table->dropColumn('precio_consulta');
        });
    }
};