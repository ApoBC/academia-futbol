<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intentos_escaneo_fallidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->text('contenido_qr');
            $table->enum('motivo_fallo', ['uuid_no_encontrado', 'desencriptacion_fallida', 'alumno_baja', 'qr_expirado']);
            $table->string('ip_origen', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('motivo_fallo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intentos_escaneo_fallidos');
    }
};
