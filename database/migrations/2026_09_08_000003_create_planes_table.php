<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['mensual', 'pack', 'trimestral', 'anual']);
            $table->unsignedInteger('duracion_dias')->nullable();
            $table->unsignedInteger('sesiones_max')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('moneda', 3)->default('PEN');
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
