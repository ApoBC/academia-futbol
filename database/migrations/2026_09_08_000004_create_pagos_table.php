<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('plan_id')->constrained('planes')->restrictOnDelete();
            $table->foreignId('admin_id')->constrained('usuarios')->restrictOnDelete();
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('PEN');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'tarjeta', 'yape', 'plin', 'otro']);
            $table->string('numero_operacion', 100)->nullable();
            $table->date('fecha_pago');
            $table->date('fecha_inicio_vigencia');
            $table->date('fecha_expiracion')->nullable();
            $table->unsignedInteger('sesiones_otorgadas')->nullable();
            $table->enum('estado', ['confirmado', 'pendiente', 'anulado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['alumno_id', 'estado', 'fecha_expiracion'], 'idx_pagos_alumno_estado_exp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
