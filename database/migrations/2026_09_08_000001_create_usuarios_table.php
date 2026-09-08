<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Refleja database/schema.sql (docs/Diseno_Base_Datos_Academia_Futbol.md).
// Existe para que `migrate` reconstruya el esquema completo en un entorno
// nuevo (tests con sqlite en memoria, otra máquina de desarrollo, etc.).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefono', 20)->nullable();
            $table->string('documento_identidad', 20)->nullable();
            $table->enum('rol', ['admin', 'profesor', 'padre'])->default('padre');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('rol');
            $table->index('documento_identidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
