<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carnes_qr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('pago_id')->constrained('pagos')->restrictOnDelete();
            $table->text('uuid_encriptado');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('fecha_generacion')->useCurrent();
            $table->date('fecha_expiracion');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['alumno_id', 'activo'], 'idx_carnes_alumno_activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carnes_qr');
    }
};
