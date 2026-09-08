<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('profesor_id')->constrained('usuarios')->restrictOnDelete();
            $table->date('fecha');
            $table->time('hora');
            $table->enum('origen', ['escaneo_qr', 'manual'])->default('escaneo_qr');
            $table->boolean('sincronizado')->default(true);
            $table->string('dispositivo_sync')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['alumno_id', 'fecha'], 'uk_asistencia_alumno_fecha');
            $table->index('fecha');
            $table->index('sincronizado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
