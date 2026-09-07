<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // NOTA: Este proyecto NO usa la tabla "users" por defecto de Laravel.
        // El diseño de BD (docs/Diseno_Base_Datos_Academia_Futbol.md) usa una
        // sola tabla "usuarios" (admin/profesor/padre) creada vía database/schema.sql.
        // El modelo User se configurará en Fase 1 para apuntar a "usuarios".
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
