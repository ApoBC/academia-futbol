<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_offline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->unique()->constrained('usuarios')->restrictOnDelete();
            $table->timestamp('fecha_ultima_sync')->nullable();
            $table->unsignedInteger('cantidad_registros_cache')->default(0);
            $table->string('hash_datos', 64)->nullable();
            $table->string('version_app', 20)->nullable();
            $table->unsignedInteger('asistencias_pendientes_sync')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_offline');
    }
};
