<?php

namespace App\Services;

use App\Enums\CategoriaAlumno;
use Carbon\Carbon;

class CategoriaService
{
    /**
     * Calcula la categoría de un alumno según su edad actual.
     * Rangos definidos en docs/Diseno_Base_Datos_Academia_Futbol.md.
     */
    public function calcular(Carbon|string $fechaNacimiento): CategoriaAlumno
    {
        $fecha = $fechaNacimiento instanceof Carbon ? $fechaNacimiento : Carbon::parse($fechaNacimiento);
        $edad = $fecha->age;

        return match (true) {
            $edad <= 7 => CategoriaAlumno::PreBenjamin,
            $edad <= 9 => CategoriaAlumno::Benjamin,
            $edad <= 11 => CategoriaAlumno::Alevin,
            $edad <= 13 => CategoriaAlumno::Infantil,
            $edad <= 15 => CategoriaAlumno::Cadete,
            default => CategoriaAlumno::Juvenil,
        };
    }
}
