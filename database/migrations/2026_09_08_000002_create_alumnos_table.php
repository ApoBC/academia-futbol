<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('padre_id')->constrained('usuarios')->restrictOnDelete();
            $table->string('nombre_completo', 150);
            $table->string('dni', 20)->nullable()->unique();
            $table->date('fecha_nacimiento');
            $table->enum('categoria', ['sub_8', 'sub_10', 'sub_12', 'sub_14', 'sub_17', 'mayores']);
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->text('alergias_enfermedades')->nullable();
            $table->boolean('estado_salud_alerta')->default(false);
            $table->string('ficha_medica_path', 500)->nullable();
            $table->string('foto_path', 500)->nullable();
            $table->enum('estado', ['sin_pago', 'activo', 'suspendido', 'baja'])->default('sin_pago');
            $table->date('fecha_expiracion')->nullable();
            $table->unsignedInteger('sesiones_restantes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('categoria');
            $table->index('estado');
            $table->index('fecha_expiracion');
            $table->index(['estado', 'fecha_expiracion', 'categoria'], 'idx_alumnos_estado_exp_cat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
