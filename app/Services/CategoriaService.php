<?php

namespace App\Services;

use App\Enums\CategoriaAlumno;
use Carbon\Carbon;

class CategoriaService
{
    /**
     * Sugiere la categoría de un alumno según su edad actual.
     * Es solo un valor por defecto: el admin puede sobrescribirla manualmente
     * al crear o editar al alumno (ver docs/Diseno_Base_Datos_Academia_Futbol.md).
     */
    public function calcular(Carbon|string $fechaNacimiento): CategoriaAlumno
    {
        $fecha = $fechaNacimiento instanceof Carbon ? $fechaNacimiento : Carbon::parse($fechaNacimiento);
        $edad = $fecha->age;

        return match (true) {
            $edad <= 7 => CategoriaAlumno::Sub8,
            $edad <= 9 => CategoriaAlumno::Sub10,
            $edad <= 11 => CategoriaAlumno::Sub12,
            $edad <= 13 => CategoriaAlumno::Sub14,
            $edad <= 16 => CategoriaAlumno::Sub17,
            default => CategoriaAlumno::Mayores,
        };
    }
}
